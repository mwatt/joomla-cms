<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Users
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
$user = & $mainframe->getUser();
if (!$user->authorize( 'com_users', 'manage' )) {
	josRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

require_once( JApplicationHelper::getPath( 'admin_html' ) );
require_once( JApplicationHelper::getPath( 'class' ) );


switch ($task) {
	case 'new':
	case 'edit':
		editUser( );
		break;

	case 'save':
	case 'apply':
 		saveUser( );
		break;

	case 'remove':
		removeUsers( );
		break;

	case 'block':
		blockUser( );
		break;

	case 'unblock':
		unBlockUser( );
		break;

	case 'logout':
		logoutUser( );
		break;

	case 'flogout':
		logoutUser( );
		break;

	case 'cancel':
		cancelUser( );
		break;

	case 'contact':
		$contact_id = JRequest::getVar( 'contact_id', '', 'post', 'int' );
		josRedirect( 'index2.php?option=com_contact&task=editA&id='. $contact_id );
		break;

	default:
		showUsers( );
		break;
}

/**
 * Display users in list form
 */
function showUsers( )
{
	global $mainframe;

	$db = $mainframe->getDBO();
	$currentUser = $mainframe->getUser();
	$acl =& JFactory::getACL();

	$option 	= JRequest::getVar( 'option');

	$filter_order		= $mainframe->getUserStateFromRequest( "$option.filter_order", 		'filter_order', 	'a.name' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.filter_order_Dir",	'filter_order_Dir',	'' );
	$filter_type		= $mainframe->getUserStateFromRequest( "$option.filter_type", 		'filter_type', 		0 );
	$filter_logged		= $mainframe->getUserStateFromRequest( "$option.filter_logged", 	'filter_logged', 	0 );
	$limit 				= $mainframe->getUserStateFromRequest( "limit", 					'limit', 			$mainframe->getCfg('list_limit') );
	$limitstart 		= $mainframe->getUserStateFromRequest( "$option.limitstart", 		'limitstart', 		0 );
	$search 			= $mainframe->getUserStateFromRequest( "$option.search", 			'search', 			'' );
	$search 			= $db->getEscaped( trim( JString::strtolower( $search ) ) );
	$where 				= array();

	if (isset( $search ) && $search!= '') {
		$where[] = "(a.username LIKE '%$search%' OR a.email LIKE '%$search%' OR a.name LIKE '%$search%')";
	}
	if ( $filter_type ) {
		if ( $filter_type == 'Public Frontend' ) {
			$where[] = "a.usertype = 'Registered' OR a.usertype = 'Author' OR a.usertype = 'Editor' OR a.usertype = 'Publisher'";
		} else if ( $filter_type == 'Public Backend' ) {
			$where[] = "a.usertype = 'Manager' OR a.usertype = 'Administrator' OR a.usertype = 'Super Administrator'";
		} else {
			$where[] = "a.usertype = LOWER( '$filter_type' )";
		}
	}
	if ( $filter_logged == 1 ) {
		$where[] = "s.userid = a.id";
	} else if ($filter_logged == 2) {
		$where[] = "s.userid IS NULL";
	}

	// exclude any child group id's for this user
	$pgids = $acl->get_group_children( $currentUser->get('gid'), 'ARO', 'RECURSE' );

	if (is_array( $pgids ) && count( $pgids ) > 0) {
		$where[] = "(a.gid NOT IN (" . implode( ',', $pgids ) . "))";
	}
	$filter = '';
	if ($filter_logged == 1 || $filter_logged == 2) {
		$filter = "\n INNER JOIN #__session AS s ON s.userid = a.id";
	}

	$orderby = "\n ORDER BY $filter_order $filter_order_Dir";
	$where = ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );

	$query = "SELECT COUNT(a.id)"
	. "\n FROM #__users AS a"
	. $filter
	. $where
	;
	$db->setQuery( $query );
	$total = $db->loadResult();

	jimport('joomla.presentation.pagination');
	$pageNav = new JPagination( $total, $limitstart, $limit );

	$query = "SELECT a.*, g.name AS groupname"
	. "\n FROM #__users AS a"
	. "\n INNER JOIN #__core_acl_aro AS aro ON aro.value = a.id"
	. "\n INNER JOIN #__core_acl_groups_aro_map AS gm ON gm.aro_id = aro.id"
	. "\n INNER JOIN #__core_acl_aro_groups AS g ON g.id = gm.group_id"
	. $filter
	. $where
	. "\n GROUP BY a.id"
	. $orderby
	;
	$db->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $db->loadObjectList();

	$n = count( $rows );
	$template = "SELECT COUNT(s.userid)"
	. "\n FROM #__session AS s"
	. "\n WHERE s.userid = %d"
	;
	for ($i = 0; $i < $n; $i++) {
		$row = &$rows[$i];
		$query = sprintf( $template, intval( $row->id ) );
		$db->setQuery( $query );
		$row->loggedin = $db->loadResult();
	}

	// get list of Groups for dropdown filter
	$query = "SELECT name AS value, name AS text"
	. "\n FROM #__core_acl_aro_groups"
	. "\n WHERE name != 'ROOT'"
	. "\n AND name != 'USERS'"
	;
	$db->setQuery( $query );
	$types[] 		= mosHTML::makeOption( '0', '- '. JText::_( 'Select Group' ) .' -' );
	$types 			= array_merge( $types, $db->loadObjectList() );
	$lists['type'] 	= mosHTML::selectList( $types, 'filter_type', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', "$filter_type" );

	// get list of Log Status for dropdown filter
	$logged[] = mosHTML::makeOption( 0, '- '. JText::_( 'Select Log Status' ) .' -');
	$logged[] = mosHTML::makeOption( 1, JText::_( 'Logged In' ) );
	$lists['logged'] = mosHTML::selectList( $logged, 'filter_logged', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', "$filter_logged" );

	// table ordering
	if ( $filter_order_Dir == 'DESC' ) {
		$lists['order_Dir'] = 'ASC';
	} else {
		$lists['order_Dir'] = 'DESC';
	}
	$lists['order'] = $filter_order;

	// search filter
	$lists['search']= $search;

	HTML_users::showUsers( $rows, $pageNav, $option, $lists );
}

/**
 * Edit the user
 */
function editUser( )
{
	global $mainframe;

	$cid 		= JRequest::getVar( 'cid', array(0) );
	$option 	= JRequest::getVar( 'option');
	if (!is_array( $cid )) {
		$cid = array(0);
	}

	$db 		=& $mainframe->getDBO();
	$user 	  	=& JUser::getInstance(intval($cid[0]));
	$acl      	=& JFactory::getACL();

	if ( $user->get('id') ) {
		$query = "SELECT *"
		. "\n FROM #__contact_details"
		. "\n WHERE user_id =". $user->get('id')
		;
		$db->setQuery( $query );
		$contact = $db->loadObjectList();
	} else {
		$contact 	= NULL;
		$row->block = 0;
	}

	$userObjectID 	= $acl->get_object_id( 'users', $user->get('id'), 'ARO' );
	$userGroups 	= $acl->get_object_groups( $userObjectID, 'ARO' );
	$userGroupName 	= strtolower( $acl->get_group_name( $userGroups[0], 'ARO' ) );

	$myObjectID 	= $acl->get_object_id( 'users', $user->get('id'), 'ARO' );
	$myGroups 		= $acl->get_object_groups( $myObjectID, 'ARO' );
	$myGroupName 	= strtolower( $acl->get_group_name( $myGroups[0], 'ARO' ) );;

	// ensure user can't add/edit group higher than themselves
	/* NOTE : This check doesn't work commented out for the time being
	if ( is_array( $myGroups ) && count( $myGroups ) > 0 ) {
		$excludeGroups = (array) $acl->get_group_children( $myGroups[0], 'ARO', 'RECURSE' );
	} else {
		$excludeGroups = array();
	}

	if ( in_array( $userGroups[0], $excludeGroups ) ) {
		echo 'not auth';
		josRedirect( 'index2.php?option=com_users', JText::_('NOT_AUTH') );
	}
	*/

	//if ( $userGroupName == 'super administrator' ) {
		// super administrators can't change
	// 	$lists['gid'] = '<input type="hidden" name="gid" value="'. $currentUser->gid .'" /><strong>'. JText::_( 'Super Administrator' ) .'</strong>';
	//} else if ( $userGroupName == $myGroupName && $myGroupName == 'administrator' ) {
	if ( $userGroupName == $myGroupName && $myGroupName == 'administrator' ) {
		// administrators can't change each other
		$lists['gid'] = '<input type="hidden" name="gid" value="'. $user->get('gid') .'" /><strong>'. JText::_( 'Administrator' ) .'</strong>';
	} else {
		$gtree = $acl->get_group_children_tree( null, 'USERS', false );

		// remove users 'above' me
		//$i = 0;
		//while ($i < count( $gtree )) {
		//	if ( in_array( $gtree[$i]->value, (array)$excludeGroups ) ) {
		//		array_splice( $gtree, $i, 1 );
		//	} else {
		//		$i++;
		//	}
		//}

		$lists['gid'] 	= mosHTML::selectList( $gtree, 'gid', 'size="10"', 'value', 'text', $user->get('gid') );
	}

	// build the html select list
	$lists['block'] 	= mosHTML::yesnoRadioList( 'block', 'class="inputbox" size="1"', $user->get('block') );
	// build the html select list
	$lists['sendEmail'] = mosHTML::yesnoRadioList( 'sendEmail', 'class="inputbox" size="1"', $user->get('sendEmail') );

	HTML_users::edituser( $user, $contact, $lists, $option );
}

/**
 * Save current edit or addition
 */
function saveUser(  )
{
	global $mainframe;

	$task 	= JRequest::getVar( 'task' );
	$option = JRequest::getVar( 'option');

	/*
	 * Initialize some variables
	 */
	$db			= & $mainframe->getDBO();
	$me			= & $mainframe->getUser();
	$MailFrom	= $mainframe->getCfg('mailfrom');
	$FromName	= $mainframe->getCfg('fromname');
	$SiteName	= $mainframe->getCfg('sitename');

	/*
	 * Lets create a new JUser object
	 */
	$user = & new JUser(JRequest::getVar( 'id', 0, 'post', 'int'));
	$original_gid = $user->get('gid');

	if (!$user->bind( $_POST )) {
		josRedirect( 'index2.php?option=com_users', $user->getError() );
		return false;
	}

	// Are we dealing with a new user which we need to create?
	$isNew 	= !$user->get('id');
	if (!$isNew)
	{
		// if group has been changed and where original group was a Super Admin
		if ( $user->get('gid') != $original_gid && $original_gid == 25 ) {
			// count number of active super admins
			$query = "SELECT COUNT( id )"
			. "\n FROM #__users"
			. "\n WHERE gid = 25"
			. "\n AND block = 0"
			;
			$db->setQuery( $query );
			$count = $db->loadResult();

			if ( $count <= 1 ) {
			// disallow change if only one Super Admin exists
				$user->_error = JText::_( 'WARN_ONLY_SUPER' );
				return false;
			}
		}
	}

	/*
	 * Lets save the JUser object
	 */
	if (!$user->save()) {
		josRedirect( 'index2.php?option=com_users', $user->getError() );
		return false;
	}


	/*
	 * Time for the email magic so get ready to sprinkle the magic dust...
	 */
	if ($isNew) {
		$adminEmail = $me->get('email');
		$adminName	= $me->get('name');

		$subject = JText::_('NEW_USER_MESSAGE_SUBJECT');
		$message = sprintf ( JText::_('NEW_USER_MESSAGE'), $user->get('name'), $SiteName, $mainframe->getSiteURL(), $user->get('username'), $user->clearPW );

		if ($MailFrom != "" && $FromName != "") {
			$adminName 	= $FromName;
			$adminEmail = $MailFrom;
		}
		JUtility::sendMail( $adminEmail, $adminName, $user->get('email'), $subject, $message );
	}

	switch ( $task ) {
		case 'apply':
        	$msg = sprintf( JText::_( 'Successfully Saved changes to User' ), $user->get('name') );
			josRedirect( 'index2.php?option=com_users&task=edit&hidemainmenu=1&cid[]='. $user->get('id'), $msg );
			break;

		case 'save':
		default:
        	$msg = sprintf( JText::_( 'Successfully Saved User' ), $user->get('name') );
			josRedirect( 'index2.php?option=com_users', $msg );
			break;
	}
}

/**
* Cancels an edit operation
*/
function cancelUser( )
{
	$option 	= JRequest::getVar( 'option');
	josRedirect( 'index2.php?option='. $option .'&task=view' );
}

/**
* Delete selected users
*/
function removeUsers(  )
{
	global $mainframe;

	$db 			= $mainframe->getDBO();
	$currentUser 	= $mainframe->getUser();

	$acl      		=& JFactory::getACL();

	$cid 			= JRequest::getVar( 'cid', array( 0 ), '', 'array' );
	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script> alert('". JText::_( 'Select an item to delete', true ) ."'); window.history.go(-1);</script>\n";
		exit;
	}

	if (count( $cid )) {
		foreach ($cid as $id) {
			// check for a super admin ... can't delete them
			$objectID 	= $acl->get_object_id( 'users', $id, 'ARO' );
			$groups 	= $acl->get_object_groups( $objectID, 'ARO' );
			$this_group = strtolower( $acl->get_group_name( $groups[0], 'ARO' ) );

			$success = false;
			if ( $this_group == 'super administrator' ) {
				$msg = JText::_( 'You cannot delete a Super Administrator' );
 			} else if ( $id == $currentUser->get( 'id' ) ) {
 				$msg = JText::_( 'You cannot delete Yourself!' );
 			} else if ( ( $this_group == 'administrator' ) && ( $currentUser->get( 'gid' ) == 24 ) ) {
 				$msg = JText::_( 'WARNDELETE' );
			} else {
				$user =& JUser::getInstance((int)$id);
				$count = 2;

				if ( $user->get( 'gid' ) == 25 ) {
					// count number of active super admins
					$query = "SELECT COUNT( id )"
					. "\n FROM #__users"
					. "\n WHERE gid = 25"
					. "\n AND block = 0"
					;
					$db->setQuery( $query );
					$count = $db->loadResult();
				}

				if ( $count <= 1 && $user->get( 'gid' ) == 25 ) {
				// cannot delete Super Admin where it is the only one that exists
					$msg = "You cannot delete this Super Administrator as it is the only active Super Administrator for your site";
				} else {
					// delete user
					$user->delete();
					$msg = '';

					JRequest::setVar( 'task', 'remove' );
					JRequest::setVar( 'cid', $id );

					// delete user acounts active sessions
					logoutUser();
				}
			}
		}
	}

	josRedirect( 'index2.php?option=com_users', $msg);
}

/**
* Unblocks one or more user records
*/
function unBlockUser( ) {
	changeUserBlock( 0 );
}

/**
* Blocks one or more user records
*/
function blockUser( ) {
	changeUserBlock( 1 );
}

/**
* Blocks or Unblocks one or more user records
* @param integer 0 if unblock, 1 if blocking
*/
function changeUserBlock( $block=1 ) {
	global $mainframe;

	$db = $mainframe->getDBO();

	$option = JRequest::getVar( 'option');
	$cid 	= JRequest::getVar( 'cid', array( 0 ), '', 'array' );
	if (!is_array( $cid )) {
		$cid = array ( 0 );
	}

	if (count( $cid ) < 1) {
		$action = $block ? 'block' : 'unblock';
		echo "<script> alert('". JText::_( 'Select an item to', true ) ." ". $action ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__users"
	. "\n SET block = $block"
	. "\n WHERE id IN ( $cids )"
	;
	$db->setQuery( $query );

	if (!$db->query()) {
		echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	// if action is to block a user
	if ( $block == 1 ) {
		foreach( $cid as $id ) {
			JRequest::setVar( 'task', 'block' );
			JRequest::setVar( 'cid', $id );

			// delete user acounts active sessions
			logoutUser();
		}
	}

	josRedirect( 'index2.php?option='. $option );
}

/**
 * logout selected users
*/
function logoutUser( ) {
	global $mainframe, $currentUser;
	$db		=& $mainframe->getDBO();
	$task 	= JRequest::getVar( 'task' );
	$cids 	= JRequest::getVar( 'cid', array( 0 ), '', 'array' );
	$client = JRequest::getVar( 'client', 0, '', 'int' );
	$id 	= JRequest::getVar( 'id', 0, '', 'int' );

	if ( is_array( $cids ) ) {
		if ( count( $cids ) < 1 ) {
			josRedirect( 'index2.php?option=com_users', JText::_( 'Please select a user' ) );
		}
		$cids = implode( ',', $cids );
	}

	if ($task == 'logout'){
		$query = "DELETE FROM #__session"
		. "\n WHERE userid IN ( $cids )"
		;
	} else if ($task == 'flogout'){
		$query = "DELETE FROM #__session"
		. "\n WHERE userid = $id"
		. "\n AND client_id = $client"
		;
	}

	if (isset( $query ) ) {
		$db->setQuery( $query );
		$db->query();
	}

	$msg = JText::_( 'User Sesssion ended' );
	switch ( $task ) {
		case 'flogout':
			josRedirect( 'index2.php', $msg );
			break;

		case 'remove':
		case 'block':
			return;
			break;

		default:
			josRedirect( 'index2.php?option=com_users', $msg );
			break;
	}
}
?>