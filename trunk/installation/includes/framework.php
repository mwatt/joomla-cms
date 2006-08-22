<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installation
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

/*
 * Joomla! system checks
 */

error_reporting( E_ALL );
@set_magic_quotes_runtime( 0 );

if (file_exists( JPATH_CONFIGURATION . DS . 'configuration.php' ) && (filesize( JPATH_CONFIGURATION . DS . 'configuration.php' ) > 10)) {
	header( 'Location: ../index.php' );
	exit();
}

/*
 * Joomla! system startup
 */

// System includes
require_once( JPATH_SITE			. DS . 'globals.php' );
require_once( JPATH_LIBRARIES		. DS . 'loader.php' );

// Installation file includes
define( 'JPATH_INCLUDES', dirname(__FILE__) );

require_once( JPATH_INCLUDES . DS . 'functions.php' );
require_once( JPATH_INCLUDES . DS . 'classes.php' );
require_once( JPATH_INCLUDES . DS . 'html.php' );

/*
 * Joomla! framework loading
 */

// Include object abstract class
jimport( 'joomla.common.compat.compat' );
jimport( 'joomla.common.abstract.object' );

// Joomla! library imports
jimport( 'joomla.application.application' );
jimport( 'joomla.application.user.user' );
jimport( 'joomla.database.table' );
jimport( 'joomla.environment.request' );
jimport( 'joomla.environment.session' );
jimport( 'joomla.environment.uri' );
jimport( 'joomla.factory' );
jimport( 'joomla.filesystem.*' );
jimport( 'joomla.i18n.language' );
jimport( 'joomla.presentation.parameter.parameter' );
jimport( 'joomla.utilities.array' );
jimport( 'joomla.utilities.error' );
jimport( 'joomla.version' );

// JString should only be loaded after pre-install checks
$task = JRequest::getVar( 'task' );
if (!($task == '' || $task == 'preinstall' || $task == 'lang')) {
	jimport( 'joomla.i18n.string' );
}
?>