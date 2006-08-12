<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Menus
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

/**
* @package Joomla
* @subpackage Menus
*/
class content_blog_section {

	/**
	* @param database A database connector object
	* @param integer The unique id of the section to edit (0 if new)
	*/
	function edit( $uid, $menutype, $option ) 
	{
		$db   =& JFactory::getDBO();
		$user =& JFactory::getUser();
		
		$menu =& JTable::getInstance('menu', $db );
		$menu->load( $uid );

		// fail if checked out not by 'me'
		if ($menu->checked_out && $menu->checked_out <> $user->get('id')) {
        	$alert = sprintf( JText::_( 'DESCBEINGEDITTED' ), JText::_( 'The module' ), $row->title );
			$action = "document.location.href='index2.php?option=$option'";
			mosErrorAlert( $alert, $action );
		}

		if ($uid) {
			$menu->checkout( $user->get('id') );
			// get previously selected Categories
			$params = new JParameter( $menu->params );
			$secids = $params->def( 'sectionid', '' );
			if ( $secids ) {
				$query = "SELECT s.id AS `value`, s.id AS `id`, s.title AS `text`"
				. "\n FROM #__sections AS s"
				. "\n WHERE s.scope = 'content'"
				. "\n AND s.id IN ( $secids )"
				. "\n ORDER BY s.name"
				;
				$db->setQuery( $query );
				$lookup = $db->loadObjectList();
			} else {
				$lookup 			= '';
			}
		} else {
			$menu->type 			= 'content_blog_section';
			$menu->menutype 		= $menutype;
			$menu->ordering 		= 9999;
			$menu->parent 			= JRequest::getVar( 'parent', 0, 'post', 'int' );
			$menu->published 		= 1;
			$lookup 				= '';
		}

		// build the html select list for section
		$rows[] = mosHTML::makeOption( '', JText::_( 'All Sections' ) );
		$query = "SELECT s.id AS `value`, s.id AS `id`, s.title AS `text`"
		. "\n FROM #__sections AS s"
		. "\n WHERE s.scope = 'content'"
		. "\n ORDER BY s.name"
		;
		$db->setQuery( $query );
		$rows = array_merge( $rows, $db->loadObjectList() );
		$section = mosHTML::selectList( $rows, 'secid[]', 'class="inputbox" size="10" multiple="multiple"', 'value', 'text', $lookup );
		$lists['sectionid']		= $section;

		// build the html select list for ordering
		$lists['ordering'] 		= mosAdminMenus::Ordering( $menu, $uid );
		// build the html select list for the group access
		$lists['access'] 		= mosAdminMenus::Access( $menu );
		// build the html select list for paraent item
		$lists['parent'] 		= JMenuHelper::Parent( $menu );
		// build published button option
		$lists['published'] 	= mosAdminMenus::Published( $menu );
		// build the url link output
		$lists['link'] 		= JMenuHelper::Link( $menu, $uid );

		// get params definitions
		$params = new JParameter( $menu->params, JApplicationHelper::getPath( 'menu_xml', $menu->type ), 'menu' );

		content_blog_section_html::edit( $menu, $lists, $params, $option );
	}

	function saveMenu( $option, $task )
	{
		global $mainframe;

		$db =& JFactory::getDBO();
		
		$params =JRequest::getVar( 'params', array(), 'post', 'array' );
		$secids	= JRequest::getVar( 'secid', array(), 'post', 'array' );
		$secid	= implode( ',', $secids );

		$_POST['params']['sectionid']	= $secid;

		$row =& JTable::getInstance('menu', $db );

		if (!$row->bind( $_POST )) {
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}

		if ( count( $secids )== 1 && $secids[0] != '' ) {
			$row->link = str_replace( 'id=0','id='. $secids[0], $row->link );
			$row->componentid = $secids[0];
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
		$row->reorder( "menutype = '$row->menutype' AND parent = $row->parent" );

		$msg = JText::_( 'Menu item Saved' );
		switch ( $task ) {
			case 'apply':
				$mainframe->redirect( 'index2.php?option='. $option .'&menutype='. $row->menutype .'&task=edit&id='. $row->id, $msg );
				break;

			case 'save':
			default:
				$mainframe->redirect( 'index2.php?option='. $option .'&menutype='. $row->menutype, $msg );
			break;
		}
	}

}
?>
