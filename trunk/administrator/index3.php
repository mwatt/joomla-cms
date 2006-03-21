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

// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) );

require_once ( JPATH_BASE .'/includes/defines.php'     );
require_once ( JPATH_BASE .'/includes/application.php' );
require_once ( JPATH_BASE .'/includes/template.php'    );

// initialise some common request directives
$task		= JRequest::getVar( 'task' );
$section	= JRequest::getVar( 'section' );
$no_html	= strtolower( JRequest::getVar( 'no_html' ) );
$josmsg		= strip_tags( JRequest::getVar( 'josmsg' ) );

// create the mainframe object
$mainframe = new JAdministrator();

// set the configuration
$mainframe->setConfiguration(JPATH_CONFIGURATION . DS . 'configuration.php');

//get the database object
$database =& $mainframe->getDBO();

// load system plugin group
JPluginHelper::importPlugin( 'system' );

// trigger the onStart events
$mainframe->triggerEvent( 'onBeforeStart' );

// Get the global option variable and create the pathway
$option = strtolower( JRequest::getVar( 'option', 'com_admin' ) );
$mainframe->_createPathWay( );	

//get the acl object (for backwards compatibility)
$acl =& JFactory::getACL();

// create the session
$mainframe->setSession( $mainframe->getCfg('live_site').$mainframe->getClientId() );

if (is_null(JSession::get('guest')) || JSession::get('guest')) {
	josRedirect( 'index.php');
}

// set language 
$mainframe->setLanguage($mainframe->getUserState('application.lang'));

// trigger the onStart events
$mainframe->triggerEvent( 'onAfterStart' );

if ($option == 'logout') {
	$mainframe->logout();
}

// get the information about the current user from the sessions table
$user   = & $mainframe->getUser();
$my		= $user->_table;

$lang = $mainframe->getLanguage();

$params = $database->loadResult();
$my->params = new JParameter( $params );

//render raw component output
if($no_html == 1) {
	$path = JApplicationHelper::getPath( 'admin', $option );
	
	//load common language files
	$lang =& $mainframe->getLanguage();
	$lang->load($option);
	require_once( $path );	
	exit();	
}

// loads template file
$cur_template = $mainframe->getTemplate();
$file     = 'component.html';

$document =& $mainframe->getDocument();
$document->parse($cur_template, $file);

header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );

initDocument($document, $file);
$document->display( $file, $mainframe->getCfg('gzip') );
?>