<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) );

require_once( JPATH_BASE.'/includes/defines.php' );
require_once( JPATH_BASE.'/includes/framework.php' );
require_once( JPATH_BASE.'/includes/application.php' );
require_once( JPATH_BASE.'/includes/menubar.html.php' );

/**
 * CREATE THE APPLICATION
 *
 * NOTE :
 */
$mainframe = new JAdministrator();

// load the configuration
$mainframe->loadConfiguration(JPATH_CONFIGURATION.DS.'configuration.php');

// create the session
$mainframe->loadSession(JUtility::getHash($mainframe->getClientId()));

/**
 * INITIALISE THE APPLICATION
 *
 * NOTE :
 */
JPluginHelper::importPlugin('system');

$mainframe->initialise(array(
	'language' => $mainframe->getUserState( "application.lang", 'lang' )
));

// trigger the onAfterInitialise events
JDEBUG ? $_PROFILER->mark('afterInitialise') : null;
$mainframe->triggerEvent('onAfterInitialise');

// set for overlib check
$mainframe->set('loadOverlib', false);

/**
 * ROUTE THE APPLICATION
 *
 * NOTE :
 */
$mainframe->route();

// trigger the onAfterDisplay events
JDEBUG ? $_PROFILER->mark('afterRoute') : null;
$mainframe->triggerEvent('onAfterRoute');

/**
 * DISPATCH THE APPLICATION
 *
 * NOTE :
 */
$option = JAdministratorHelper::findOption();
$mainframe->dispatch();

// trigger the onAfterDisplay events
JDEBUG ? $_PROFILER->mark('afterDispatch') : null;
$mainframe->triggerEvent('onAfterDispatch');

/**
 * RENDER THE APPLICATION
 *
 * NOTE :
 */
$mainframe->render();

// trigger the onAfterDisplay events
JDEBUG ? $_PROFILER->mark( 'afterRender' ) : null;
$mainframe->triggerEvent( 'onAfterRender' );

/**
 * CLOSE THE SESSION
 */
JSession::close();

/**
 * RETURN THE RESPONSE
 */
echo JResponse::toString($mainframe->getCfg('gzip'));
?>