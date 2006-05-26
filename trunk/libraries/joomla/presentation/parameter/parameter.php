<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.registry.registry' );

/**
 * Parameter handler
 *
 * @author 		Johan Janssens <johan.janssens@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage 	Parameter
 * @since		1.5
 */
class JParameter extends JRegistry
{
	/**
	 * The raw params string
	 *
	 * @access	private
	 * @var string
	 */
	var $_raw = null;

	/**
	 * The xml params element
	 *
	 * @access	private
	 * @var object
	 */
	var $_xml = null;

	/**
	* loaded elements
	*
	* @access	private
	* @var		array
	*/
	var $_elements = array();

	/**
	* directories, where element types can be stored
	*
	* @access	private
	* @var		array
	*/
	var $_elementDirs  = array();

	/**
	 * Constructor
	 *
	 * @access protected
	 * @param string The raw parms text
	 * @param string Path to the xml setup file
	 * @var string The type of setup file
	 */
	function __construct($data, $path = '')
	{
		if( !defined( 'JPARAMETER_INCLUDE_PATH' ) ) {
			define( 'JPARAMETER_INCLUDE_PATH', dirname( __FILE__ ) . '/element' );
		}

		parent::__construct('parameter');

		$this->loadINI($data);
		$this->loadSetupFile($path);

		$this->_raw = $data;
	}

	/**
	 * Set a value
	 *
	 * @access public
	 * @param string The name of the param
	 * @param string The value of the parameter
	 * @return string The set value
	 */
	function set($key, $value = '') {
		return $this->setValue('parameter.'.$key, (string) $value);
	}

	/**
	 * Get a value
	 *
	 * @access public
	 * @param string The name of the param
	 * @param mixed The default value if not found
	 * @return string
	 */
	function get($key, $default = '') 
	{
		$value = $this->getValue('parameter.'.$key);
		$result = (empty($value) && ($value !== 0) && ($value !== '0')) ? $default : $value;
		return $result;
	}

	/**
	 * Sets the XML object from custom xml files
	 * @access public
	 * @param object An XML object
	 */
	function setXML( &$xml )
	{
		if (is_object( $xml ))
		{
			$this->_xml = $xml;
			if ($dir = $xml->attributes( 'addparameterdir' ))
			{
				$this->addParameterDir( JPATH_SITE . $dir );
			}
		}
	}

	/**
	 * Sets a default value if not alreay assigned
	 *
	 * @access public
	 * @param string The name of the param
	 * @param string The value of the parameter
	 * @return string The set value
	 */
	function def($key, $value = '') {
		return $this->set($key, $this->get($key, (string) $value));
	}

	/**
	 * Render
	 *
	 * @access public
	 * @param string The name of the control, or the default text area if a setup file is not found
	 * @return string HTML
	 */
	function render($name = 'params')
	{
		if (!is_object($this->_xml)) {
			return false;
		}
		
		$params = $this->getParams($name);

		$html = array ();
		$html[] = '<table width="100%" class="paramlist" cellspacing="1">';

		if ($description = $this->_xml->attributes('description')) {
			// add the params description to the display
			$html[] = '<tr><td colspan="3">'.$description.'</td></tr>';
		}
		
		foreach ($params as $param)
		{
			$html[] = '<tr>';

			$html[] = '<td width="40%" class="paramlist_key"><span class="editlinktip">'.$param[0].'</span></td>';
			$html[] = '<td class="paramlist_value">'.$param[1].'</td>';

			$html[] = '</tr>';
		}

		if (count($params) < 1) {
			$html[] = "<tr><td colspan=\"2\"><i>".JText::_('There are no Parameters for this item')."</i></td></tr>";
		}

		$html[] = '</table>';

		return implode("\n", $html);
	}
	
	/**
	 * Render all parameters
	 * 
	 * @access public
	 * @param string The name of the control, or the default text area if a setup file is not found
	 * @return array of all parameters, each as array Any array of the label, the form element and the tooltip
	 */
	function getParams($name = 'params') 
	{
		if (!is_object($this->_xml)) {
			return false;
		}
		$results = array();
		foreach ($this->_xml->children() as $param)  {
			$results[] = $this->getParam($param, $name);
		}
		return $results;
	}

	/**
	 * Render a parameter type
	 *
	 * @param object A param tag node
	 * @param string The control name
	 * @return array Any array of the label, the form element and the tooltip
	 */
	function getParam(&$node, $control_name = 'params')
	{
		//get the type of the parameter
		$type = $node->attributes('type');

		//remove any occurance of a mos_ prefix
		$type = str_replace('mos_', '', $type);

		$element =& $this->loadElement($type);

		/**
		 * error happened
		 */
		if ($element === false) {

			$result = array();
			$result[0] = $node->attributes('name');
			$result[1] = JText::_('Element not defined for type').' = '.$type;
			return $result;
		}

		return $element->render($node, $control_name);
	}

	/**
	* Loads an xml setup file and parses it
	*
	* @access	public
	* @param	string	path to xml setup file
	* @return	object
	* @since 1.5
	*/
	function loadSetupFile($path)
	{
		$result = false;

		if ($path)
		{
			$xml = & JFactory::getXMLParser('Simple');

			if ($xml->loadFile($path))
			{
				if ($params = & $xml->document->params[0]) {
					$this->setXML( $params );
					$result = true;
				}
			}
		}
		else
		{
			$result = true;
		}

		return $result;
	}

	/**
	* Loads a element type
	*
	* @access	public
	* @param	string	elementType
	* @return	object
	* @since 1.5
	*/
	function &loadElement( $type, $new = false ) 
	{
		$false = false;
		$signature = md5( $type  );

		if( isset( $this->_elements[$signature] ) && $new === false ) {
			return	$this->_elements[$signature];
		}

		if( !class_exists( 'JElement' ) ) {
			jimport('joomla.presentation.parameter.element'); 
		}

		$elementClass	=	'JElement_' . $type;
		if( !class_exists( $elementClass ) ) {
			if( isset( $this->_elementDirs ) )
				$dirs = $this->_elementDirs;
			else
				$dirs = array();

			array_push( $dirs, $this->getIncludePath());

			$found = false;
			foreach( $dirs as $dir ) {
				$elementFile	= sprintf( "%s/%s.php", $dir, str_replace( '_', '/', $type ) );

				if (@include_once $elementFile) {
					$found = true;
					break;
				}
			}

			if( !$found ) {
				return $false;
			}
		}

		if( !class_exists( $elementClass ) ) {
			return $false;
		}

		$this->_elements[$signature] = new $elementClass($this);

		return $this->_elements[$signature];
	}

	/**
	* Add a directory where JParameter should search for element types
	*
	* You may either pass a string or an array of directories.
	*
	* JParameter will be searching for a element type in the same
	* order you added them. If the parameter type cannot be found in
	* the custom folders, it will look in
	* JParameter/types.
	*
	* @access	public
	* @param	string|array	directory or directories to search.
	* @since 1.5
	*/
	function addParameterDir( $dir ) 
	{
		if( is_array( $dir ) ) {
			$this->_elementDirs = array_merge( $this->_elementDirs, $dir );
		} else {
			array_push( $this->_elementDirs, $dir );
		}
	}

   /**
	* Get the include path
	*
	* @access	public
	* @return   string
	* @since 1.5
	*/
	function getIncludePath() {
		return	JPARAMETER_INCLUDE_PATH;
	}
}
?>