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

if (!file_exists( JPATH_CONFIGURATION . DS . 'configuration.php' ) || (filesize( JPATH_CONFIGURATION . DS . 'configuration.php' ) < 10)) {
	// TODO: Throw 500 error
	header( 'Location: ../installation/index.php' );
	exit();
}

/*
 * Joomla! system startup
 */

// System includes
// globals.php disabled for now as it breaks xmlrpcs.php
// TODO: We need to make sure to enable it prior to release!
//require_once( JPATH_SITE			. DS . 'globals.php' );
require_once( JPATH_LIBRARIES		. DS . 'loader.php' );

// File includes
define( 'JPATH_INCLUDES', dirname(__FILE__) );

/*
 * Joomla! framework loading
 */

// Include object abstract class
jimport( 'joomla.common.compat.compat' );
jimport( 'joomla.common.abstract.object' );

// Joomla! library imports
jimport( 'joomla.application.application' );
jimport( 'joomla.application.event' );
jimport( 'joomla.application.extension.plugin' );
jimport( 'joomla.application.user.user' );
jimport( 'joomla.database.table' );
jimport( 'joomla.environment.request' );
jimport( 'joomla.environment.session' );
jimport( 'joomla.factory' );
jimport( 'joomla.filesystem.*' );
jimport( 'joomla.i18n.language' );
jimport( 'joomla.i18n.string' );
jimport( 'joomla.utilities.error' );
?>