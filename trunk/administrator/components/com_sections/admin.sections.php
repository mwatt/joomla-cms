<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Sections
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

require_once( JApplicationHelper::getPath( 'admin_html' ) );

// get parameters from the URL or submitted form
$scope 		= JRequest::getVar( 'scope' );
$cid 		= JRequest::getVar( 'cid', array(0), '', 'array' );
$section 	= JRequest::getVar( 'scope' );
if (!is_array( $cid )) {
	$cid = array(0);
}

switch ($task) {
	case 'new':
	case 'edit':
		editSection( );
		break;

	case 'go2menu':
	case 'go2menuitem':
	case 'menulink':
	case 'save':
	case 'apply':
		saveSection( $option, $scope, $task );
		break;

	case 'remove':
		removeSections( $cid, $scope, $option );
		break;

	case 'copyselect':
		copySectionSelect( $option, $cid, $section );
		break;

	case 'copysave':
		copySectionSave( $cid );
		break;

	case 'publish':
		publishSections( $scope, $cid, 1, $option );
		break;

	case 'unpublish':
		publishSections( $scope, $cid, 0, $option );
		break;

	case 'cancel':
		cancelSection( $option, $scope );
		break;

	case 'orderup':
		orderSection( $cid[0], -1, $option, $scope );
		break;

	case 'orderdown':
		orderSection( $cid[0], 1, $option, $scope );
		break;

	case 'accesspublic':
		accessMenu( $cid[0], 0, $option );
		break;

	case 'accessregistered':
		accessMenu( $cid[0], 1, $option );
		break;

	case 'accessspecial':
		accessMenu( $cid[0], 2, $option );
		break;

	case 'saveorder':
		saveOrder( $cid );
		break;

	default:
		showSections( $scope, $option );
		break;
}

/**
* Compiles a list of categories for a section
* @param database A database connector object
* @param string The name of the category section
* @param string The name of the current user
*/
function showSections( $scope, $option ) 
{
	global $mainframe;

	$db					=& JFactory::getDBO();
	$user 				=& JFactory::getUser();
	$filter_order		= $mainframe->getUserStateFromRequest( "$option.filter_order", 		'filter_order', 	's.ordering' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.filter_order_Dir",	'filter_order_Dir',	'' );
	$filter_state 		= $mainframe->getUserStateFromRequest( "$option.filter_state", 		'filter_state', 	'' );
	$limit 				= $mainframe->getUserStateFromRequest( "limit", 					'limit', 			$mainframe->getCfg('list_limit') );
	$limitstart 		= $mainframe->getUserStateFromRequest( "$option.limitstart", 		'limitstart', 		0 );
	$search 			= $mainframe->getUserStateFromRequest( "$option.search", 			'search', 			'' );
	$search 			= $db->getEscaped( trim( JString::strtolower( $search ) ) );

	$where[] = "s.scope = '$scope'";

	if ( $filter_state ) {
		if ( $filter_state == 'P' ) {
			$where[] = "s.published = 1";
		} else if ($filter_state == 'U' ) {
			$where[] = "s.published = 0";
		}
	}
	if ($search) {
		$where[] = "LOWER(s.title) LIKE '%$search%'";
	}

	$where 		= ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );
	$orderby 	= "\n ORDER BY $filter_order $filter_order_Dir, s.ordering";

	// get the total number of records
	$query = "SELECT COUNT(*)"
	. "\n FROM #__sections AS s"
	. $where
	;
	$db->setQuery( $query );
	$total = $db->loadResult();

	jimport('joomla.presentation.pagination');
	$pageNav = new JPagination( $total, $limitstart, $limit );

	$query = "SELECT s.*, g.name AS groupname, u.name AS editor"
	. "\n FROM #__sections AS s"
	. "\n LEFT JOIN #__content AS cc ON s.id = cc.sectionid"
	. "\n LEFT JOIN #__users AS u ON u.id = s.checked_out"
	. "\n LEFT JOIN #__groups AS g ON g.id = s.access"
	. $where
	. "\n GROUP BY s.id"
	. $orderby
	;
	$db->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $db->loadObjectList();
	if ($db->getErrorNum()) {
		echo $db->stderr();
		return false;
	}

	$count = count( $rows );
	// number of Active Items
	for ( $i = 0; $i < $count; $i++ ) {
		$query = "SELECT COUNT( a.id )"
		. "\n FROM #__categories AS a"
		. "\n WHERE a.section = '". $rows[$i]->id ."'"
		. "\n AND a.published <> -2"
		;
		$db->setQuery( $query );
		$active = $db->loadResult();
		$rows[$i]->categories = $active;
	}
	// number of Active Items
	for ( $i = 0; $i < $count; $i++ ) {
		$query = "SELECT COUNT( a.id )"
		. "\n FROM #__content AS a"
		. "\n WHERE a.sectionid = '". $rows[$i]->id ."'"
		. "\n AND a.state <> -2"
		;
		$db->setQuery( $query );
		$active = $db->loadResult();
		$rows[$i]->active = $active;
	}
	// number of Trashed Items
	for ( $i = 0; $i < $count; $i++ ) {
		$query = "SELECT COUNT( a.id )"
		. "\n FROM #__content AS a"
		. "\n WHERE a.sectionid = '". $rows[$i]->id ."'"
		. "\n AND a.state = -2"
		;
		$db->setQuery( $query );
		$trash = $db->loadResult();
		$rows[$i]->trash = $trash;
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

	sections_html::show( $rows, $scope, $user->get('id'), $pageNav, $option, $lists );
}

/**
* Compiles information to add or edit a section
* @param database A database connector object
* @param string The name of the category section
* @param integer The unique id of the category to edit (0 if new)
* @param string The name of the current user
*/
function editSection( ) 
{
	$db			=& JFactory::getDBO();
	$user 		=& JFactory::getUser();
	
	$option 	= JRequest::getVar( 'option');
	$scope 		= JRequest::getVar( 'scope' );
	$cid 		= JRequest::getVar( 'cid', array(0), '', 'array' );
	if (!is_array( $cid )) {
		$cid = array(0);
	}

	$row =& JTable::getInstance('section', $db );
	// load the row from the db table
	$row->load( $cid[0] );

	// fail if checked out not by 'me'
	if ($row->isCheckedOut( $user->get('id') )) {
    	$msg = sprintf( JText::_( 'DESCBEINGEDITTED' ), JText::_( 'The section' ), $row->title );
		josRedirect( 'index2.php?option='. $option .'&scope='. $row->scope .'&josmsg='. $msg );
	}

	if ( $cid[0] ) {
		$row->checkout( $user->get('id') );
		if ( $row->id > 0 ) {
			$query = "SELECT *"
			. "\n FROM #__menu"
			. "\n WHERE componentid = '". $row->id ."'"
			. "\n AND ( type = 'content_archive_section' OR type = 'content_blog_section' OR type = 'content_section' )"
			;
			$db->setQuery( $query );
			$menus = $db->loadObjectList();
			$count = count( $menus );
			for( $i = 0; $i < $count; $i++ ) {
				switch ( $menus[$i]->type ) {
					case 'content_section':
						$menus[$i]->type = JText::_( 'Section Table' );
						break;

					case 'content_blog_section':
						$menus[$i]->type = JText::_( 'Section Blog' );
						break;

					case 'content_archive_section':
						$menus[$i]->type = JText::_( 'Section Blog Archive' );
						break;
				}
			}
		} else {
			$menus = array();
		}
	} else {
		$row->scope 		= $scope;
		$row->published 	= 1;
		$menus 			= array();
	}

	// build the html select list for section types
	$types[] = mosHTML::makeOption( '', JText::_( 'Select Type' ) );
	$types[] = mosHTML::makeOption( 'content_section', JText::_( 'Section List' ) );
	$types[] = mosHTML::makeOption( 'content_blog_section', JText::_( 'Section Blog' ) );
	$types[] = mosHTML::makeOption( 'content_archive_section', JText::_( 'Section Archive Blog' ) );
	$lists['link_type'] 		= mosHTML::selectList( $types, 'link_type', 'class="inputbox" size="1"', 'value', 'text' );;

	// build the html select list for ordering
	$query = "SELECT ordering AS value, title AS text"
	. "\n FROM #__sections"
	. "\n WHERE scope='$row->scope' ORDER BY ordering"
	;
	$lists['ordering'] 			= mosAdminMenus::SpecificOrdering( $row, $cid[0], $query );

	// build the select list for the image positions
	$active =  ( $row->image_position ? $row->image_position : 'left' );
	$lists['image_position'] 	= mosAdminMenus::Positions( 'image_position', $active, NULL, 0 );
	// build the html select list for images
	$lists['image'] 			= mosAdminMenus::Images( 'image', $row->image );
	// build the html select list for the group access
	$lists['access'] 			= mosAdminMenus::Access( $row );
	// build the html radio buttons for published
	$lists['published'] 		= mosHTML::yesnoRadioList( 'published', 'class="inputbox"', $row->published );
	// build the html select list for menu selection
	$lists['menuselect']		= mosAdminMenus::MenuSelect( );

	sections_html::edit( $row, $option, $lists, $menus );
}

/**
* Saves the catefory after an edit form submit
* @param database A database connector object
* @param string The name of the category section
*/
function saveSection( $option, $scope, $task ) 
{
	$db			=& JFactory::getDBO();
	$menu 		= JRequest::getVar( 'menu', 'mainmenu', 'post' );
	$menuid		= JRequest::getVar( 'menuid', 0, 'post', 'int' );
	$oldtitle 	= JRequest::getVar( 'oldtitle', '', '', 'post' );

	$row =& JTable::getInstance('section', $db );
	if (!$row->bind( $_POST )) {
		echo "<script> alert('".$row->getError()."'); document.location.href='index2.php?option=$option&scope=$scope&task=new'; </script>\n";
		exit();
	}
	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); document.location.href='index2.php?option=$option&scope=$scope&task=new'; </script>\n";
		exit();
	}
	if ( $oldtitle ) {
		if ( $oldtitle <> $row->title ) {
			$query = "UPDATE #__menu"
			. "\n SET name = '$row->title'"
			. "\n WHERE name = '$oldtitle'"
			. "\n AND type = 'content_section'"
			;
			$db->setQuery( $query );
			$db->query();
		}
	}

	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();
	$row->reorder( "scope='$row->scope'" );

	switch ( $task ) {
		case 'go2menu':
			josRedirect( 'index2.php?option=com_menus&menutype='. $menu );
			break;

		case 'go2menuitem':
			josRedirect( 'index2.php?option=com_menus&menutype='. $menu .'&task=edit&hidemainmenu=1&id='. $menuid );
			break;

		case 'menulink':
			menuLink( $row->id );
			break;

		case 'apply':
			$msg = JText::_( 'Changes to Section saved' );
			josRedirect( 'index2.php?option='. $option .'&scope='. $scope .'&task=editA&hidemainmenu=1&id='. $row->id, $msg );
			break;

		case 'save':
		default:
			$msg = JText::_( 'Section saved' );
			josRedirect( 'index2.php?option='. $option .'&scope='. $scope, $msg );
			break;
	}
}
/**
* Deletes one or more categories from the categories table
* @param database A database connector object
* @param string The name of the category section
* @param array An array of unique category id numbers
*/
function removeSections( $cid, $scope, $option ) 
{
	$db =& JFactory::getDBO();
	if (count( $cid ) < 1) {
		echo "<script> alert('". JText::_( 'Select a section to delete', true ) ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$cids = implode( ',', $cid );

	$query = "SELECT s.id, s.name, COUNT(c.id) AS numcat"
	. "\n FROM #__sections AS s"
	. "\n LEFT JOIN #__categories AS c ON c.section=s.id"
	. "\n WHERE s.id IN ( $cids )"
	. "\n GROUP BY s.id"
	;
	$db->setQuery( $query );
	if (!($rows = $db->loadObjectList())) {
		echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
	}

	$err = array();
	$cid = array();
	foreach ($rows as $row) {
		if ($row->numcat == 0) {
			$cid[] = $row->id;
			$name[] = $row->name;
		} else {
			$err[] = $row->name;
		}
	}

	if (count( $cid )) {
		$cids = implode( ',', $cid );
		$query = "DELETE FROM #__sections"
		. "\n WHERE id IN ( $cids )"
		;
		$db->setQuery( $query );
		if (!$db->query()) {
			echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
		}
	}

	if (count( $err )) {
		$cids = implode( ', ', $err );
    	$msg = sprintf( JText::_( 'DESCCANNOTBEREMOVED' ), $cids );
		josRedirect( 'index2.php?option='. $option .'&scope='. $scope, $msg );
	}

	$names = implode( ', ', $name );
	$msg = sprintf( JText::_( 'Sections successfully deleted' ), $names );
	josRedirect( 'index2.php?option='. $option .'&scope='. $scope, $msg );
}

/**
* Publishes or Unpublishes one or more categories
* @param database A database connector object
* @param string The name of the category section
* @param integer A unique category id (passed from an edit form)
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
* @param string The name of the current user
*/
function publishSections( $scope, $cid=null, $publish=1, $option ) 
{
	$db 	=& JFactory::getDBO();
	$user 	=& JFactory::getUser();
	
	if ( !is_array( $cid ) || count( $cid ) < 1 ) {
		$action = $publish ? 'publish' : 'unpublish';
		echo "<script> alert('". JText::_( 'Select a section to', true ) ." ". $action ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$cids = implode( ',', $cid );
	$count = count( $cid );
	if ( $publish ) {
		if ( !$count ){
			echo "<script> alert('". JText::_( 'Cannot Publish an Empty Section', true ) .": ". $count ."'); window.history.go(-1);</script>\n";
			return;
		}
	}

	$query = "UPDATE #__sections"
	. "\n SET published = " . intval( $publish )
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = " .$user->get('id'). " ) )"
	;
	$db->setQuery( $query );
	if (!$db->query()) {
		echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if ( $count == 1 ) {
		$row =& JTable::getInstance('section', $db );
		$row->checkin( $cid[0] );
	}

	// check if section linked to menu items if unpublishing
	if ( $publish == 0 ) {
		$query = "SELECT id"
		. "\n FROM #__menu"
		. "\n WHERE type = 'content_section'"
		. "\n AND componentid IN ( $cids )"
		;
		$db->setQuery( $query );
		$menus = $db->loadObjectList();

		if ($menus) {
			foreach ($menus as $menu) {
				$query = "UPDATE #__menu"
				. "\n SET published = " . intval( $publish )
				. "\n WHERE id = $menu->id"
				;
				$db->setQuery( $query );
				$db->query();
			}
		}
	}

	josRedirect( 'index2.php?option='. $option .'&scope='. $scope );
}

/**
* Cancels an edit operation
* @param database A database connector object
* @param string The name of the category section
* @param integer A unique category id
*/
function cancelSection( $option, $scope ) 
{	
	$db =& JFactory::getDBO();
	$row =& JTable::getInstance('section', $db );
	$row->bind( $_POST );
	$row->checkin();

	josRedirect( 'index2.php?option='. $option .'&scope='. $scope );
}

/**
* Moves the order of a record
* @param integer The increment to reorder by
*/
function orderSection( $uid, $inc, $option, $scope ) 
{
	$db =& JFactory::getDBO();
	$row =& JTable::getInstance('section', $db );
	$row->load( $uid );
	$row->move( $inc, "scope = '$row->scope'" );

	josRedirect( 'index2.php?option='. $option .'&scope='. $scope );
}


/**
* Form for copying item(s) to a specific menu
*/
function copySectionSelect( $option, $cid, $section ) 
{
	$db =& JFactory::getDBO();
	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script> alert('". JText::_( 'Select an item to move', true ) ."'); window.history.go(-1);</script>\n";
		exit;
	}

	## query to list selected categories
	$cids = implode( ',', $cid );
	$query = "SELECT a.name, a.id"
	. "\n FROM #__categories AS a"
	. "\n WHERE a.section IN ( $cids )"
	;
	$db->setQuery( $query );
	$categories = $db->loadObjectList();

	## query to list items from categories
	$query = "SELECT a.title, a.id"
	. "\n FROM #__content AS a"
	. "\n WHERE a.sectionid IN ( $cids )"
	. "\n ORDER BY a.sectionid, a.catid, a.title"
	;
	$db->setQuery( $query );
	$contents = $db->loadObjectList();

	sections_html::copySectionSelect( $option, $cid, $categories, $contents, $section );
}


/**
* Save the item(s) to the menu selected
*/
function copySectionSave( $sectionid ) 
{	
	$db			=& JFactory::getDBO();
	$title 		= JRequest::getVar( 'title' );
	$contentid 	= JRequest::getVar( 'content' );
	$categoryid = JRequest::getVar( 'category' );

	// copy section
	$section =& JTable::getInstance('section', $db );
	foreach( $sectionid as $id ) {
		$section->load( $id );
		$section->id 	= NULL;
		$section->title = $title;
		$section->name 	= $title;
		if ( !$section->check() ) {
			echo "<script> alert('".$section->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}

		if ( !$section->store() ) {
			echo "<script> alert('".$section->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		$section->checkin();
		$section->reorder( "section = '$section->id'" );
		// stores original catid
		$newsectids[]["old"] = $id;
		// pulls new catid
		$newsectids[]["new"] = $section->id;
	}
	$sectionMove = $section->id;

	// copy categories
	$category =& JTable::getInstance('category', $db );
	foreach( $categoryid as $id ) {
		$category->load( $id );
		$category->id = NULL;
		$category->section = $sectionMove;
		foreach( $newsectids as $newsectid ) {
			if ( $category->section == $newsectid["old"] ) {
				$category->section = $newsectid["new"];
			}
		}
		if (!$category->check()) {
			echo "<script> alert('".$category->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}

		if (!$category->store()) {
			echo "<script> alert('".$category->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		$category->checkin();
		$category->reorder( "section = '$category->section'" );
		// stores original catid
		$newcatids[]["old"] = $id;
		// pulls new catid
		$newcatids[]["new"] = $category->id;
	}

	$content =& JTable::getInstance('content', $db );
	foreach( $contentid as $id) {
		$content->load( $id );
		$content->id = NULL;
		$content->hits = 0;
		foreach( $newsectids as $newsectid ) {
			if ( $content->sectionid == $newsectid["old"] ) {
				$content->sectionid = $newsectid["new"];
			}
		}
		foreach( $newcatids as $newcatid ) {
			if ( $content->catid == $newcatid["old"] ) {
				$content->catid = $newcatid["new"];
			}
		}
		if (!$content->check()) {
			echo "<script> alert('".$content->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}

		if (!$content->store()) {
			echo "<script> alert('".$content->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		$content->checkin();
	}
	$sectionOld =& JTable::getInstance('section', $db );
	$sectionOld->load( $sectionMove );

	$msg = sprintf( JText::_( 'DESCCATANDITEMSCOPIED' ), $sectionOld-> name, $title );
	josRedirect( 'index2.php?option=com_sections&scope=content&josmsg='. $msg );
}

/**
* changes the access level of a record
* @param integer The increment to reorder by
*/
function accessMenu( $uid, $access, $option ) 
{
	$db	=& JFactory::getDBO();
	$row =& JTable::getInstance('section', $db );
	$row->load( $uid );
	$row->access = $access;

	if ( !$row->check() ) {
		return $row->getError();
	}
	if ( !$row->store() ) {
		return $row->getError();
	}

	josRedirect( 'index2.php?option='. $option .'&scope='. $row->scope );
}

function menuLink( $id ) 
{
	$db		 =& JFactory::getDBO();
	
	$section =& JTable::getInstance('section', $db );
	$section->bind( $_POST );
	$section->checkin();

	$menu 		= JRequest::getVar( 'menuselect', '', 'post' );
	$name 		= JRequest::getVar( 'link_name', '', 'post' );
	$type 		= JRequest::getVar( 'link_type', '', 'post' );

	$name		= ampReplace($name);

	switch ( $type ) {
		case 'content_section':
			$link 		= 'index.php?option=com_content&task=section&id='. $id;
			$menutype	= 'Section Table';
			break;

		case 'content_blog_section':
			$link 		= 'index.php?option=com_content&task=blogsection&id='. $id;
			$menutype	= 'Section Blog';
			break;

		case 'content_archive_section':
			$link 		= 'index.php?option=com_content&task=archivesection&id='. $id;
			$menutype	= 'Section Blog Archive';
			break;
	}

	$row 				=& JTable::getInstance('menu', $db );
	$row->menutype 		= $menu;
	$row->name 			= $name;
	$row->type 			= $type;
	$row->published		= 1;
	$row->componentid	= $id;
	$row->link			= $link;
	$row->ordering		= 9999;

	if ( $type == 'content_blog_section' ) {
		$row->params = 'sectionid='. $id;
	}

	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();
	$row->reorder( "menutype = '$menu'" );

	$msg = sprintf( JText::_( 'menutype successfully created' ), $name, $menutype, $menu );
	josRedirect( 'index2.php?option=com_sections&scope=content&task=editA&hidemainmenu=1&id='. $id,  $msg );
}

function saveOrder( &$cid ) 
{
	$db			=& JFactory::getDBO();
	
	$total		= count( $cid );
	$order 		= JRequest::getVar( 'order', array(0), 'post', 'array' );
	$row 		=& JTable::getInstance('section', $db );
	$conditions = array();

	// update ordering values
	for( $i=0; $i < $total; $i++ ) 
	{
		$row->load( $cid[$i] );
		if ($row->ordering != $order[$i]) {
			$row->ordering = $order[$i];
			if (!$row->store()) {
				echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
				exit();
			} 
			// remember to updateOrder this group
			$condition = "scope = '$row->scope'";
			$found = false;
			foreach ( $conditions as $cond )
				if ($cond[1]==$condition) {
					$found = true;
					break;
				}
			if (!$found) $conditions[] = array($row->id, $condition);
		}
	}

	// execute updateOrder for each group
	foreach ( $conditions as $cond ) {
		$row->load( $cond[0] );
		$row->reorder( $cond[1] );
	}

	$msg 	= JText::_( 'New ordering saved' );
	josRedirect( 'index2.php?option=com_sections&scope=content', $msg );
}
?>
