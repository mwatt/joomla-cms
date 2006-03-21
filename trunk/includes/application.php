<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
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
class JSite extends JApplication {

	/**
	* Class constructor
	* 
	* @access protected
	* @param integer A client id
	*/
	function __construct() {
		parent::__construct(0);
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
	
		if (parent::login($username, $password)) 
		{	
			$return = JRequest::getVar( 'return' );
		
			if ( $return && !( strpos( $return, 'com_registration' ) || strpos( $return, 'com_login' ) ) ) {
				// checks for the presence of a return url
				// and ensures that this url is not the registration or login pages
				mosRedirect( $return );
			}
		} 
		
		return false;
	}
	
	/**
	* Logout authentication function
	* 
	* @access public
	* @see JApplication::login
	*/
	function logout($return = null) 
	{
		parent::logout();
		
		$return = JRequest::getVar( 'return' );

		if ( $return && !( strpos( $return, 'com_registration' ) || strpos( $return, 'com_login' ) ) ) {
			// checks for the presence of a return url
			// and ensures that this url is not the registration or logout pages
			mosRedirect( $return );
		} else {
			mosRedirect( 'index.php' );
		}
	}
	
	/**
	* Set Page Title
	* 
	* @param string $title The title for the page
	* @since 1.1
	*/
	function setPageTitle( $title=null ) {
	
		$site = $this->getCfg('sitename');
		
		if($this->getCfg('offline')) {
			$site .= ' [Offline]';
		}
		
		$title = stripslashes($title);
		
		$document=& $this->getDocument();
		$document->setTitle( $site.' - '.$title);
	}

	/**
	* Get Page title
	* 
	* @return string The page title
	* @since 1.1
	*/
	function getPageTitle() {
		$document=& $this->getDocument();
		return $document->getTitle();
	}
	
	/**
	 * Set the configuration
	 *
	 * @access public
	 * @param string	The path to the configuration file
	 * @param string	The type of the configuration file
	 * @since 1.1
	 */
	function setConfiguration($file, $type = 'config') 
	{
		parent::setConfiguration($file, $type);
		
		// Create the JConfig object
		$config = new JConfig();
		$config->live_site     = substr_replace($this->getBaseURL(), '', -1, 1);
		$config->absolute_path = JPATH_SITE;

		// Load the configuration values into the registry
		$this->_registry->loadObject($config);
		
		//Insert configuration values into global scope (for backwards compatibility)
		foreach (get_object_vars($config) as $k => $v) {
			$name = 'mosConfig_'.$k;
			$GLOBALS[$name] = $v;
		}
		// create the backward language value
		$lang = $this->getLanguage();
		$GLOBALS['mosConfig_lang'] = $lang->getBackwardLang();
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
			$db = $this->getDBO();
			$query = "SELECT template, menuid"
				. "\n FROM #__templates_menu"
				. "\n WHERE client_id = 0"
				;
			$db->setQuery( $query );
			$templates = $db->loadObjectList('menuid');
		}
		
		if (!empty($Itemid) && (isset($templates[$Itemid])))
		{
			$template = $templates[$Itemid];
		}
		else
		{
			$template = $templates[0];
		}

		return $template->template;
	}
}


/** 
 * @global $_VERSION 
 */
$_VERSION = new JVersion();

/**
 *  Legacy global
 * 	use JApplicaiton->registerEvent and JApplication->triggerEvent for event handling
 *  use JPlugingHelper::importPlugin to load bot code
 *  @deprecated As of version 1.1
 */
$_MAMBOTS = new mosMambotHandler();
?>