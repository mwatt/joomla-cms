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
defined( '_VALID_MOS' ) or die( 'Restricted access' );
define( '_MOS_MAMBO_INCLUDED', 1 );

if (phpversion() < '4.2.0') {
	require_once( $mosConfig_absolute_path . '/includes/compat.php41x.php' );
}
if (phpversion() < '4.3.0') {
	require_once( $mosConfig_absolute_path . '/includes/compat.php42x.php' );
}
if (in_array( 'globals', array_keys( array_change_key_case( $_REQUEST, CASE_LOWER ) ) ) ) {
	die( 'Fatal error.  Global variable hack attempted.' );
}
if (in_array( '_post', array_keys( array_change_key_case( $_REQUEST, CASE_LOWER ) ) ) ) {
	die( 'Fatal error.  Post variable hack attempted.' );
}
if (version_compare( phpversion(), '5.0' ) < 0) {
	require_once( $mosConfig_absolute_path . '/includes/compat.php50x.php' );
}

@set_magic_quotes_runtime( 0 );

if (@$mosConfig_error_reporting === 0) {
	error_reporting( 0 );
} else if (@$mosConfig_error_reporting > 0) {
	error_reporting( $mosConfig_error_reporting );
}

/**
 * Intelligent file importer
 * @param string A dot syntax path
 * @param boolean True to use require_once, false to use require
 */
function jimport( $path, $requireOnce=true ) {
	global $mosConfig_absolute_path;

	$path = $mosConfig_absolute_path
		. DIRECTORY_SEPARATOR . str_replace( '.', DIRECTORY_SEPARATOR, $path );
	
	// TODO: Rules, try a few variations, search for factories, etc

	$path = $path . '.php';

	if ($requireOnce) {
		return require_once( $path );
	} else {
		return require( $path );
	}
}

// experimenting

jimport( 'includes.database' );
require_once( $mosConfig_absolute_path . '/includes/gacl.class.php' );
require_once( $mosConfig_absolute_path . '/includes/gacl_api.class.php' );
require_once( $mosConfig_absolute_path . '/includes/phpmailer/class.phpmailer.php' );
require_once( $mosConfig_absolute_path . '/includes/phpInputFilter/class.inputfilter.php' );

jimport( 'libraries.joomla.version' );
jimport( 'libraries.joomla.functions' );
jimport( 'libraries.joomla.classes' );
jimport( 'libraries.joomla.models' );
jimport( 'libraries.joomla.html' );
jimport( 'libraries.joomla.factory' );
jimport( 'libraries.joomla.files' );
jimport( 'libraries.joomla.xml' );

/*
require_once( $mosConfig_absolute_path . '/includes/version.php' );
require_once( $mosConfig_absolute_path . '/includes/database.php' );
require_once( $mosConfig_absolute_path . '/includes/gacl.class.php' );
require_once( $mosConfig_absolute_path . '/includes/gacl_api.class.php' );
require_once( $mosConfig_absolute_path . '/includes/phpmailer/class.phpmailer.php' );
require_once( $mosConfig_absolute_path . '/includes/phpInputFilter/class.inputfilter.php' );

require_once( $mosConfig_absolute_path . '/libraries/joomla/functions.php' );
require_once( $mosConfig_absolute_path . '/libraries/joomla/classes.php' );
require_once( $mosConfig_absolute_path . '/libraries/joomla/models.php' );
require_once( $mosConfig_absolute_path . '/libraries/joomla/html.php' );
require_once( $mosConfig_absolute_path . '/libraries/joomla/factory.php' );
require_once( $mosConfig_absolute_path . '/libraries/joomla/files.php' );
require_once( $mosConfig_absolute_path . '/libraries/joomla/xml.php' );
require_once( $mosConfig_absolute_path . '/libraries/joomla/legacy.php' );
*/


/** @global $database */
$database = new database( $mosConfig_host, $mosConfig_user, $mosConfig_password, $mosConfig_db, $mosConfig_dbprefix );
if ($database->getErrorNum()) {
	$mosSystemError = $database->getErrorNum();
	$basePath = dirname( __FILE__ );
	include $basePath . '/../configuration.php';
	include $basePath . '/../offline.php';
	exit();
}
$database->debug( $mosConfig_debug );

/** @global $acl */
$acl = new gacl_api();

/** @global $_MAMBOTS */
$_MAMBOTS = new mosMambotHandler();

/** @global $_VERSION */
$_VERSION = new JVersion();

//TODO : implement mambothandler class as singleton, add getBotHandler to JFactory

//TODO : implement editor functionality as a class
jimport( 'libraries.joomla.editor' );


//TODO : implement mambothandler class as singleton, add getVersion to JFactory
jimport( 'libraries.joomla.legacy' );
?>
