<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Installation
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
* @package		Joomla
* @subpackage	Installation
*/
class JInstallationController
{
	/**
	 * @param patTemplate A template object
	 */
	function chooseLanguage($vars)
	{
		$native = JLanguageHelper::detectLanguage();
		$forced = JInstallationHelper::getLocalise();
		if ( !empty( $forced['lang'] ) ){
			$native = $forced['lang'];
		}
		$lists = array ();
		$lists['langs'] = JLanguageHelper::createLanguageList($native);

		return JInstallationView::chooseLanguage($lists);
	}

	/**
	 * @param patTemplate A template object
	 */
	function preInstall($vars)
	{
		$lists = array ();

		$phpOptions[] = array (
			'label' => JText::_('PHP version').' >= 4.3.0',
			'state' => phpversion() < '4.3' ? 'No' : 'Yes'
		);
		$phpOptions[] = array (
			'label' => '- '.JText::_('zlib compression support'),
			'state' => extension_loaded('zlib') ? 'Yes' : 'No'
		);
		$phpOptions[] = array (
			'label' => '- '.JText::_('XML support'),
			'state' => extension_loaded('xml') ? 'Yes' : 'No',
			'statetext' => extension_loaded('xml') ? 'Yes' : 'No'
		);
		$phpOptions[] = array (
			'label' => '- '.JText::_('MySQL support'),
			'state' => (function_exists('mysql_connect') || function_exists('mysqli_connect')) ? 'Yes' : 'No'
		);
		if (extension_loaded( 'mbstring' )) {
			$mbDefLang = strtolower( ini_get( 'mbstring.language' ) ) == 'neutral';
			$phpOptions[] = array (
				'label' => JText::_( 'MB language is default' ),
				'state' => $mbDefLang ? 'Yes' : 'No',
				'notice' => $mbDefLang ? '' : JText::_( 'NOTICEMBLANGNOTDEFAULT' )
			);
			$mbOvl = ini_get('mbstring.func_overload') != 0;
			$phpOptions[] = array (
				'label' => JText::_('MB string overload off'),
				'state' => !$mbOvl ? 'Yes' : 'No',
				'notice' => $mbOvl ? JText::_('NOTICEMBSTRINGOVERLOAD') : ''
			);
		}
		$sp = '';
		/*$phpOptions[] = array (
			'label' => JText::_('Session path set'),
			'state' => ($sp = ini_get('session.save_path')) ? 'Yes' : 'No'
		);
		$phpOptions[] = array (
			'label' => JText::_('Session path writeable'),
			'state' => is_writable($sp) ? 'Yes' : 'No'
		);*/
		$cW = (@ file_exists('../configuration.php') && @ is_writable('../configuration.php')) || is_writable('..');
		$phpOptions[] = array (
			'label' => 'configuration.php '.JText::_('writeable'),
			'state' => $cW ? 'Yes' : 'No',
			'notice' => $cW ? '' : JText::_('NOTICEYOUCANSTILLINSTALL')
		);
		$lists['phpOptions'] = & $phpOptions;

		$phpRecommended = array (
			array (
				JText::_('Safe Mode'),
				'safe_mode',
				'OFF'
			),
			array (
				JText::_('Display Errors'),
				'display_errors',
				'ON'
			),
			array (
				JText::_('File Uploads'),
				'file_uploads',
				'ON'
			),
			array (
				JText::_('Magic Quotes Runtime'),
				'magic_quotes_runtime',
				'OFF'
			),
			array (
				JText::_('Register Globals'),
				'register_globals',
				'OFF'
			),
			array (
				JText::_('Output Buffering'),
				'output_buffering',
				'OFF'
			),
			array (
				JText::_('Session auto start'),
				'session.auto_start',
				'OFF'
			),
		);

		foreach ($phpRecommended as $setting)
		{
			$lists['phpSettings'][] = array (
				'label' => $setting[0],
				'setting' => $setting[2],
				'actual' => get_php_setting( $setting[1] ),
				'state' => get_php_setting($setting[1]) == $setting[2] ? 'Yes' : 'No'
			);
		}

		return JInstallationView::preInstall( $vars, $lists );
	}

	/**
	 * Gets the parameters for database creation
	 */
	function license($vars)
	{
		return JInstallationView::license($vars);
	}

	/**
	 * Gets the parameters for database creation
	 */
	function dbConfig($vars)
	{
		global $mainframe;

		if (!isset ($vars['DBPrefix'])) {
			$vars['DBPrefix'] = 'jos_';
		}

		$lists = array ();
		$files = array ('mysql', 'mysqli',);
		$db = JInstallationHelper::detectDB();
		foreach ($files as $file)
		{
			$option = array ();
			$option['text'] = $file;
			if (strcasecmp($option['text'], $db) == 0)
			{
				$option['selected'] = 'selected="true"';
			}
			$lists['dbTypes'][] = $option;
		}

		$doc =& JFactory::getDocument();
		return JInstallationView::dbConfig($vars, $lists);
	}

	/**
	 * Gets the parameters for database creation
	 * @return boolean True if successful
	 */
	function makeDB($vars)
	{
		// Initialize variables
		$errors = null;

		$lang 		= JArrayHelper::getValue($vars, 'lang', 'en-GB');
		$DBcreated	= JArrayHelper::getValue($vars, 'DBcreated', '0');

		$DBtype 	= JArrayHelper::getValue($vars, 'DBtype', 'mysql');
		$DBhostname = JArrayHelper::getValue($vars, 'DBhostname', '');
		$DBuserName = JArrayHelper::getValue($vars, 'DBuserName', '');
		$DBpassword = JArrayHelper::getValue($vars, 'DBpassword', '');
		$DBname 	= JArrayHelper::getValue($vars, 'DBname', '');
		$DBPrefix 	= JArrayHelper::getValue($vars, 'DBPrefix', 'jos_');
		$DBOld 		= JArrayHelper::getValue($vars, 'DBOld', 'bu');
//		$DBSample = mosGetParam($vars, 'DBSample', 1);
		$DButfSupport 	= intval(JArrayHelper::getValue($vars, 'DButfSupport', 0));
		$DBversion 		= JArrayHelper::getValue($vars, 'DBversion', '');

		// these 3 errors should be caught by the javascript in dbConfig
		if ($DBtype == '')
		{
			return JInstallationView::error($vars, JText::_('validType'), 'dbconfig');
		}
		if (!$DBhostname || !$DBuserName || !$DBname)
		{
			return JInstallationView::error($vars, JText::_('validDBDetails'), 'dbconfig');
		}
		if ($DBname == '')
		{
			return JInstallationView::error($vars, JText::_('emptyDBName'), 'dbconfig');
		}

		if (!$DBcreated)
		{
			jimport('joomla.database.database');
			$db = & JDatabase::getInstance($DBtype, $DBhostname, $DBuserName, $DBpassword, $DBname, $DBPrefix);

			if ($err = $db->getErrorNum()) {
				if ($err == 3) {
					// connection ok, need to create database
					if (JInstallationHelper::createDatabase($db, $DBname, $DButfSupport, $DBcollation)) {
						// make the new connection to the new database
						$db = NULL;
						$db = & JDatabase::getInstance($DBtype, $DBhostname, $DBuserName, $DBpassword, $DBname, $DBPrefix);
					} else {
						$error = $db->getErrorMsg();
						return JInstallationView::error($vars, array (JText::sprintf('WARNCREATEDB', $DBname)), 'dbconfig', $error);
					}
				} else {
					// connection failed
					//JInstallationView::error( $vars, array( 'Could not connect to the database.  Connector returned', $db->getErrorNum() ), 'dbconfig', $db->getErrorMsg() );
					return JInstallationView::error($vars, array (JText::sprintf('WARNNOTCONNECTDB', $db->getErrorNum())), 'dbconfig', $db->getErrorMsg());
				}
			} else {
				// pre-existing database - need to set character set to utf8
				// will only affect MySQL 4.1.2 and up
				JInstallationHelper::setDBCharset($db, $DBname);
			}

			$db = & JDatabase::getInstance($DBtype, $DBhostname, $DBuserName, $DBpassword, $DBname, $DBPrefix);

			if ($DBOld == 'rm') {
				if (JInstallationHelper::deleteDatabase($db, $DBname, $DBPrefix, $errors)) {
					return JInstallationView::error($vars, JText::_('WARNDELETEDB'), 'dbconfig', JInstallationHelper::errors2string($errors));
				}
			}
			else
			{
				/*
				 * We assume since we aren't deleting the database that we need
				 * to back it up :)
				 */
				if (JInstallationHelper::backupDatabase($db, $DBname, $DBPrefix, $errors)) {
					return JInstallationView::error($vars, JText::_('WARNBACKINGUPDB'), 'dbconfig', JInstallationHelper::errors2string($errors));
				}
			}

			$type = $DBtype;
			if ($type == 'mysqli') {
				$type = 'mysql';
			}

			// set collation and use utf-8 compatibile script if appropriate
			if ($DButfSupport) {
				$dbscheme = 'sql'.DS.$type.DS.'joomla.sql';
			} else {
				$dbscheme = 'sql'.DS.$type.DS.'joomla_backward.sql';
			}

			if (JInstallationHelper::populateDatabase($db, $dbscheme, $errors) > 0)
			{
				return JInstallationView::error($vars, JText::_('WARNPOPULATINGDB'), 'dbconfig', JInstallationHelper::errors2string($errors));
			}

		}

		return true;
	}

	/**
	 * Gets ftp configuration parameters
	 */
	function ftpConfig($vars, $DBcreated = '0')
	{
		global $mainframe;

		// Require the xajax library
		require_once( JPATH_BASE.DS.'includes'.DS.'xajax'.DS.'xajax.inc.php' );

		// Instantiate the xajax object and register the function
		$xajax = new xajax(JURI::base().'includes/jajax.php');
		$xajax->registerFunction(array('getFtpRoot', 'JAJAXHandler', 'ftproot'));
		$xajax->registerFunction(array('FTPVerify', 'JAJAXHandler', 'ftpverify'));
		//$xajax->debugOn();

		$vars['DBcreated'] = JArrayHelper::getValue($vars, 'DBcreated', $DBcreated);
		$strip = get_magic_quotes_gpc();

		if (!isset ($vars['ftpEnable'])) {
			$vars['ftpEnable'] = '1';
		}
		if (!isset ($vars['ftpHost'])) {
			$vars['ftpHost'] = '127.0.0.1';
		}
		if (!isset ($vars['ftpPort'])) {
			$vars['ftpPort'] = '21';
		}
		if (!isset ($vars['ftpUser'])) {
			$vars['ftpUser'] = '';
		}
		if (!isset ($vars['ftpPassword'])) {
			$vars['ftpPassword'] = '';
		}

		$doc =& JFactory::getDocument();
		$doc->addCustomTag($xajax->getJavascript('', 'includes/js/xajax.js', 'includes/js/xajax.js'));

		return JInstallationView::ftpConfig($vars);
	}

	/**
	 * Finishes configuration parameters
	 */
	function mainConfig($vars)
	{
		global $mainframe;

		// get ftp configuration into registry for use in case of safe mode
		if($vars['ftpEnable']) {
			JInstallationHelper::setFTPCfg( $vars );
		}

		// Require the xajax library
		require_once( JPATH_BASE.DS.'includes'.DS.'xajax'.DS.'xajax.inc.php' );

		// Instantiate the xajax object and register the function
		$xajax = new xajax(JURI::base().'includes/jajax.php');
		$xajax->registerFunction(array('instDefault', 'JAJAXHandler', 'sampledata'));
//		$xajax->debugOn();
		$xajax->errorHandlerOn();
		$doc =& JFactory::getDocument();
		$doc->addCustomTag($xajax->getJavascript('', 'includes/js/xajax.js', 'includes/js/xajax.js'));

		// Deal with possible sql script uploads from this stage
		$vars['loadchecked'] = 0;
		if (JRequest::getVar( 'sqlupload', 0, 'post', 'int' ) == 1)
		{
			$vars['sqlresponse'] = JInstallationHelper::uploadSql( $vars );
			$vars['dataloaded'] = '1';
			$vars['loadchecked'] = 1;
		}
		if (JRequest::getVar( 'migrationupload', 0, 'post', 'int' ) == 1)
		{
			$vars['migresponse'] = JInstallationHelper::uploadSql( $vars, true );
			$vars['dataloaded'] = '1';
			$vars['loadchecked'] = 2;
		}

//		$strip = get_magic_quotes_gpc();

		if (isset ($vars['siteName']))
		{
			$vars['siteName'] = stripslashes(stripslashes($vars['siteName']));
		}

		$folders = array (
			'administrator/backups',
			'administrator/cache',
			'administrator/components',
			'administrator/language',
			'administrator/modules',
			'administrator/templates',
			'cache',
			'components',
			'images',
			'images/banners',
			'images/stories',
			'language',
			'plugins',
			'plugins/content',
			'plugins/editors',
			'plugins/search',
			'plugins/system',
			'tmp',
			'modules',
			'templates',
		);

		// Now lets make sure we have permissions set on the appropriate folders
//		foreach ($folders as $folder)
//		{
//			if (!JInstallationHelper::setDirPerms( $folder, $vars ))
//			{
//				$lists['folderPerms'][] = $folder;
//			}
//		}

		return JInstallationView::mainConfig($vars);
	}

	function saveConfig(&$vars)
	{
		global $mainframe;

		$lang =& JFactory::getLanguage();

		// Import authentication library
		jimport( 'joomla.user.helper' );

		// Set some needed variables
		$vars['siteUrl']		= $mainframe->getSiteURL();
		$vars['secret']			= JUserHelper::genRandomPassword(16);
		$vars['hidePdf']		= intval(!is_writable(JPATH_SITE.DS.'tmp'));

		$vars['offline']		= JText::_( 'STDOFFLINEMSG' );
		$vars['errormsg']		= JText::_( 'STDERRORMSG' );
		$vars['metadesc']		= JText::_( 'STDMETADESC' );
		$vars['metakeys']		= JText::_( 'STDMETAKEYS' );
		$vars['tmp_path']		= JPATH_ROOT.DS.'tmp';
		$vars['log_path']		= JPATH_ROOT.DS.'logs';

		// set default language
		$forced = JInstallationHelper::getLocalise();
		if ( empty($forced['lang']) ) {
			$vars['deflang'] = 'en-GB';
			$vars['bclang'] = 'english';
		} else {
			$vars['deflang'] = $forced['lang'];
			$vars['bclang'] = $lang->getBackwardLang();
		}

		if ( empty( $forced['helpurl'] ) ) {
			$vars['helpurl'] = 'http://help.joomla.org';
		} else {
			$vars['helpurl'] = $forced['helpurl'];
		}

		// If FTP has not been enabled, set the value to 0
		if (!isset($vars['ftpEnable']))
		{
			$vars['ftpEnable'] = 0;
		}

		/*
		 * Trim the last slash from the FTP root, as the FTP root usually replaces JPATH_ROOT.
		 * If the path had a trailing slash, this would lead to double slashes, like "/joomla//configuration.php"
		 */
		if (isset($vars['ftpRoot'])) {
			$vars['ftpRoot'] = rtrim($vars['ftpRoot'], '/');
		}

		switch ($vars['DBtype']) {

			case 'mssql' :
				$vars['ZERO_DATE'] = '1/01/1990';
				break;

			default :
				$vars['ZERO_DATE'] = '0000-00-00 00:00:00';
				break;
		}


		JInstallationHelper::createAdminUser($vars);

		$tmpl = & JInstallationView::createTemplate();
		$tmpl->readTemplatesFromFile('configuration.html');
		$tmpl->addVars('configuration', $vars, 'var_');

		$buffer = $tmpl->getParsedTemplate('configuration');
		$path = JPATH_CONFIGURATION.DS.'configuration.php';

		if (file_exists($path)) {
			$canWrite = is_writable($path);
		} else {
			$canWrite = is_writable(JPATH_CONFIGURATION);
		}

		/*
		 * If the file exists but isn't writable OR if the file doesn't exist and the parent directory
		 * is not writable we need to use FTP
		 */
		$ftpFlag = false;
		if ((file_exists($path) && !is_writable($path)) || (!file_exists($path) && !is_writable(dirname($path)))) {
			$ftpFlag = true;
		}

		// Check for safe mode
		if (ini_get('safe_mode'))
		{
			$ftpFlag = true;
		}

		// Enable/Disable override
		if (!isset($vars['ftpEnable']) || ($vars['ftpEnable'] != 1))
		{
			$ftpFlag = false;
		}

		if ($ftpFlag == true)
		{
			// Connect the FTP client
			jimport('joomla.client.ftp');
			jimport('joomla.filesystem.path');

			$ftp = & JFTP::getInstance($vars['ftpHost'], $vars['ftpPort']);
			$ftp->login($vars['ftpUser'], $vars['ftpPassword']);

			// Translate path for the FTP account
			$file = JPath::clean(str_replace(JPATH_CONFIGURATION, $vars['ftpRoot'], $path), '/');

			// Use FTP write buffer to file
			if (!$ftp->write($file, $buffer)) {
				return $buffer;
			}

			$ftp->quit();
			return '';

		}
		else
		{
			if ($canWrite) {
				file_put_contents($path, $buffer);
				return '';
			} else {
				return $buffer;
			}
		}
	}

	/**
	 * Displays the finish screen
	 */
	function finish($vars, $buffer = '')
	{
		global $mainframe;

		$vars['siteurl'] = $mainframe->getSiteURL();
		$vars['adminurl'] = $vars['siteurl'].'administrator/';

		return JInstallationView::finish($vars, $buffer);
	}
}

/**
* @package		Joomla
* @subpackage	Installation
*/
class JInstallationHelper
{
	/**
	 * @return string A guess at the db required
	 */
	function detectDB()
	{
		$map = array ('mysql_connect' => 'mysql', 'mysqli_connect' => 'mysqli', 'mssql_connect' => 'mssql');
		foreach ($map as $f => $db)
		{
			if (function_exists($f))
			{
				return $db;
			}
		}
		return 'mysql';
	}

	/**
	 * @param array
	 * @return string
	 */
	function errors2string(& $errors)
	{
		$buffer = '';
		foreach ($errors as $error)
		{
			$buffer .= 'SQL='.$error['msg'].":\n- - - - - - - - - -\n".$error['sql']."\n= = = = = = = = = =\n\n";
		}
		return $buffer;
	}
	/**
	 * Creates a new database
	 * @param object Database connector
	 * @param string Database name
	 * @param boolean utf-8 support
	 * @param string Selected collation
	 * @return boolean success
	 */
	function createDatabase(& $db, $DBname, $DButfSupport, $DBcollation)
	{
		if ($DButfSupport)
		{
			$sql = "CREATE DATABASE `$DBname` CHARACTER SET `utf8` COLLATE `$DBcollation`";
		}
		else
		{
			$sql = "CREATE DATABASE `$DBname`";
		}

		$db->setQuery($sql);
		$db->query();
		$result = $db->getErrorNum();

		if ($result != 0)
		{
			return false;
		}

		return true;
	}

	/**
	 * Sets character set of the database to utf-8 with selected collation
	 * Used in instances of pre-existing database
	 * @param object Database object
	 * @param string Database name
	 * @param string Selected collation
	 * @return boolean success
	 */
	function setDBCharset(& $db, $DBname)
	{
		if ($db->hasUTF())
		{
			$sql = "ALTER DATABASE `$DBname` CHARACTER SET `utf8`";
			$db->setQuery($sql);
			$db->query();
			$result = $db->getErrorNum();
			if ($result != 0) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Backs up existing tables
	 * @param object Database connector
	 * @param array An array of errors encountered
	 */
	function backupDatabase(& $db, $DBname, $DBPrefix, & $errors)
	{
		// Initialize backup prefix variable
		// TODO: Should this be user-defined?
		$BUPrefix = 'bak_';

		$query = "SHOW TABLES FROM `$DBname`";
		$db->setQuery($query);
		$errors = array ();
		if ($tables = $db->loadResultArray())
		{
			foreach ($tables as $table)
			{
				if (strpos($table, $DBPrefix) === 0)
				{
					$butable = str_replace($DBPrefix, $BUPrefix, $table);
					$query = "DROP TABLE IF EXISTS `$butable`";
					$db->setQuery($query);
					$db->query();
					if ($db->getErrorNum())
					{
						$errors[$db->getQuery()] = $db->getErrorMsg();
					}
					$query = "RENAME TABLE `$table` TO `$butable`";
					$db->setQuery($query);
					$db->query();
					if ($db->getErrorNum())
					{
						$errors[$db->getQuery()] = $db->getErrorMsg();
					}
				}
			}
		}

		return count($errors);
	}
	/**
	 * Deletes all database tables
	 * @param object Database connector
	 * @param array An array of errors encountered
	 */
	function deleteDatabase(& $db, $DBname, $DBPrefix, & $errors)
	{
		$query = "SHOW TABLES FROM `$DBname`";
		$db->setQuery($query);
		$errors = array ();
		if ($tables = $db->loadResultArray())
		{
			foreach ($tables as $table)
			{
				if (strpos($table, $DBPrefix) === 0)
				{
					$query = "DROP TABLE IF EXISTS `$table`";
					$db->setQuery($query);
					$db->query();
					if ($db->getErrorNum())
					{
						$errors[$db->getQuery()] = $db->getErrorMsg();
					}
				}
			}
		}

		return count($errors);
	}

	/**
	 *
	 */
	function populateDatabase(& $db, $sqlfile, & $errors)
	{
		if( !($buffer = file_get_contents($sqlfile)) )
		{
			return -1;
		}
		$queries = JInstallationHelper::splitSql($buffer);

		foreach ($queries as $query)
		{
			$query = trim($query);
			if ($query != '' && $query {0} != '#')
			{
				$db->setQuery($query);
				$db->query();
				JInstallationHelper::getDBErrors($errors, $db );
			}
		}
		return count($errors);
	}

	/**
	 * @param string
	 * @return array
	 */
	function splitSql($sql)
	{
		$sql = trim($sql);
		$sql = preg_replace("/\n\#[^\n]*/", '', "\n".$sql);
		$buffer = array ();
		$ret = array ();
		$in_string = false;

		for ($i = 0; $i < strlen($sql) - 1; $i ++) {
			if ($sql[$i] == ";" && !$in_string)
			{
				$ret[] = substr($sql, 0, $i);
				$sql = substr($sql, $i +1);
				$i = 0;
			}

			if ($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\")
			{
				$in_string = false;
			}
			elseif (!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset ($buffer[0]) || $buffer[0] != "\\"))
			{
				$in_string = $sql[$i];
			}
			if (isset ($buffer[1]))
			{
				$buffer[0] = $buffer[1];
			}
			$buffer[1] = $sql[$i];
		}

		if (!empty ($sql))
		{
			$ret[] = $sql;
		}
		return ($ret);
	}

	/**
	 * Calculates the file/dir permissions mask
	 */
	function getFilePerms($input, $type = 'file')
	{
		$perms = '';
		if (JArrayHelper::getValue($input, $type.'PermsMode', 0))
		{
			$action = ($type == 'dir') ? 'Search' : 'Execute';
			$perms = '0'. (JArrayHelper::getValue($input, $type.'PermsUserRead', 0) * 4 + JArrayHelper::getValue($input, $type.'PermsUserWrite', 0) * 2 + JArrayHelper::getValue($input, $type.'PermsUser'.$action, 0)). (JArrayHelper::getValue($input, $type.'PermsGroupRead', 0) * 4 + JArrayHelper::getValue($input, $type.'PermsGroupWrite', 0) * 2 + JArrayHelper::getValue($input, $type.'PermsGroup'.$action, 0)). (JArrayHelper::getValue($input, $type.'PermsWorldRead', 0) * 4 + JArrayHelper::getValue($input, $type.'PermsWorldWrite', 0) * 2 + JArrayHelper::getValue($input, $type.'PermsWorld'.$action, 0));
		}
		return $perms;
	}

	/**
	 * Creates the admin user
	 */
	function createAdminUser(& $vars)
	{
		$DBtype		= JArrayHelper::getValue($vars, 'DBtype', 'mysql');
		$DBhostname	= JArrayHelper::getValue($vars, 'DBhostname', '');
		$DBuserName	= JArrayHelper::getValue($vars, 'DBuserName', '');
		$DBpassword	= JArrayHelper::getValue($vars, 'DBpassword', '');
		$DBname		= JArrayHelper::getValue($vars, 'DBname', '');
		$DBPrefix	= JArrayHelper::getValue($vars, 'DBPrefix', '');

		$adminPassword	= JArrayHelper::getValue($vars, 'adminPassword', '');
		$adminEmail		= JArrayHelper::getValue($vars, 'adminEmail', '');

		$cryptpass = md5($adminPassword);
		$vars['adminLogin'] = 'admin';

		jimport('joomla.database.database');
		$db = & JDatabase::getInstance($DBtype, $DBhostname, $DBuserName, $DBpassword, $DBname, $DBPrefix);

		// create the admin user
		$installdate 	= date('Y-m-d H:i:s');
		$nullDate 		= $db->getNullDate();
		$query = "INSERT INTO #__users VALUES (62, 'Administrator', 'admin', ".$db->Quote($adminEmail).", ".$db->Quote($cryptpass).", 'Super Administrator', 0, 1, 25, '$installdate', '$nullDate', '', '')";
		$db->setQuery($query);
		if (!$db->query())
		{
			// is there already and existing admin in migrated data
			if ( $db->getErrorNum() == 1062 )
			{
				$vars['adminLogin'] = JText::_('Admin login in migrated content was kept');
				$vars['adminPassword'] = JText::_('Admin password in migrated content was kept');
				return;
			}
			else
			{
				echo $db->getErrorMsg();
				return;
			}
		}

		// add the ARO (Access Request Object)
		$query = "INSERT INTO #__core_acl_aro VALUES (10,'users','62',0,'Administrator',0)";
		$db->setQuery($query);
		if (!$db->query())
		{
			echo $db->getErrorMsg();
			return;
		}

		// add the map between the ARO and the Group
		$query = "INSERT INTO #__core_acl_groups_aro_map VALUES (25,'',10)";
		$db->setQuery($query);
		if (!$db->query())
		{
			echo $db->getErrorMsg();
			return;
		}
	}

	/**
	 * Check the webserver user permissions for writing files/folders
	 *
	 * @static
	 * @return	boolean	True if correct permissions exist
	 * @since	1.5
	 */
	function fsPermissionsCheck()
	{
		if(!is_writable(JPATH_ROOT.DS.'tmp')) {
			return false;
		}
		if(!mkdir(JPATH_ROOT.DS.'tmp'.DS.'test', 0755)) {
			return false;
		}
		if(!copy(JPATH_ROOT.DS.'tmp'.DS.'index.html', JPATH_ROOT.DS.'tmp'.DS.'test'.DS.'index.html')) {
			return false;
		}
		if(!chmod(JPATH_ROOT.DS.'tmp'.DS.'test'.DS.'index.html', 0777)) {
			return false;
		}
		if(!unlink(JPATH_ROOT.DS.'tmp'.DS.'test'.DS.'index.html')) {
			return false;
		}
		if(!rmdir(JPATH_ROOT.DS.'tmp'.DS.'test')) {
			return false;
		}
		return true;
	}

	/**
	 * Find the ftp filesystem root for a given user/pass pair
	 *
	 * @static
	 * @param	string	$user	Username of the ftp user to determine root for
	 * @param	string	$pass	Password of the ftp user to determine root for
	 * @return	string	Filesystem root for given FTP user
	 * @since 1.5
	 */
	function findFtpRoot($user, $pass, $host='127.0.0.1', $port='21')
	{
		jimport('joomla.client.ftp');
		$ftpPaths = array();

		// Connect and login to the FTP server (using binary transfer mode to be able to compare files)
		$ftp =& JFTP::getInstance($host, $port, array('type'=>FTP_BINARY));
		if (!$ftp->isConnected()) {
			return JError::raiseError('31', 'NOCONNECT');
		}
		if (!$ftp->login($user, $pass)) {
			return JError::raiseError('31', 'NOLOGIN');
		}

		// Get list of folders in the CWD
		$ftpFolders = $ftp->listDetails(null, 'folders');
		if ($ftpFolders === false || count($ftpFolders) == 0) {
			return JError::raiseError('SOME_ERROR_CODE', 'NODIRECTORYLISTING');
		}
		for ($i=0, $n=count($ftpFolders); $i<$n; $i++) {
			$ftpFolders[$i] = $ftpFolders[$i]['name'];
		}

		// Check if Joomla! is installed at the FTP root
		$dirList = array('administrator', 'components', 'installation', 'language', 'libraries', 'plugins');
		if (count(array_diff($dirList, $ftpFolders)) == 0) {
			$ftpPaths[] = '/';
		}

		// Process the list: cycle through all parts of JPATH_SITE, beginning from the end
		$parts		= explode(DS, JPATH_SITE);
		$tmpPath	= '';
		for ($i=count($parts)-1; $i>=0; $i--)
		{
			$tmpPath = '/'.$parts[$i].$tmpPath;
			if (in_array($parts[$i], $ftpFolders)) {
				$ftpPaths[] = $tmpPath;
			}
		}

		// Check all possible paths for the real Joomla! installation
		$checkValue = file_get_contents(JPATH_LIBRARIES.DS.'joomla'.DS.'version.php');
		foreach ($ftpPaths as $tmpPath)
		{
			$filePath = rtrim($tmpPath, '/').'/libraries/joomla/version.php';
			@$ftp->read($filePath, $buffer);
			if ($buffer == $checkValue)
			{
				$ftpPath = $tmpPath;
				break;
			}
		}

		// Close the FTP connection
		$ftp->quit();

		// Return the FTP root path
		if (isset($ftpPath)) {
			return $ftpPath;
		} else {
			return JError::raiseError('SOME_ERROR_CODE', 'Unable to autodetect the FTP root folder');
		}
	}

	/**
	 * Verify the FTP configuration values are valid
	 *
	 * @static
	 * @param	string	$user	Username of the ftp user to determine root for
	 * @param	string	$pass	Password of the ftp user to determine root for
	 * @return	mixed	Boolean true on success or JError object on fail
	 * @since	1.5
	 */
	function FTPVerify($user, $pass, $root, $host='127.0.0.1', $port='21')
	{
		jimport('joomla.client.ftp');
		$ftp = & JFTP::getInstance($host, $port);

		// Verify connection
		if (!$ftp->isConnected()) {
			return JError::raiseWarning('31', 'NOCONNECT');
		}

		// Verify username and password
		if (!$ftp->login($user, $pass)) {
			return JError::raiseWarning('31', 'NOLOGIN');
		}

		// Verify root path exists
		if (!$ftp->chdir($root)) {
			return JError::raiseWarning('31', 'NOROOT');
		}

		// Verify valid root path
		$checkList = array('CHANGELOG.php', 'COPYRIGHT.php', 'feed.php', 'index.php', 'INSTALL.php', 'LICENSE.php');
		$rootList = $ftp->listNames();
		$ftp->quit();
		foreach ($checkList as $check) {
			if (!in_array($check, $rootList)) {
				return JError::raiseWarning('31', 'INVALIDROOT');
			}
		}

		// TODO: Perhaps check various needed ftp functions to see if they exist and are available

		return true;
	}

	/**
	 * Set default folder permissions
	 *
	 * @param string $path The full file path
	 * @param string $buffer The buffer to write
	 * @return boolean True on success
	 * @since 1.5
	 */
	function setDirPerms($dir, &$srv)
	{
		jimport('joomla.filesystem.path');

		/*
		 * Initialize variables
		 */
		$ftpFlag = false;
		$ftpRoot = $srv['ftpRoot'];

		/*
		 * First we need to determine if the path is chmodable
		 */
		if (!JPath::canChmod(JPath::clean(JPATH_SITE.DS.$dir)))
		{
			$ftpFlag = true;
		}

		// Do NOT use ftp if it is not enabled
		if (!$srv['ftpEnable'])
		{
			$ftpFlag = false;
		}

		if ($ftpFlag == true)
		{
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = & JFTP::getInstance($srv['ftpHost'], $srv['ftpPort']);
			$ftp->login($srv['ftpUser'],$srv['ftpPassword']);

			//Translate path for the FTP account
			$path = JPath::clean($ftpRoot."/".$dir);

			/*
			 * chmod using ftp
			 */
			if (!$ftp->chmod($path, '0755'))
			{
				$ret = false;
			}

			$ftp->quit();
			$ret = true;
		}
		else
		{

			$path = JPath::clean(JPATH_SITE.DS.$dir);

			if (!@ chmod($path, octdec('0755')))
			{
				$ret = false;
			}
			else
			{
				$ret = true;
			}
		}

		return $ret;
	}

	/**
	 * Uploads a sql script and executes it. Script can be text file or zip/gz packed
	 *
	 * @static
	 * @param array The installation variables
	 * @param boolean true if the script is a migration script
	 * @return string Success or error messages
	 * @since 1.5
	 */
	function uploadSql( &$args, $migration = false )
	{
		global $mainframe;
		$archive = '';
		$script = '';

		/*
		 * Check for iconv
		 */
		if ($migration && !function_exists( 'iconv' ) ) {
			return JText::_( 'WARNICONV' );
		}


		/*
		 * Get the uploaded file information
		 */
		if( $migration )
		{
			$sqlFile	= JRequest::getVar('migrationFile', '', 'files', 'array');
		}
		else
		{
			$sqlFile	= JRequest::getVar('sqlFile', '', 'files', 'array');
		}

		/*
		 * Make sure that file uploads are enabled in php
		 */
		if (!(bool) ini_get('file_uploads'))
		{
			return JText::_('WARNINSTALLFILE');
		}

		/*
		 * Make sure that zlib is loaded so that the package can be unpacked
		 */
		if (!extension_loaded('zlib'))
		{
			return JText::_('WARNINSTALLZLIB');
		}

		/*
		 * If there is no uploaded file, we have a problem...
		 */
		if (!is_array($sqlFile) || $sqlFile['size'] < 1)
		{
			return JText::_('WARNNOFILE');
		}

		/*
		 * Move uploaded file
		 */
		// Set permissions for tmp dir
		JInstallationHelper::_chmod(JPATH_SITE.DS.'tmp', 0777);
		jimport('joomla.filesystem.file');
		$uploaded = JFile::upload($sqlFile['tmp_name'], JPATH_SITE.DS.'tmp'.DS.$sqlFile['name']);

		if( !eregi('.sql$', $sqlFile['name']) )
		{
			$archive = JPATH_SITE.DS.'tmp'.DS.$sqlFile['name'];
		}
		else
		{
			$script = JPATH_SITE.DS.'tmp'.DS.$sqlFile['name'];
		}

		// unpack archived sql files
		if ($archive )
		{
			$package = JInstallationHelper::unpack( $archive, $args );
			if ( $package === false )
			{
				return JText::_('WARNUNPACK');
			}
			$script = $package['folder'].$package['script'];
		}

		jimport('joomla.database.database');
		$db = & JDatabase::getInstance($args['DBtype'], $args['DBhostname'], $args['DBuserName'], $args['DBpassword'], $args['DBname'], $args['DBPrefix']);

		/*
		 * If migration perform manipulations on script file before population
		 */
		if ( $migration )
		{
			$script = JInstallationHelper::preMigrate($script, $args, $db);
			if ( $script == false )
			{
				return JText::_( 'Script operations failed' );
			}
		}

		$errors = null;
		$msg = '';
		$result = JInstallationHelper::populateDatabase($db, $script, $errors);

		/*
		 * If migration, perform post population manipulations (menu table construction)
		 */
		$migErrors = null;
		if ( $migration )
		{
			$migResult = JInstallationHelper::postMigrate( $db, $migErrors, $args );

			if ( $migResult != 0 )
			{
				/*
				 * Merge populate and migrate processing errors
				 */
				if( $result == 0 )
				{
					$result = $migResult;
					$errors = $migErrors;
				}
				else
				{
					$result += $migResult;
					$errors = array_merge( $errors, $migErrors );
				}
			}
		}


		/*
		 * prepare sql error messages if returned from populate and migrate
		 */
		if (!is_null($errors))
		{
			foreach($errors as $error)
			{
				$msg .= stripslashes( $error['msg'] );
				$msg .= chr(13)."-------------".chr(13);
				$txt = '<textarea cols="40" rows="4" name="instDefault" readonly="readonly" >'.JText::_("Database Errors Reported").chr(13).$msg.'</textarea>';
			}
		}
		else
		{
			// consider other possible errors from populate
			$msg = $result == 0 ? JText::_('SQL script installed successfully') : JText::_('Error installing SQL script') ;
			$txt = '<input size="50" value="'.$msg.'" readonly="readonly" />';
		}

		/*
		 * Clean up
		 */
		if ($archive)
		{
			JFile::delete( $archive );
			JFolder::delete( $package['folder'] );
		}
		else
		{
			JFile::delete( $script );
		}

		return $txt;
	}

	/**
	 * Unpacks a compressed script file either as zip or gz/ Assumes single file in archive
	 *
	 * @static
	 * @param string $p_filename The uploaded package filename or install directory
	 * @return unpacked filename on success, False on error
	 * @since 1.5
	 */
	function unpack($p_filename, &$vars) {

		/*
		 * Initialize variables
		 */
		// Path to the archive
		$archivename = $p_filename;
		// Temporary folder to extract the archive into
		$tmpdir = uniqid('install_');


		// Clean the paths to use for archive extraction
		$extractdir = JPath::clean(dirname($p_filename).DS.$tmpdir);
		$archivename = JPath::clean($archivename);

		$result = JArchive::extract( $archivename, $extractdir);

		if ( $result === false ) {
			return false;
		}


		/*
		 * return the file found in the extract folder and also folder name
		 */
		if ($handle = opendir( $extractdir ))
		{
   			while (false !== ($file = readdir($handle)))
   			{
				if ($file != "." && $file != "..")
				{
					 $script = $file;
					 continue;
				}
   			}
   			closedir($handle);
		}
		$retval['script'] = $script;
		$retval['folder'] = $extractdir;
		return $retval;

	}

	/**
	 * Performs pre-populate conversions on a migration script
	 *
	 * @static
	 * @param string $scriptName The uploaded / unpacked script file
	 * $param array $args The installation varibables
	 * @return converted filename on success, False on error
	 * @since 1.5
	 */
	function preMigrate( $scriptName, &$args, $db )
	{
		//TODO add error handling
		$buffer = '';
		$newPrefix = $args['DBPrefix'];
		/*
		 * read script file into buffer
		 */
		$buffer = file_get_contents( $scriptName );
		if(  $buffer == false )
		{
			return false;
		}

		/*
		 * search and replace table prefixes
		 */
		$oldPrefix = trim( $args['oldPrefix']);
		$oldPrefix = rtrim( $oldPrefix, '_' ) . '_';
		$buffer = str_replace( $oldPrefix, $newPrefix, $buffer );

		/*
		 * give temp name to menu and modules tables
		 */
		$buffer = str_replace ( $newPrefix.'modules', $newPrefix.'modules_migration', $buffer );
		$buffer = str_replace ( $newPrefix.'menu', $newPrefix.'menu_migration', $buffer );

		/*
		 * Create two empty temporary tables
		 */

		$query = 'DROP TABLE IF EXISTS '.$newPrefix.'modules_migration';
		$db->setQuery( $query );
		$db->query();

		$query = 'DROP TABLE IF EXISTS '.$newPrefix.'menu_migration';
		$db->setQuery( $query );
		$db->query();

		$query = 'CREATE TABLE '.$newPrefix.'modules_migration SELECT * FROM '.$newPrefix.'modules WHERE 0';
		$db->setQuery( $query );
		$db->query();

		$query = 'CREATE TABLE '.$newPrefix.'menu_migration SELECT * FROM '.$newPrefix.'menu WHERE 0';
		$db->setQuery( $query );
		$db->query();

		/*
		 * rename two aro_acl... field names
		 */
		$buffer = preg_replace ( '/group_id(?!.{15,25}aro_id)/', 'id', $buffer );
		$buffer = preg_replace ( '/aro_id(?=.{1,6}section_value)/', 'id', $buffer );

		/*
		 * convert to utf-8
		 */
		$srcEncoding = $args['srcEncoding'];
		$buffer = iconv( $srcEncoding, 'utf-8//TRANSLIT', $buffer );
		/*
		 * write to file
		 */
		$newFile = dirname( $scriptName ).DS.'converted.sql';
		$ret = file_put_contents( $newFile, $buffer );
		$buffer = '';
		JFile::delete( $scriptName );
		return $newFile;
	}

	/**
	 * Performs post-populate conversions after importing a migration script
	 * These include constructing an appropriate menu table for core content items
	 * and adding core modules from old site to the modules table
	 *
	 * @static
	 * @param JDatabase
	 * @param array errors (by ref)
	 * @return error count
	 * @since 1.5
	 */
	function postMigrate( $db, & $errors, & $args ) {

		$newPrefix = $args['DBPrefix'];

		/*
		 * Check to see if migration is from 4.5.1
		 */
		$query = 'SELECT id FROM '.$newPrefix.'users WHERE usertype = "superadministrator"';
		$db->setQuery($query);
		$rows = $db->loadRowList(  );
		JInstallationHelper::getDBErrors($errors, $db );

		/*
		 * if it is, then fill usertype field with correct values from aro_group
		 */
		if ( count($rows) > 0 )
		{
			$query = 'UPDATE '.$newPrefix.'users AS u, '.$newPrefix.'core_acl_aro_groups AS g' .
					' SET u.usertype = g.value' .
					' WHERE u.gid = g.id';
			$db->setQuery($query);
			$db->query();
			JInstallationHelper::getDBErrors($errors, $db );
		}
//		/*

		/*
		 * Construct the menu table based on old table references to core items
		 */
		// Component - change all
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET `type` = "component" WHERE `type` = "components";';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// Component Item Link
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET `link` = SUBSTRING(link, 1, LOCATE("&Itemid=", link) -1), `type` = "component" WHERE `type` = "component_item_link";';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// get com_contact id
		$query = 'SELECT `id` FROM `'.$newPrefix.'components` WHERE `option`="com_contact" AND `parent` = 0';
		$db->setQuery( $query );
		JInstallationHelper::getDBErrors($errors, $db );
		$compId = $db->loadResult();

		// contact category table
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET  `link` = INSERT(link, LOCATE("catid=", link), 0, "view=category&"), `type` = "component", `componentid` = '.$compId.' WHERE `type` = "contact_category_table"';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// contact item link
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET  `link` = INSERT(link, LOCATE("task=view", link), 20, "view=contact&id"), `type` = "component", `componentid` = '.$compId.' WHERE `type` = "contact_item_link"';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// get com_content id
		$query = 'SELECT `id` FROM `'.$newPrefix.'components` WHERE `option`="com_content" AND `parent` = 0';
		$db->setQuery( $query );
		JInstallationHelper::getDBErrors($errors, $db );
		$compId = $db->loadResult();

		// front page
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET `control` = "view_name=", `link` = "index.php?option=com_content&view=frontpage", `type` = "component", `componentid` = '.$compId.' WHERE `link` LIKE "%option=com_frontpage%"';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// content archive category or section
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET  `link` = "index.php?option=com_content&view=archive", `type` = "component", `componentid` = '.$compId.' WHERE (`type` = "content_archive_category" OR `type` = "content_archive_section")';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// content blog category
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET  `link` = INSERT(link, LOCATE("task=blogcat", link), 17, "view=category&layout=blog"), `type` = "component", `componentid` = '.$compId.' WHERE `type` = "content_blog_category"';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// content blog section
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET `link` = INSERT(link, LOCATE("task=blogsec", link), 16, "view=section&layout=blog"), `type` = "component", `componentid` = '.$compId.' WHERE `type` = "content_blog_section";';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// content category
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET `link` = INSERT(link, LOCATE("task=", link), LOCATE("&id=", link) - LOCATE("task=", link), "view=category"), `type` = "component", `componentid` = '.$compId.' WHERE `type` = "content_category"';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// content item link and typed content
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET  `link` = INSERT(link, LOCATE("task=", link), 9, "view=article"), `type` = "component", `componentid` = '.$compId.' WHERE (`type` = "content_item_link" OR `type` = "content_typed")';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// content section
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET  `link` = INSERT(link, LOCATE("task=", link), 12, "view=section"), `type` = "component", `componentid` = '.$compId.' WHERE `type` = "content_section"';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// get com_newsfeeds id
		$query = 'SELECT `id` FROM `'.$newPrefix.'components` WHERE `option`="com_newsfeeds" AND `parent` = 0';
		$db->setQuery( $query );
		JInstallationHelper::getDBErrors($errors, $db );
		$compId = $db->loadResult();

		// newsfeed categories
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET `link` = CONCAT(link, "&view=categories"), `componentid` = '.$compId.' WHERE `type` = "component" AND link LIKE "%option=com_newsfeeds%"';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// newsfeed category table
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET `link` = INSERT(link, LOCATE("catid=", link), 5, "view=category&catid"), `type` = "component", `componentid` = '.$compId.' WHERE `type` = "newsfeed_category_table"';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// newsfeed link
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET `link` = INSERT(link, LOCATE("task=", link), 9, "view=newsfeed"), `type` = "component", `componentid` = '.$compId.' WHERE `type` = "newsfeed_link"';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// user checkin items
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET `link` = INSERT(link, LOCATE("CheckIn", link), 7, "checkin") WHERE `type` = "url" AND link LIKE "%option=com_user&task=CheckIn%"';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// user edit details
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET `link` = INSERT(link, LOCATE("UserDetails", link), 11, "edit") WHERE `type` = "url" AND link LIKE "%option=com_user&task=UserDetails%"';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// get com_weblinks id
		$query = 'SELECT `id` FROM `'.$newPrefix.'components` WHERE `option`="com_weblinks" AND `parent` = 0';
		$db->setQuery( $query );
		JInstallationHelper::getDBErrors($errors, $db );
		$compId = $db->loadResult();

		// weblinks categories
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET `link` = CONCAT(link, "&view=categories"), `componentid` = '.$compId.' WHERE `type` = "component" AND link LIKE "%option=com_weblinks%"';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// weblinks category table
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET `link` = INSERT(link, LOCATE("catid=", link), 5, "view=category&catid"), `type` = "component", `componentid` = '.$compId.' WHERE `type` = "weblink_category_table"';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// weblinks submit new item
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET `link` = INSERT(link, LOCATE("task=", link), 8, "view=weblink&layout=form") WHERE `type` = "url" AND link LIKE "%option=com_weblinks%"';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// get com_wrapper id
		$query = 'SELECT `id` FROM `'.$newPrefix.'components` WHERE `option`="com_wrapper" AND `parent` = 0';
		$db->setQuery( $query );
		JInstallationHelper::getDBErrors($errors, $db );
		$compId = $db->loadResult();

		// wrapper
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET `link` = CONCAT(link, "&view=wrapper"), `type` = "component", `componentid` = '.$compId.' WHERE `type` = "wrapper"';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// set default to lowest ordering published on mainmenu
		$query = 'SELECT MIN( `ordering` ) FROM `'.$newPrefix.'menu_migration` WHERE `published` = 1 AND `parent` = 0 AND `menutype` = "mainmenu"';
		$db->setQuery( $query );
		JInstallationHelper::getDBErrors($errors, $db );
		$minorder = $db->loadResult();

		$query = 'SELECT `id` FROM `'.$newPrefix.'menu_migration` WHERE `published` = 1 AND `parent` = 0 AND `menutype` = "mainmenu" AND `ordering` = '.$minorder;
		$db->setQuery( $query );
		JInstallationHelper::getDBErrors($errors, $db );
		$menuitemid = $db->loadResult();

		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET `home` = 1 WHERE `id` = '.$menuitemid;
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		// tidy up urls with Itemids
		$query = 'UPDATE `'.$newPrefix.'menu_migration` SET `link` = SUBSTRING(`link`,1,LOCATE("&Itemid",`link`)-1) WHERE `type` = "url" AND `link` LIKE "%&Itemid=%"';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		$query = 'SELECT DISTINCT `option` FROM '.$newPrefix.'components WHERE `option` != ""';
		$db->setQuery( $query );
		JInstallationHelper::getDBErrors($errors, $db );
		$lookup = $db->loadResultArray();
		$lookup[] = 'com_user&';

		// prepare to copy across
		$query = 'SELECT * FROM '.$newPrefix.'menu_migration';
		$db->setQuery( $query );
		JInstallationHelper::getDBErrors($errors, $db );
		$oldMenuItems = $db->loadObjectList();

		$query = 'DELETE FROM '.$newPrefix.'menu WHERE 1';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		$query = 'SELECT * FROM '.$newPrefix.'menu';
		$db->setQuery( $query );
		JInstallationHelper::getDBErrors($errors, $db );
		$newMenuItems = $db->loadObjectList();

		// filter out links to 3pd components
		foreach( $oldMenuItems as $item )
		{
			if ( $item->type == 'url' && !strpos( $item->link, 'com_') )
			{
				$newMenuItems[] = $item;
			}
			else if ( $item->type == 'url' && JInstallationHelper::isValidItem( $item->link, $lookup ) )
			{
				$newMenuItems[] = $item;
			}
			else if ( $item->type == 'component' && JInstallationHelper::isValidItem( $item->link, $lookup ))
			{
				$newMenuItems[] = $item;
			}
		}

		// build the menu table
		foreach ( $newMenuItems as $item )
		{
			$db->insertObject( $newPrefix.'menu', $item );
			JInstallationHelper::getDBErrors($errors, $db );
		}

		// fix possible orphaned sub menu items
		$query = 'UPDATE  `'.$newPrefix.'menu` AS c LEFT OUTER JOIN `'.$newPrefix.'menu` AS p ON c.parent = p.id SET c.parent = 0 WHERE c.parent <> 0 AND p.id IS NULL';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		/*
		 * Construct the menu_type table base on new menu table types
		 */
		$query = 'SELECT DISTINCT `menutype` FROM '.$newPrefix.'menu WHERE 1';
		$db->setQuery( $query );
		JInstallationHelper::getDBErrors($errors, $db );
		$menuTypes = $db->loadResultArray();

		$query = 'TRUNCATE TABLE '.$newPrefix.'menu_types';
		$db->setQuery($query);
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		foreach( $menuTypes as $mType )
		{
			$query = 'INSERT INTO '.$newPrefix.'menu_types ( menutype, title ) VALUES ("'.$mType.'", "'.$mType.'");';
			$db->setQuery($query);
			$db->query();
			JInstallationHelper::getDBErrors($errors, $db );
		}

		/*
		 * Add core client modules from old site to modules table as unpublished
		 */
		$query = 'SELECT module FROM '.$newPrefix.'modules WHERE client_id = 0 AND module != "mod_mainmenu"';
		$db->setQuery( $query );
		JInstallationHelper::getDBErrors($errors, $db );
		$lookup = $db->loadResultArray();

		$query = 'SELECT MAX(id) FROM '.$newPrefix.'modules ';
		$db->setQuery( $query );
		JInstallationHelper::getDBErrors($errors, $db );
		$nextId = $db->loadResult();

		foreach( $lookup as $module )
		{
			$nextId++;
			$qry = 'SELECT * FROM '.$newPrefix.'modules_migration WHERE module = "'.$module.'" AND client_id = 0';
			$db->setQuery( $qry );
			JInstallationHelper::getDBErrors($errors, $db );
			if ( $row = $db->loadObject() )
			{
				$row->id = $nextId;
				$row->published = 0;
				$db->insertObject( $newPrefix.'modules', $row );
				JInstallationHelper::getDBErrors($errors, $db );
			}
		}
		/*
		 * Clean up
		 */

		$query = 'DROP TABLE IF EXISTS '.$newPrefix.'modules_migration';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );

		$query = 'DROP TABLE IF EXISTS '.$newPrefix.'menu_migration';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db );



		return count( $errors );
	}

	function isValidItem ( $link, $lookup )
	{
		foreach( $lookup as $component )
		{
			if ( strpos( $link, $component ) != false )
			{
				return true;
			}
		}
		return false;
	}

	function getDBErrors( & $errors, $db )
	{
		if ($db->getErrorNum() > 0)
		{
			$errors[] = array('msg' => $db->getErrorMsg(), 'sql' => $db->_sql);
		}
	}

	/**
	 * Inserts ftp variables to mainframe registry
	 * Needed to activate ftp layer for file operations in safe mode
	 *
	 * @param array The post values
	 */
	function setFTPCfg( $vars )
	{
		global $mainframe;
		$arr = array();
		$arr['ftp_enable'] = $vars['ftpEnable'];
		$arr['ftp_user'] = $vars['ftpUser'];
		$arr['ftp_pass'] = $vars['ftpPassword'];
		$arr['ftp_root'] = $vars['ftpRoot'];
		$arr['ftp_host'] = $vars['ftpHost'];
		$arr['ftp_port'] = $vars['ftpPort'];

		$mainframe->setCfg( $arr, 'config' );
	}

	function _chmod( $path, $mode )
	{
		global $mainframe;
   		$ret = false;

		// Initialize variables
		$ftpFlag	= true;
		$ftpRoot	= $mainframe->getCfg('ftp_root');

		// Do NOT use ftp if it is not enabled
		if ($mainframe->getCfg('ftp_enable') != 1) {
			$ftpFlag = false;
		}

		if ($ftpFlag == true)
		{
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = & JFTP::getInstance($mainframe->getCfg('ftp_host'), $mainframe->getCfg('ftp_port'));
			$ftp->login($mainframe->getCfg('ftp_user'), $mainframe->getCfg('ftp_pass'));

			//Translate the destination path for the FTP account
			$path = JPath::clean(str_replace(JPATH_SITE, $ftpRoot, $path), '/');

			// do the ftp chmod
			if (!$ftp->chmod($path, $mode))
			{
				// FTP connector throws an error
				return false;
			}
			$ftp->quit();
			$ret = true;
		}
		else
		{
			$ret = @ chmod($path, $mode);
		}

		return $ret;
	}

	/**
	 * returns the langauge code and help url set in the localise.xml file.
	 * Used for forcing a particular language in localised releases
	 */
	function getLocalise()
	{
		jimport('joomla.factory');
		$xml = & JFactory::getXMLParser('Simple');
		if (!$xml->loadFile(JPATH_SITE.DS.'installation'.DS.'localise.xml')) {
			return 'no file'; //null;
		}

		// Check that it's a localise file
		if ($xml->document->name() != 'localise') {
			return 'not a localise'; //null;
		}
		$tags =  $xml->document->children();
		$ret = array();
		$ret['lang'] = $tags[0]->data();
		$ret['helpurl'] = $tags[1]->data();
		return  $ret;

	}


}
?>
