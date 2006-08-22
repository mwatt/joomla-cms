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

/*
 * Joomla! system checks
 */

@set_magic_quotes_runtime( 0 );

if (!file_exists( JPATH_CONFIGURATION . DS . 'configuration.php' ) || (filesize( JPATH_CONFIGURATION . DS . 'configuration.php' ) < 10)) {
	header( 'Location: installation/index.php' );
	exit();
}

/*
 * Joomla! system startup
 */

// System includes
require_once( JPATH_SITE			. DS . 'globals.php' );
require_once( JPATH_CONFIGURATION	. DS . 'configuration.php' );
require_once( JPATH_LIBRARIES		. DS . 'loader.php' );

// System configuration
$CONFIG = new JConfig();

if (@$CONFIG->error_reporting === 0) {
	error_reporting( 0 );
} else if (@$CONFIG->error_reporting > 0) {
	error_reporting( $CONFIG->error_reporting );
}

define( 'JDEBUG', $CONFIG->debug );

unset( $CONFIG );

/*
 * Joomla! framework loading
 */

// Include object abstract class
jimport( 'joomla.common.compat.compat' );
jimport( 'joomla.common.abstract.object' );

// System profiler
if (JDEBUG) {
	jimport( 'joomla.utilities.profiler' );
	$_PROFILER =& JProfiler::getInstance( 'Application' );
}

// Joomla! library imports
jimport( 'joomla.application.application' );
jimport( 'joomla.application.event' );
jimport( 'joomla.application.extension.plugin' );
jimport( 'joomla.application.menu' );
jimport( 'joomla.application.user.user' );
jimport( 'joomla.database.table' );
jimport( 'joomla.environment.request' );
jimport( 'joomla.environment.session' );
jimport( 'joomla.environment.uri' );
jimport( 'joomla.factory' );
jimport( 'joomla.i18n.language' );
jimport( 'joomla.i18n.string' );
jimport( 'joomla.presentation.html' );
jimport( 'joomla.utilities.array' );
jimport( 'joomla.utilities.error' );
jimport( 'joomla.utilities.functions' );
jimport( 'joomla.utilities.utility' );
jimport( 'joomla.version' );

JDEBUG ? $_PROFILER->mark( 'afterLoadFramework' ) : null;
?>