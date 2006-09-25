<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport('joomla.application.module.helper');

/**
 * JDocumentHTML Include function
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */
class patTemplate_Function_Include extends patTemplate_Function
{
   /**
	* name of the function
	* @access	private
	* @var		string
	*/
	var $_name	=	'include';

	/**
	* reference to the JDocument object that instantiated the module
	*
	* @access	protected
	* @var	object
	*/
	var	$_tmpl;


   /**
	* call the function
	*
	* @access	public
	* @param	array	parameters of the function (= attributes of the tag)
	* @param	string	content of the tag
	* @return	string	content to insert into the template
	*/
	function call( $params, $content )
	{
		if(!isset($params['type'])) {
			return false;
		}

		$type = isset($params['type']) ? strtolower( $params['type'] ) : null;
		unset($params['type']);

		$name = isset($params['name']) ? strtolower( $params['name'] ) : null;
		unset($params['name']);

		switch($type)
		{
			case 'modules'  		:
			{
				$modules =& JModuleHelper::getModules($name);

				$total = count($modules);
				for($i = 0; $i < $total; $i++) {
					foreach($params as $param => $value) {
						$modules[$i]->$param = $value;
					}
				}

				$this->_addPlaceholder($type, $name);

			} break;
			case 'module' 		:
			{
				$module =& JModuleHelper::getModule($name);

				foreach($params as $param => $value) {
					$module->$param = $value;
				}

				$this->_addPlaceholder($type, $name);
			} break;

			case 'message'		:
			case 'head'         :
			case 'component'	:
			{
				//do nothing
			}	break;

			default : $this->_addPlaceholder($type, $name);
		}

		return '{'.strtoupper($type).'_'.strtoupper($name).'}';
	}

	 /**
	* reference to the patTemplate object that instantiated the module
	*
	* @access	public
	* @param	object		JDocument object
	*/
	function setTemplateReference( &$tmpl )
	{
		$this->_tmpl = &$tmpl;
	}

	/**
	 * Adds a discovered placeholder
	 *
	 * @access protected
	 * @param string 	$type	The renderer type
	 * @param string 	$name	The renderer name
	 */
	function _addPlaceholder($type, $name) {
		$this->_tmpl->_discoveredPlaceholders['document'][$type][] = $name;
	}
}
?>