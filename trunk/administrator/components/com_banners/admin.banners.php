<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Banners
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Make sure the user is authorized to view this page
$user = & JFactory::getUser();
if (!$user->authorize( 'com_banners', 'manage' )) {
	$mainframe->redirect( 'index.php', JText::_('ALERTNOTAUTH') );
}

// Set the table directory
JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_banners'.DS.'tables');

$controllerName = JRequest::getVar( 'c', 'banner' );

switch ($controllerName)
{
	default:
		$controllerName = 'banner';
		// allow fall through

	case 'banner' :
	case 'client':
		// Temporary interceptor
		$task = JRequest::getVar('task');
		if ($task == 'listclients') {
			$controllerName = 'client';
		}

		require_once( JPATH_COMPONENT.DS.'controllers'.DS.$controllerName.'.php' );
		$controllerName = 'BannerController'.$controllerName;

		// Create the controller
		$controller = new $controllerName();

		// Perform the Request task
		$controller->execute( JRequest::getVar('task') );

		// Redirect if set by the controller
		$controller->redirect();
		break;
}