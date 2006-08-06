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

/**
* Base class for a Joomla! application
*
* Acts as a Factory class for application specific objects and
* provides many supporting API functions.
*
* @abstract
* @package		Joomla.Framework
* @subpackage	Application
* @since		1.5
*/

class JApplication extends JObject
{
	/**
	 * The pathway store
	 *
	 * @var object  JPathWay object
	 * @access protected
	 */
	var $_pathway = null;

	/**
	 * The client identifier
	 *
	 * @var integer
	 * @access protected
	 * @since 1.5
	 */
	var $_clientId = null;

	/**
	 * The url of the application
	 *
	 * @var string
	 * @access protected
	 */
	var $_baseURL = null;

	/**
	* Class constructor
	*
	* @param string 	The URL option passed in
	* @param integer	A client identifier
	*/
	function __construct( $clientId = 0 )
	{
		$this->_clientId = $clientId;
		$this->set( 'requestTime', date('Y-m-d H:i', time()) );
	}
	
	/**
	* Execute the application
	*
	* @access public
	*/
	function execute($option, $tmpl)
	{
	
	}

	 /**
	 * Gets a configuration value
	 *
	 * @access public
	 * @param string 	$varname 	The name of the value to get
	 * @return The user state
	 */
	function getCfg( $varname )
	{
		$config =& JFactory::getConfig();
		return $config->getValue('config.'.$varname);
	}

	/**
	 * Gets a user state
	 *
	 * @access public
	 * @param string 	$key 	The path of the state
	 * @return The user state
	 */
	function getUserState( $key )
	{
		$registry =& JSession::get('registry');
		if(!is_null($registry)) {
			return $registry->getValue($key);
		}
		return false;
	}

	/**
	* Sets the value of a user state variable
	*
	* @access public
	* @param string $key 	The path of the state
	* @param string $value 	The value of the variable
	* @return mixed The previous state if exist
	*/
	function setUserState( $key, $value )
	{
		$registry =& JSession::get('registry');
		if(!is_null($registry)) {
			return $registry->setValue($key, $value);
		}
		return false;
	}

	/**
	 * Gets the value of a user state variable
	 *
	 * @access public
	 * @param string The key of the user state variable
	 * @param string The name of the variable passed in a request
	 * @param string The default value for the variable if not found
	 * @return The request user state
	 */
	function getUserStateFromRequest( $key, $request, $default=null )
	{
		//Force namespace
		$key = 'request.'.$key;

		$old_state = $this->getUserState( $key );
		$cur_state = isset( $old_state ) ? $old_state : $default;
		$new_state = JRequest::getVar( $request, $cur_state );

		/*
		$new_state = isset( $_REQUEST[$request] ) ? $_REQUEST[$request] : $cur_state;

		// filter input
		jimport( 'phpinputfilter.inputfilter' );
		$iFilter = new InputFilter();
		$new_state = $iFilter->process( $new_state );
		*/

		$this->setUserState( $key, $new_state );

		return $new_state;
	}

	/**
	 * Registers a handler to a particular event group
	 *
	 * @static
	 * @param string The event name
	 * @param mixed The handler, a function or an instance of a event object
	 * @since 1.5
	 */
	function registerEvent($event, $handler) {
		$dispatcher =& JEventDispatcher::getInstance();
		return $dispatcher->register($event, $handler);
	}

	/**
	 * Calls all handlers associated with an event group
	 *
	 * @static
	 * @param string The event name
	 * @param array An array of arguments
	 * @return array An array of results from each function call
	 * @since 1.5
	 */
	function triggerEvent($event, $args=null) {
		$dispatcher =& JEventDispatcher::getInstance();
		return $dispatcher->trigger($event, $args);
	}

	/**
	 * Login authentication function
	 *
	 * Username and encoded password are passed the the onLoginUser event who
	 * is responsible for the user validation.
	 * A successful validation updates the current session record with the
	 * users details.
	 *
	 * Username and Password are sent as credentials (along with other possibilities)
	 * to each observer (JAuthenticatePlugin) for user validation.  Successful validation will
	 * update the current session with the user details
	 *
	 * @param string The username
	 * @param string The password
	 * @return boolean True on success
	 * @access public
	 * @since 1.5
	 */
	function login($username,$password)
	{
		if (!JUtility::spoofCheck()) {
			return JError::raiseWarning( 403, JText::_( 'E_SESSION_TIMEOUT' ) );
		}

		if (empty($username))  {
			return JError::raiseWarning('SOME_ERROR_CODE', JText::_('E_LOGIN_USERNAME'));
		}

		if(empty($password)) {
			return JError::raiseWarning('SOME_ERROR_CODE', JText::_('E_LOGIN_PASSWORD'));
		}

		// Get the global database connector object
		$db = JFactory::getDBO();

		// Build the credentials array
		$credentials['username'] = $username;
		$credentials['password'] = $password;

		// Get the global JAuthenticate object
		jimport( 'joomla.application.user.authenticate');
		$auth = & JAuthenticate::getInstance();
		$authenticated = $auth->authenticate($credentials);

		if ($authenticated !== false)
		{
			// Import the user plugin group
			JPluginHelper::importPlugin('user');

			// OK, the credentials are authenticated.  Lets fire the onLogin event
			$results = $this->triggerEvent( 'onLogin', $credentials);

			/*
			 * If any of the authentication plugins did not successfully complete the login
			 * routine then the whole method fails.  Any errors raised should be done in
			 * the plugin as this provides the ability to provide much more information
			 * about why the routine may have failed.
			 */
			if (!in_array(false, $results, true))
			{
				// Get the JUser object for the user to login
				$user =& JUser::getInstance( $username );

				// If the user is blocked, redirect with an error
				if ($user->get('block') == 1) {
					return JError::raiseWarning('SOME_ERROR_CODE', JText::_('E_NOLOGIN_BLOCKED'));
				}

				// Fudge the ACL stuff for now...
				// TODO: Implement ACL :)
				jimport('joomla.factory');
				$acl = &JFactory::getACL();
				$grp = $acl->getAroGroup($user->get('id'));
				$row->gid = 1;

				// ToDO: Add simple mapping based on the group table to allow positive references between content and user groups
				if ($acl->is_group_child_of($grp->name, 'Registered', 'ARO') || $acl->is_group_child_of($grp->name, 'Public Backend', 'ARO')) {
					// fudge Authors, Editors, Publishers and Super Administrators into the Special Group
					$user->set('gid', 2);
				}
				$user->set('usertype', $grp->name);

				// Register the needed session variables
				JSession::set('guest', 0);
				JSession::set('username', $user->get('username'));
				JSession::set('userid', intval($user->get('id')));
				JSession::set('usertype', $user->get('usertype'));
				JSession::set('gid', intval($user->get('gid')));

				// Register session variables to prevent spoofing
				JSession::set('JAuthenticate_RemoteAddr', $_SERVER['REMOTE_ADDR']);
				JSession::set('JAuthenticate_UserAgent', $_SERVER['HTTP_USER_AGENT']);

				// Get the session object
				$session = & JTable::getInstance('session', JFactory::getDBO());
				$session->load( JSession::id());

				$session->guest = 0;
				$session->username = $user->get('username');
				$session->userid = intval($user->get('id'));
				$session->usertype = $user->get('usertype');
				$session->gid = intval($user->get('gid'));

				$session->update();

				// Hit the user last visit field
				$user->setLastVisit();

				// Set remember me option
				$remember = JRequest::getVar( 'remember' ); //needs to be a paramater
				if ($remember == 'yes') {
					$lifetime = time() + 365*24*60*60;
					setcookie( 'usercookie[username]', $user->get('username'), $lifetime, '/' );
					setcookie( 'usercookie[password]', $user->get('password'), $lifetime, '/' );
				}

				return true;
			}
		}
		return JError::raiseWarning('SOME_ERROR_CODE', JText::_('E_LOGIN_AUTHENTICATE'));
	}

	/**
	* Logout authentication function
	*
	* Passed the current user information to the onLogoutUser event and reverts the current
	* session record back to 'anonymous' parameters
	* @access public
	*/
	function logout()
	{
		// Initialize variables
		$retval = false;

		// Get a user object from the JApplication
		$user = JFactory::getUser();

		// Build the credentials array
		$credentials['username'] 	= $user->get('username');
		$credentials['id'] 			= $user->get('id');

		// Import the user plugin group
		JPluginHelper::importPlugin('user');

		// OK, the credentials are built. Lets fire the onLogout event
		$results = $this->triggerEvent( 'onLogout', $credentials);

		/*
		 * If any of the authentication plugins did not successfully complete the logout
		 * routine then the whole method fails.  Any errors raised should be done in
		 * the plugin as this provides the ability to provide much more information
		 * about why the routine may have failed.
		 */
		if (!in_array(false, $results, true))
		{
			// Remove the session from the session table
			$session = & JTable::getInstance('session', JFactory::getDBO());
			$session->load( JSession::id());
			$session->destroy();

			// Destroy the php session for this user
			JSession::destroy();

			$retval = true;
		}

		// Hit the user last visit field
		$user->setLastVisit();

		return $retval;
	}

	/**
	 * Return the application url
	 *
	 * @access public
	 * @return string The url of the application
	 * @since 1.5
	 */
	function getBaseURL()
	{
		$uri =& JFactory::getURI();

		$url  = $uri->getScheme().'://';
		$url .= $uri->getHost();
		if ($port = $uri->getPort()) {
			$url .= ":$port";
		}
		$url .=  rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/';
		return $url;
	}

	/**
	 * Set the user session
	 *
	 * @access public
	 * @param string	The sessions name
	 */
	function setSession($name)
	{
		$this->_createSession($name);

		if (JSession::isIdle()) {
			$this->logout();
		}

		JSession::updateIdle();
	}

	/**
	 * Set the application language
	 *
	 * @access private
	 * @param string 	The language name
	 * @since 1.5
	 */
	function setLanguage( $lang )
	{
		//Set the language in the class
		$conf =& JFactory::getConfig();
		$conf->setValue('config.language', $lang);

		//set language debug
		$lang =& JFactory::getLanguage();
		$lang->setDebug($this->getCfg('debug_lang'));

		// create the backward compatible language value for old 3PD components
		$GLOBALS['mosConfig_lang']  = $lang->getBackwardLang();
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
		$this->_createConfiguration($file, $type);
		
		// Set the database debug 
		$db =& JFactory::getDBO();
		$db->debug( $this->getCfg('debug_db'));
		
		// Set the language debug
		$lang =& JFactory::getLanguage();
		$lang->setDebug( $this->getCfg('debug'));
	}

	/**
	 * Gets the name of the current template
	 *
	 * @return string
	 */
	function getTemplate() {
		return '_system';
	}

	/**
	 * Return a reference to the JPathWay object
	 *
	 * @access public
	 * @return jpathway 	JPathWay object
	 * @since 1.5
	 */
	function &getPathWay() {
		return $this->_pathway;
	}

	/**
	 * Create a JPathWay object and set the home/component items of the pathway
	 *
	 * @access private
	 * @return boolean True if successful
	 * @since 1.5
	 */
	function _createPathWay()
	{
		//Load the pathway object
		jimport( 'joomla.application.pathway' );

		//Get some request variables
		$ItemID = JRequest::getVar( 'Itemid', null, '', 'int' );
		$option = JRequest::getVar('option');

		// Create a JPathWay object
		$this->_pathway = new JPathWay();

		// If not on the frontpage, add the component item to the pathway
		if (($option == 'com_frontpage') || ($option == '')) {

			// Add the home item to the pathway only and it is not linked
			$this->_pathway->addItem( 'Home', '' );
		} else {

			// Initialize variables
			$IIDstring = null;

			// Add the home item to the pathway
			$this->_pathway->addItem( 'Home', 'index.php' );

			// Get the actual component name
			if (substr($option, 0, 4) == 'com_') {
				$comName = substr($option, 4);
			} else {
				$comName = $option;
			}
			// Handle the ItemID
			if ($ItemID) {
				$IIDstring = '&Itemid='.$ItemID;
			}

			$this->_pathway->addItem( $comName, 'index.php?option='.$option.$IIDstring);
		}

		return true;
	}

	/**
	 * Create the configuration registry
	 *
	 * @access private
	 * @param string $file 	The path to the configuration file
	 * @param string $type	The format type
	 */
	function _createConfiguration($file, $type = 'PHP')
	{
		jimport( 'joomla.registry.registry' );

		require_once( $file );

		// Create the JConfig object
		$config = new JConfig();

		// Get the global configuration object
		$registry =& JFactory::getConfig();
		
		// Load the configuration values into the registry
		$registry->loadObject($config);	
	}

	/**
	 * Create the user session
	 *
	 * Old sessions are flushed based on the configuration value for the cookie
	 * lifetime. If an existing session, then the last access time is updated.
	 * If a new session, a session id is generated and a record is created in
	 * the #__sessions table.
	 *
	 * @access	private
	 * @param	string		The sessions name
	 * @param	boolean 	Use cookies to store the session on the client
	 * @return	void
	 * @since		1.5
	 */
	function _createSession( $name, $useCookies = true)
	{
		JSession::useCookies(true);
		JSession::start(md5( $name ));

		$session = & JTable::getInstance('session', JFactory::getDBO());
		$session->purge( intval( $this->getCfg( 'lifetime' ) ) );

		if ($session->load( JSession::id())) {
			// Session cookie exists, update time in session table
			$session->update();
		} else {

			//make sure the session is cleared if we are logging in
			$option = JRequest::getVar('option');
			if ($option == 'login') {
				JSession::clear();
			}

			//create persistance store in the session
			JSession::set('registry', new JRegistry('session'));

			if (!$session->insert( JSession::id(), $this->getClientId())) {
				die( $session->getError() );
			}

			//TODO::Fix remember me (harden and move out of function)
			//$usercookie = JRequest::getVar( 'usercookie', null, 'COOKIE' );
			//if ($usercookie) {
				// Remember me cookie exists. Login with usercookie info.
			//	$this->login( $usercookie['username'], $usercookie['password'] );
			//}
		}

		JSession::setIdle($this->getCfg('lifetime'));
		JSession::setGcMaxLifetime($this->getCfg('lifetime'));

		// Set user specific editor
		$user =& JFactory::getUser();
		$editor = $user->getParam('editor', $this->getCfg('editor'));

		$config =& JFactory::getConfig();
		$config->setValue('config.editor', $editor);

		return true;
	}

	/**
	 * Gets the client id of the current running application
	 *
	 * @access	public
	 * @return	int			A client identifier
	 * @since		1.5
	 */
	function getClientId( ) {
		return $this->_clientId;
	}

	/**
	 * Is admin interface?
	 *
	 * @access	public
	 * @return	boolean		True if this application is administrator
	 * @since	1.0.2
	 */
	function isAdmin() {
		return ($this->_clientId == 1) ?  true : false;
	}

	/**
	 * Is site interface?
	 *
	 * @access	public
	 * @return	boolean		True of this application is site
	 * @since	1.5
	 */
	function isSite() {
		return ($this->_clientId == 0) ?  true : false;
	}

	/**
	 * Depreceated functions
	 */

	 /**
	 * Depreceated, use JPathWay->addItem() method instead
	 * @since 1.5
	 */
	function appendPathWay( $name, $link = null )
	{
		/*
		 * To provide backward compatability if no second parameter is set
		 * set it to null
		 */
		if ($link == null) {
			$link = '';
		}

		// Add item to the pathway object
		if ($this->_pathway->addItem($name, $link)) {
			return true;
		}

		return false;
  }

	 /**
 	 * Depreceated, use JPathWay->getNamePathWay() method instead
 	 * @since 1.5
 	 */
	function getCustomPathWay() {
		return $this->_pathway->getNamePathWay();
	}

	 /**
	* Depreacted, use JDocument->renderHead instead
	* @since 1.5
	*/
	 function getHead() {
		$document=& JFactory::getDocument();
		return $document->get('head');
	 }

	/**
	* Depreacted, use JDocument->setMetadata instead
	* @since 1.5
	*/
	function addMetaTag( $name, $content, $prepend='', $append='' ) {
		$document=& JFactory::getDocument();
		$document->setMetadata($name, $content);
	}

	/**
	* Depreacted, use JDocument->setMetadata instead
	* @since 1.5
	*/
	function appendMetaTag( $name, $content ) {
		$this->addMetaTag($name, $content);
	}

	/**
	* Depreacted, use JDocument->setMetadata instead
	* @since 1.5
	*/
	function prependMetaTag( $name, $content ) {
		$this->addMetaTag($name, $content);
	}

	/**
	* Depreacted, use JDocument->addCustomTag instead
	* @since 1.5
	*/
	function addCustomHeadTag( $html ) {
		$document=& JFactory::getDocument();
		return $document->addCustomTag($html);
	}

	/**
	* Depreacted, use JApplicationHelper::getItemCount instead
	* @since 1.5
	*/
	function getBlogSectionCount( ) {
		return JApplicationHelper::getItemCount( 'content_blog_section' );
	}

	/**
	* Depreacted, use JApplicationHelper::getItemCount instead
	* @since 1.5
	*/
	function getBlogCategoryCount( ) {
		return JApplicationHelper::getItemCount( 'content_blog_category' );
	}

	/**
	* Depreacted, use JApplicationHelper::getItemCount instead
	* @since 1.5
	*/
	function getGlobalBlogSectionCount( ) {
		return JApplicationHelper::getItemCount( 'content_blog_section' );
	}

	/**
	* Depreacted, use JApplicationHelper::getItemCount instead
	* @since 1.5
	*/
	function getStaticContentCount( ) {
		return JApplicationHelper::getItemCount( 'content_typed' );
	}

	/**
	* Depreacted, use JApplicationHelper::getItemCount instead
	* @since 1.5
	*/
	function getContentItemLinkCount( ) {
		return JApplicationHelper::getItemCount( 'content_item_link');
	}

	/**
	* Depreacted, use JApplicationHelper::getPath instead
	* @since 1.5
	*/
	function getPath($varname, $user_option=null) {
		return JApplicationHelper::getPath ($varname, $user_option);
	}
}

/**
 * Application helper functions
 *
 * @static
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.5
 */
class JApplicationHelper
{

	/**
	 * Gets information on a specific client id.  This method will be useful in
	 * future versions when we start mapping applications in the database.
	 *
	 * @access	public
	 * @param	int		$id	A client identifier
	 * @return	mixed	Object describing the client or false if not known
	 * @since	1.5
	 */
	function getClientInfo($id, $byName = false) {

		static $clients;

		// Only create the array if it does not exist
		if (!is_array($clients))
		{
			$obj = new stdClass();

			// Site Client
			$obj->id		= 0;
			$obj->name	= 'site';
			$obj->path	= JPATH_SITE;
			$clients[0] = clone($obj);

			// Administrator Client
			$obj->id		= 1;
			$obj->name	= 'administrator';
			$obj->path	= JPATH_ADMINISTRATOR;
			$clients[1] = clone($obj);

			// Installation Client
			$obj->id		= 2;
			$obj->name	= 'installation';
			$obj->path	= JPATH_INSTALLATION;
			$clients[2] = clone($obj);
		}

		/*
		 * Are we looking for client information by id or by name?
		 */
		if (!$byName)
		{
			if (!isset($clients[$id])){
				return false;
			} else {
				return $clients[$id];
			}
		}
		else
		{
			foreach ($clients as $client) {
				if ($client->name == strtolower($id)) {
					return $client;
				}
			}
			return false;
		}
	}

	/**
	 * Get the ItemId for a content item
	 *
	 * @access public
	 * @return integer
	 * @since 1.0
	 */
	function getItemid( $id ) {
		require_once (JApplicationHelper::getPath('helper', 'com_content'));
		return JContentHelper::getItemid($id);
	}

	/**
	 * Count the items in the menu for a certain type
	 *
	 * @access public
	 * @return integer
	 * @since 1.5
	 */
	function getItemCount( $type ) {
		$menus = &JMenu::getInstance();
		return count($menus->getItems('type', $type));
	}

	/**
	* Get a path
	*
	* @access public
	* @param string $varname
	* @param string $user_option
	* @return string The requested path
	* @since 1.0
	*/
	function getPath( $varname, $user_option=null )
	{
		// check needed for handling of custom/new module xml file loading
		$check = ( ( $varname == 'mod0_xml' ) || ( $varname == 'mod1_xml' ) );

		if ( !$user_option && !$check ) {
			$user_option = JRequest::getVar('option', null);
		}

		$result = null;
		$name 	= substr( $user_option, 4 );

		switch ($varname) {
			case 'front':
				$result = JApplicationHelper::_checkPath( DS.'components'.DS. $user_option .DS. $name .'.php', 0 );
				break;

			case 'html':
			case 'front_html':
				if ( !( $result = JApplicationHelper::_checkPath( DS.'templates'.DS. JApplication::getTemplate() .DS.'components'.DS. $name .'.html.php', 0 ) ) ) {
					$result = JApplicationHelper::_checkPath( DS.'components'.DS. $user_option .DS. $name .'.html.php', 0 );
				}
				break;

			case 'toolbar':
				$result = JApplicationHelper::_checkPath( DS.'components'.DS. $user_option .DS.'toolbar.'. $name .'.php', -1 );
				break;

			case 'toolbar_html':
				$result = JApplicationHelper::_checkPath( DS.'components'.DS. $user_option .DS.'toolbar.'. $name .'.html.php', -1 );
				break;

			case 'toolbar_default':
			case 'toolbar_front':
				$result = JApplicationHelper::_checkPath( DS.'includes'.DS.'HTML_toolbar.php', 0 );
				break;

			case 'admin':
				$path 	= DS.'components'.DS. $user_option .DS.'admin.'. $name .'.php';
				$result = JApplicationHelper::_checkPath( $path, -1 );
				break;

			case 'admin_html':
				$path	= DS.'components'.DS. $user_option .DS.'admin.'. $name .'.html.php';
				$result = JApplicationHelper::_checkPath( $path, -1 );
				break;

			case 'admin_functions':
				$path	= DS.'components'.DS. $user_option .DS. $name .'.functions.php';
				$result = JApplicationHelper::_checkPath( $path, -1 );
				break;

			case 'class':
				if ( !( $result = JApplicationHelper::_checkPath( DS.'components'.DS. $user_option .DS. $name .'.class.php' ) ) ) {
					$result = JApplicationHelper::_checkPath( DS.'includes'.DS. $name .'.php' );
				}
				break;

			case 'helper':
				$path	= DS.'components'.DS. $user_option .DS. $name .'.helper.php';
				$result = JApplicationHelper::_checkPath( $path );
				break;

			case 'com_xml':
				$path 	= DS.'components'.DS. $user_option .DS. $name .'.xml';
				$result = JApplicationHelper::_checkPath( $path, 1 );
				break;

			case 'mod0_xml':
				$path = DS.'modules'.DS. $user_option .DS. $user_option. '.xml';
				$result = JApplicationHelper::_checkPath( $path );
				break;

			case 'mod1_xml':
				// admin modules
				$path = DS.'modules'.DS. $user_option .DS. $user_option. '.xml';
				$result = JApplicationHelper::_checkPath( $path, -1 );
				break;

			case 'bot_xml':
				// Site plugins
				$path 	= DS.'plugins'.DS. $user_option .'.xml';
				$result = JApplicationHelper::_checkPath( $path, 0 );
				break;

			case 'menu_xml':
				$path 	= DS.'components'.DS.'com_menus'.DS. $user_option .DS. $user_option .'.xml';
				$result = JApplicationHelper::_checkPath( $path, -1 );
				break;
		}

		return $result;
	}

	function parseXMLInstallFile($path)
	{
		// Read the file to see if it's a valid component XML file
		$xml = & JFactory::getXMLParser('Simple');

		if (!$xml->loadFile($path)) {
			unset($xml);
			return false;
		}

		/*
		 * Check for a valid XML root tag.
	     *
		 * Should be 'install', but for backward compatability we will accept 'mosinstall'.
		 */
		if ($xml->document->name() != 'install' && $xml->document->name() != 'mosinstall') {
			unset($xml);
			return false;
		}

		$data = array();

		$element = & $xml->document->name[0];
		$data['name'] = $element ? $element->data() : '';
		$data['type'] = $element ? $xml->document->attributes("type") : '';

		$element = & $xml->document->creationdate[0];
		$data['creationdate'] = $element ? $element->data() : 'Unknown';

		$element = & $xml->document->author[0];
		$data['author'] = $element ? $element->data() : 'Unknown';

		$element = & $xml->document->copyright[0];
		$data['copyright'] = $element ? $element->data() : '';

		$element = & $xml->document->authoremail[0];
		$data['authorEmail'] = $element ? $element->data() : '';

		$element = & $xml->document->authorurl[0];
		$data['authorUrl'] = $element ? $element->data() : '';

		$element = & $xml->document->version[0];
		$data['version'] = $element ? $element->data() : '';

		$element = & $xml->document->description[0];
		$data['description'] = $element ? $element->data() : '';

		$element = & $xml->document->group[0];
		$data['group'] = $element ? $element->data() : '';

		return $data;
	}

	function parseXMLLangMetaFile($path)
	{
		// Read the file to see if it's a valid component XML file
		$xml = & JFactory::getXMLParser('Simple');

		if (!$xml->loadFile($path)) {
			unset($xml);
			return false;
		}

		/*
		 * Check for a valid XML root tag.
	     *
		 * Should be 'langMetaData'.
		 */
		if ($xml->document->name() != 'metafile') {
			unset($xml);
			return false;
		}

		$data = array();

		$element = & $xml->document->name[0];
		$data['name'] = $element ? $element->data() : '';
		$data['type'] = $element ? $xml->document->attributes("type") : '';

		$element = & $xml->document->creationdate[0];
		$data['creationdate'] = $element ? $element->data() : 'Unknown';

		$element = & $xml->document->author[0];
		$data['author'] = $element ? $element->data() : 'Unknown';

		$element = & $xml->document->copyright[0];
		$data['copyright'] = $element ? $element->data() : '';

		$element = & $xml->document->authoremail[0];
		$data['authorEmail'] = $element ? $element->data() : '';

		$element = & $xml->document->authorurl[0];
		$data['authorUrl'] = $element ? $element->data() : '';

		$element = & $xml->document->version[0];
		$data['version'] = $element ? $element->data() : '';

		$element = & $xml->document->description[0];
		$data['description'] = $element ? $element->data() : '';

		$element = & $xml->document->group[0];
		$data['group'] = $element ? $element->group() : '';
		return $data;
	}

	/**
	 * Tries to find a file in the administrator or site areas
	 *
	 * @access private
	 * @param string 	$parth			A file name
	 * @param integer 	$checkAdmin		0 to check site only, 1 to check site and admin, -1 to check admin only
	 * @since 1.5
	 */
	function _checkPath( $path, $checkAdmin=1 )
	{
		$file = JPATH_SITE . $path;
		if ($checkAdmin > -1 && file_exists( $file )) {
			return $file;
		} else if ($checkAdmin != 0) {
			$file = JPATH_ADMINISTRATOR . $path;
			if (file_exists( $file )) {
				return $file;
			}
		}

		return null;
	}

}
?>