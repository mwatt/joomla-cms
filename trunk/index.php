<?php
/**
* @version $Id: index.php 1244 2005-11-29 02:39:31Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) );

require_once ( JPATH_BASE .'/includes/defines.php' );
require_once ( JPATH_BASE .'/includes/application.php' );

/**
 * CREATE THE APPLICATION
 */
$mainframe = new JSite();

// looad the configuration settings
$mainframe->setConfiguration(JPATH_CONFIGURATION . DS . 'configuration.php');

// load the legacy libraries if enabled
$mainframe->setLegacy();

// create the session
$mainframe->setSession( JURI::resolve('/', -1).$mainframe->getClientId() );

// set the language
$mainframe->setLanguage();

// load system plugin group
JPluginHelper::importPlugin( 'system' );

// trigger the onStart events
$mainframe->triggerEvent( 'onBeforeStart' );

// trigger the onAfterStart events
$mainframe->triggerEvent( 'onAfterStart' );

JDEBUG ? $_PROFILER->mark( 'afterStartFramework' ) : null;

// authorization
$Itemid = JSiteHelper::findItemid();
$mainframe->authorize($Itemid);

/**
 * Set the version variable as a global
 */
$GLOBALS['_VERSION'] = new JVersion();

// set for overlib check
$mainframe->set( 'loadOverlib', false );

// trigger the onBeforeDisplay events
$mainframe->triggerEvent( 'onBeforeDisplay' );

/** 
 * EXECUTE THE APPLICATION
 * 
 * Note: This section of initialization must be performed last.
 */
$option = JSiteHelper::findOption();
$mainframe->execute($option, isset($tmpl) ? $tmpl : 'index.php');

// trigger the onAfterDisplay events
$mainframe->triggerEvent( 'onAfterDisplay' );

JDEBUG ? $_PROFILER->mark( 'afterDisplayOutput' ) : null;

JDEBUG ? $_PROFILER->report( true, $mainframe->getCfg( 'debug_db' ) ) : null;
?>