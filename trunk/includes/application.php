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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_BASE.'/includes/framework.php' );

/**
* Joomla! Application class
*
* Provide many supporting API functions
*
* @package Joomla
* @final
*/
class JSite extends JApplication
{
	/**
	* Class constructor
	*
	* @access protected
	* @param integer A client id
	*/
	function __construct() {
		parent::__construct(0);

		$this->_createPathWay( );
	}
	
	/**
	* Check if the user can access the application
	*
	* @access public
	*/
	function authorize($itemid)
	{
		//TODO :: should we show a login screen here ?
		$menus =& JMenu::getInstance();
		if(!$menus->authorize($itemid, JFactory::getUser())) {
			JError::raiseError( 403, JText::_('Not Authorised') );
		}
	}
	
	/**
	* Execute the application
	*
	* @access public
	*/
	function execute($option)
	{
		$template = JRequest::getVar( 'template', $this->getTemplate(), 'default', 'string' );
		$raw  	  = JRequest::getVar( 'no_html', 0, '', 'int' );
		$format   = JRequest::getVar( 'format', $raw ? 'raw' : 'html',  '', 'string'  );
		$file 	  = JRequest::getVar( 'tmpl', 'index.php', '', 'string'  );
		
		$user     =& JFactory::getUser();

		if ($this->getCfg('offline') && $user->get('gid') < '23' ) {
			$file = 'offline.php';
		}

		$this->_display($format, $template, $file);
	}
	
	/**
	* Login authentication function
	*
	* @param string The username
	* @param string The password
	* @access public
	* @see JApplication::login
	*/
	function login($username=null, $password=null, $return=null)
	{
		if(!$username || !$password) {
			$username = trim( JRequest::getVar( 'username', '', 'post' ) );
			$password = trim( JRequest::getVar( 'passwd', '', 'post' ) );
		}

		return parent::login($username, $password);
	}

	/**
	* Logout authentication function
	*
	* @access public
	* @see JApplication::login
	*/
	function logout($return = null) {
		return parent::logout();
	}

	/**
	* Set Page Title
	*
	* @param string $title The title for the page
	* @since 1.5
	*/
	function setPageTitle( $title=null ) {

		$site = $this->getCfg('sitename');

		if($this->getCfg('offline')) {
			$site .= ' [Offline]';
		}

		$title = stripslashes($title);

		$document=& JFactory::getDocument();
		$document->setTitle( $site.' - '.$title);
	}

	/**
	* Get Page title
	*
	* @return string The page title
	* @since 1.5
	*/
	function getPageTitle() {
		$document=& JFactory::getDocument();
		return $document->getTitle();
	}

	/**
	 * Set the configuration
	 *
	 * @access public
	 * @param string	The path to the configuration file
	 * @param string	The type of the configuration file
	 * @since 1.5
	 */
	function setConfiguration($file, $type = 'config')
	{
		parent::setConfiguration($file, $type);

		$registry =& JFactory::getConfig();
		$registry->setValue('config.live_site', substr_replace($this->getBaseURL(), '', -1, 1));
		$registry->setValue('config.absolute_path', JPATH_SITE);

		// Create the JConfig object
		$config = new JConfig();

		//Insert configuration values into global scope (for backwards compatibility)
		foreach (get_object_vars($config) as $k => $v) {
			$name = 'mosConfig_'.$k;
			$GLOBALS[$name] = $v;
		}
	}

	/**
	* Get the template
	*
	* @return string The template name
	* @since 1.0
	*/
	function getTemplate()
	{
		global $Itemid;

		static $templates;

		if (!isset ($templates))
		{
			$templates = array();

			/*
			 * Load template entries for each menuid
			 */
			$db = JFactory::getDBO();
			$query = "SELECT template, menuid"
				. "\n FROM #__templates_menu"
				. "\n WHERE client_id = 0"
				;
			$db->setQuery( $query );
			$templates = $db->loadObjectList('menuid');
		}

		if ($template = $this->getUserState( 'setTemplate' ))
		{
			// ok, allows for an override of the template from a component
			// eg, $mainframe->setTemplate( 'solar-flare-ii' );
		}
		else if (!empty($Itemid) && (isset($templates[$Itemid]))) {
			$template = $templates[$Itemid];
		} else {
			$template = $templates[0];
		}

		return $template->template;
	}

	/**
	 * Overrides the default template that would be used
	 *
	 * @param string The template name
	 */
	function setTemplate( $template )
	{
		if (is_dir( JPATH_SITE . '/templates/' . $template )) {
			$this->setUserState( 'setTemplate', $template );
		}
	}

	/**
	* Set the language
	*
	* @access public
	* @since 1.5
	*/
	function setLanguage($lang='')
	{
		// if a language was specified at login it has priority
		// otherwise use user or default language settings
		if (empty($lang)) {
			$user = & JFactory::getUser();
			$lang = $user->getParam( 'language', $this->getCfg('lang_site') );
		}

		//One last check to make sure we have something
		if (empty($lang)) {
			$lang = 'en-GB';
		}

		parent::setLanguage($lang);
	}
	
	/**
	* Set the legacy state of the application
	*
	* @access	public
	* @param	boolean	$force	Force loading of the legacy libraries
	* @since	1.5
	*/
	function setLegacy($force = false)
	{
		$config = & JFactory::getConfig();
		if ($config->getValue('config.legacy') || $force) {
			jimport('joomla.common.legacy');
		}
	}
	
	/**
	* Display the application
	*
	* @access protected
	* @since 1.5
	*/
	function _display($format, $template, $file)
	{
		$user     =& JFactory::getUser();
		$document =& JFactory::getDocument($format);

		switch($format)
		{
			case 'html':
				//set metadata
				$document->setMetaData( 'keywords', $this->getCfg('MetaKeys') );

				if ( $user->get('id') ) {
					$document->addScript( 'includes/js/joomla/common.js');
					$document->addScript( 'includes/js/joomla.javascript.js');
				}
				break;

			default: break;
		}

	
		$document->setTitle( $this->getCfg('sitename' ));
		$document->setDescription( $this->getCfg('MetaDesc') );
		
		$params = array(
			'outline'   => JRequest::getVar('tp', 0 ),
			'template' 	=> $template,
			'file'		=> $file,
			'directory'	=> JPATH_BASE.DS.'templates'
		);
		
		$document->display( $this->getCfg('caching_tmpl'), $this->getCfg('gzip'), $params);
	}
}

/**
 * @package Joomla
 * @static
 */
class JSiteHelper
{
	/**
	 * Gets the current menu item
	 *
	 * @static
	 * @return	object	Reference to the current menu item, an an empty menu object if none set
	 * @since	1.5
	 */
	function &getCurrentMenuItem()
	{
		$itemid = JRequest::getVar( 'Itemid', 0, '', 'int' );
		$menu	= &JMenu::getInstance();

		$result = &$menu->getItem( $itemid );
		if ($result == false) {
			$result = JTable::getInstance( 'menu', JFactory::getDBO() );
		}
		return $result;
	}

	/**
	 * Gets the parameter object for the current menu
	 *
	 * @static
	 * @return	object	A JParameter object
	 * @since	1.5
	 */
	function &getMenuParams()
	{
		static $instance;

		if ($instance == null)
		{
			$item		= &JSiteHelper::getCurrentMenuItem();
			$instance	= new JParameter( $item->params );
		}
		return $instance;
	}

	/**
	 * Gets the control parameters object for the current menu
	 *
	 * @static
	 * @return	object	A JParameter object
	 * @since	1.5
	 */
	function &getControlParams()
	{
		static $instance;

		if ($instance == null)
		{
			$item		= &JSiteHelper::getCurrentMenuItem();
			$instance	= new JParameter( $item->control );
		}
		return $instance;
	}
	
	/**
	 * Return the application itemid
	 *
	 * @access public
	 * @return string Option
	 * @since 1.5
	 */
	function findItemid()
	{
		$itemid = JRequest::getVar( 'Itemid', 0, '', 'int' );
		$option = strtolower(JRequest::getVar('option', null));

		if ( $itemid === 0 )
		{
			// checking if we can find the Itemid thru the content
			if($option == 'com_content')
			{
				require_once (JApplicationHelper::getPath('helper', 'com_content'));
				$id 	= JRequest::getVar( 'id', 0, '', 'int' );
				$itemid = JContentHelper::getItemid($id);
			}
			else
			{
				$menus =& JMenu::getInstance();
				$item  =& $menus->getDefault();

				$itemid = $item->id;
			}
		}

		return JRequest::setVar( 'Itemid', $itemid, '', 'int' );
	}

	/**
	 * Return the application option string [main component]
	 *
	 * @access public
	 * @return string Option
	 * @since 1.5
	 */
	function findOption()
	{
		$option = strtolower(JRequest::getVar('option', null));

		if(empty($option))
		{
			$menu =& JMenu::getInstance();
			$item =& $menu->getItem(JSiteHelper::findItemid());

			$component = JTable::getInstance( 'component', JFactory::getDBO() );
			$component->load($item->componentid);

			$option = $component->option;

		}

		return $option;
	}
}
?>