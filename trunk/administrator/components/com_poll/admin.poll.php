<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Polls
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
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
if (!$user->authorize( 'com_poll', 'manage' ))
{
	josRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

require_once( JApplicationHelper::getPath( 'admin_html' ) );
require_once( JApplicationHelper::getPath( 'class' ) );

$cid 	= JRequest::getVar( 'cid', array(0), '', 'array' );
if (!is_array( $cid )) {
	$cid = array(0);
}

switch( $task ) {
	case 'new':
	case 'edit':
		editPoll( );
		break;

	case 'save':
	case 'apply':
		savePoll( $task );
		break;

	case 'remove':
		removePoll( $cid, $option );
		break;

	case 'publish':
		publishPolls( $cid, 1, $option );
		break;

	case 'unpublish':
		publishPolls( $cid, 0, $option );
		break;

	case 'cancel':
		cancelPoll( $option );
		break;

	case 'preview':
		previewPoll($option);
		break;

	default:
		showPolls( $option );
		break;
}

function showPolls( $option ) {
	global $database, $mainframe;

	$filter_order		= $mainframe->getUserStateFromRequest( "$option.filter_order", 		'filter_order', 	'm.id' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.filter_order_Dir",	'filter_order_Dir',	'' );
	$filter_state 		= $mainframe->getUserStateFromRequest( "$option.filter_state", 		'filter_state', 	'' );
	$limit 				= $mainframe->getUserStateFromRequest( "limit", 					'limit', 			$mainframe->getCfg('list_limit') );
	$limitstart 		= $mainframe->getUserStateFromRequest( "$option.limitstart", 		'limitstart', 		0 );
	$search 			= $mainframe->getUserStateFromRequest( "$option.search", 			'search', 			'' );
	$search 			= $database->getEscaped( trim( JString::strtolower( $search ) ) );

	$where = array();

	if ( $filter_state ) {
		if ( $filter_state == 'P' ) {
			$where[] = "m.published = 1";
		} else if ($filter_state == 'U' ) {
			$where[] = "m.published = 0";
		}
	}
	if ($search) {
		$where[] = "LOWER(m.title) LIKE '%$search%'";
	}

	$where 		= ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );
	$orderby 	= "\n ORDER BY $filter_order $filter_order_Dir";

	$query = "SELECT COUNT(m.id)"
	. "\n FROM #__polls AS m"
	. $where
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	jimport('joomla.presentation.pagination');
	$pageNav = new JPagination( $total, $limitstart, $limit );

	$query = "SELECT m.*, u.name AS editor, COUNT(d.id) AS numoptions"
	. "\n FROM #__polls AS m"
	. "\n LEFT JOIN #__users AS u ON u.id = m.checked_out"
	. "\n LEFT JOIN #__poll_data AS d ON d.pollid = m.id AND d.text <> ''"
	. $where
	. "\n GROUP BY m.id"
	. $orderby
	;
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $database->loadObjectList();

	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}

	// state filter
	$lists['state']	= mosCommonHTML::selectState( $filter_state );

	// table ordering
	if ( $filter_order_Dir == 'DESC' ) {
		$lists['order_Dir'] = 'ASC';
	} else {
		$lists['order_Dir'] = 'DESC';
	}
	$lists['order'] = $filter_order;

	// search filter
	$lists['search']= $search;

	HTML_poll::showPolls( $rows, $pageNav, $option, $lists );
}

function editPoll( ) {
	global $database, $my;

	$cid 	= JRequest::getVar( 'cid', array(0));
	$option = JRequest::getVar( 'option');
	if (!is_array( $cid )) {
		$cid = array(0);
	}
	$uid 	= $cid[0];
	
	$row = new mosPoll( $database );
	// load the row from the db table
	$row->load( $uid );

	// fail if checked out not by 'me'
	if ($row->isCheckedOut( $my->id )) {
    	$msg = sprintf( JText::_( 'DESCBEINGEDITTED' ), JText::_( 'The poll' ), $row->title );
		josRedirect( 'index2.php?option='. $option, $msg );
	}

	$options = array();

	if ($uid) {
		$row->checkout( $my->id );
		$query = "SELECT id, text"
		. "\n FROM #__poll_data"
		. "\n WHERE pollid = $uid"
		. "\n ORDER BY id"
		;
		$database->setQuery($query);
		$options = $database->loadObjectList();
	} else {
		$row->lag = 3600*24;
	}

	// get selected pages
	if ( $uid ) {
		$query = "SELECT menuid AS value"
		. "\n FROM #__poll_menu"
		. "\n WHERE pollid = $row->id"
		;
		$database->setQuery( $query );
		$lookup = $database->loadObjectList();
	} else {
		$lookup = array( mosHTML::makeOption( 0, JText::_( 'All' ) ) );
	}

	// build the html select list
	$lists['select'] = mosAdminMenus::MenuLinks( $lookup, 1, 1 );

	HTML_poll::editPoll($row, $options, $lists );
}

function savePoll( $task ) {
	global $database, $my;

	// save the poll parent information
	$row = new mosPoll( $database );
	if (!$row->bind( $_POST )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$isNew = ($row->id == 0);

	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();
	// save the poll options
	$options = JRequest::getVar( 'polloption', array(), 'post', 'array' );

	foreach ($options as $i=>$text) {
		$text = $database->Quote($text);
		if ($isNew) {
			$query = "INSERT INTO #__poll_data"
			. "\n ( pollid, text )"
			. "\n VALUES ( $row->id, $text )"
			;
			$database->setQuery( $query );
			$database->query();
		} else {
			$query = "UPDATE #__poll_data"
			. "\n SET text = $text"
			. "\n WHERE id = $i"
			. "\n AND pollid = $row->id"
			;
			$database->setQuery( $query );
			$database->query();
		}
	}

	// update the menu visibility
	$selections = JRequest::getVar( 'selections', array(), 'post', 'array' );

	$query = "DELETE FROM #__poll_menu"
	. "\n WHERE pollid = $row->id"
	;
	$database->setQuery( $query );
	$database->query();

	for ($i=0, $n=count($selections); $i < $n; $i++) {
		$query = "INSERT INTO #__poll_menu"
		. "\n SET pollid = $row->id, menuid = ". $selections[$i]
		;
		$database->setQuery( $query );
		$database->query();
	}

	switch ($task) {
		case 'apply':
			$link = 'index2.php?option=com_poll&task=editA&id='. $row->id .'&hidemainmenu=1';
			break;

		case 'save':
		default:
			$link = 'index2.php?option=com_poll';
			break;
	}

	josRedirect($link);
}

function removePoll( $cid, $option )
{
	global $database;
	$msg = '';
	for ($i=0, $n=count($cid); $i < $n; $i++) {
		$poll = new mosPoll( $database );
		if (!$poll->delete( $cid[$i] )) {
			$msg .= $poll->getError();
		}
	}
	josRedirect( 'index2.php?option='. $option, $msg );
}

/**
* Publishes or Unpublishes one or more records
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
* @param string The current url option
*/
function publishPolls( $cid=null, $publish=1, $option )
{
	global $database, $my;

	$catid = JRequest::getVar( 'catid', array(0), 'post', 'array' );

	if (!is_array( $cid ) || count( $cid ) < 1) {
		$action = $publish ? 'publish' : 'unpublish';
		echo "<script> alert('". JText::_( 'Select an item to', true ) ." ". $action ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__polls"
	. "\n SET published = " . intval( $publish )
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = $my->id ) )"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (count( $cid ) == 1) {
		$row = new mosPoll( $database );
		$row->checkin( $cid[0] );
	}
	josRedirect( 'index2.php?option='. $option );
}

function cancelPoll( $option )
{
	global $database;
	$row = new mosPoll( $database );
	$row->bind( $_POST );
	$row->checkin();
	josRedirect( 'index2.php?option='. $option );
}

function previewPoll($option)
{
	global $database, $mainframe;

	$mainframe->setPageTitle(JText::_('Poll Preview'));

	$pollid = JRequest::getVar( 'pollid', 0, '', 'int' );
	$css = JRequest::getVar( 't', '' );

	$query = "SELECT title"
		. "\n FROM #__polls"
		. "\n WHERE id = $pollid"
	;
	$database->setQuery( $query );
	$title = $database->loadResult();

	$query = "SELECT text"
		. "\n FROM #__poll_data"
		. "\n WHERE pollid = $pollid"
		. "\n ORDER BY id"
	;
	$database->setQuery( $query );
	$options = $database->loadResultArray();

	HTML_poll::previewPoll($title, $options);
}
?>
