<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Config
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Make sure the user is authorized to view this page
 */
$user = & JFactory::getUser();
if (!$user->authorize( 'com_config', 'manage' )) {
	$mainframe->redirect('index2.php?', JText::_('ALERTNOTAUTH'));
}


$controllerName = JRequest::getVar( 'c', 'global' );

switch ($controllerName)
{
	case 'global':
		require_once( JPATH_COMPONENT.DS.'controllers'.DS.'global.php' );
		require_once( JPATH_COMPONENT.DS.'views'.DS.'global.php' );

		$controller = new JConfigGlobalController( 'showConfig' );
		break;

	default:
		// TODO: Lock down access to config changes
		require_once( JPATH_COMPONENT.DS.'controllers'.DS.'component.php' );
		require_once( JPATH_COMPONENT.DS.'models'.DS.'component.php' );
		require_once( JPATH_COMPONENT.DS.'views'.DS.'component.php' );

		$controller = new JConfigComponentController( 'edit' );
		break;
}

$controller->execute( JRequest::getVar( 'task' ) );
$controller->redirect();
?>