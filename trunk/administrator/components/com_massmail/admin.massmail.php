<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Massmail
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
 * Make sure the user is authorized to view this page
 */
$user = & JFactory::getUser();
if (!$user->authorize( 'com_massmail', 'manage' )) {
	josRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

require_once( JApplicationHelper::getPath( 'admin_html' ) );

switch ($task) {
	case 'send':
		sendMail();
		break;

	case 'cancel':
		josRedirect( 'index2.php' );
		break;

	default:
		messageForm( $option );
		break;
}

function messageForm( $option )
{
	$acl = JFactory::getACL();

	$gtree = array(
		mosHTML::makeOption( 0, '- '. JText::_( 'All User Groups' ) .' -' )
	);

	// get list of groups
	$lists = array();
	$gtree = array_merge( $gtree, $acl->get_group_children_tree( null, 'USERS', false ) );
	$lists['gid'] = mosHTML::selectList( $gtree, 'mm_group', 'size="10"', 'value', 'text', 0 );

	HTML_massmail::messageForm( $lists, $option );
}

function sendMail()
{
	global $mainframe;

	$db					=& JFactory::getDBO();
	$user 				=& JFactory::getUser();
	$acl 				= JFactory::getACL();

	$mode				= JRequest::getVar( 'mm_mode', 0, 'post' );
	$subject			= JRequest::getVar( 'mm_subject', '', 'post' );
	$gou				= JRequest::getVar( 'mm_group', '', 'post' );
	$recurse			= JRequest::getVar( 'mm_recurse', 'NO_RECURSE', 'post' );
	// pulls message inoformation either in text or html format
	if ( $mode ) {
		$message_body	= $_POST['mm_message'];
	} else {
		// automatically removes html formatting
		$message_body	= JRequest::getVar( 'mm_message', '', 'post' );
	}
	$message_body 		= stripslashes( $message_body );

	if (!$message_body || !$subject || $gou === null) {
		josRedirect( 'index2.php?option=com_massmail&josmsg='. JText::_( 'Please fill in the form correctly' ) );
	}

	// get users in the group out of the acl
	$to = $acl->get_group_objects( $gou, 'ARO', $recurse );

	$rows = array();
	if ( count( $to['users'] ) || $gou === '0' ) {
		// Get sending email address
		$query = "SELECT email"
		. "\n FROM #__users"
		. "\n WHERE id = " .$user->get('id')
		;
		$db->setQuery( $query );
		$user->set( 'email', $db->loadResult() );

		// Get all users email and group except for senders
		$query = "SELECT email"
		. "\n FROM #__users"
		. "\n WHERE id != " .$user->get('id')
		. ( $gou !== '0' ? " AND id IN (" . implode( ',', $to['users'] ) . ")" : '' )
		;
		$db->setQuery( $query );
		$rows = $db->loadObjectList();

		// Build e-mail message format
		$message_header 	= sprintf( _MASSMAIL_MESSAGE, $mainframe->getCfg('sitename') );
		$message 			= $message_header . $message_body;
		$subject 			= $mainframe->getCfg('sitename'). ' / '. stripslashes( $subject);

		//Send email
		foreach ($rows as $row) {
			mosMail( $mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname'), $row->email, $subject, $message, $mode );
		}
	}

	$msg = sprintf( JText::_( 'E-mail sent to' ), count( $rows ) );
	josRedirect( 'index2.php?option=com_massmail', $msg );
}
?>
