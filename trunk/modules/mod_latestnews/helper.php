<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE . '/components/com_content/helpers/content.php');

class modLatestNewsHelper
{
	function getList(&$params)
	{
		global $mainframe;

		$db			=& JFactory::getDBO();
		$user		=& JFactory::getUser();
		$userId		= (int) $user->get('id');

		$type		= (int) $params->get('type', 1);
		$count		= (int) $params->get('count', 5);
		$catid		= trim( $params->get('catid') );
		$secid		= trim( $params->get('secid') );
		$show_front	= $params->get('show_front', 1);

		$contentConfig = &JComponentHelper::getParams( 'com_content' );
		$access		= !$contentConfig->get('shownoauth');

		$nullDate	= $db->getNullDate();
		$now		= date('Y-m-d H:i:s', time());

		$where		= 'a.state = 1'
			. "\n AND ( a.publish_up = '$nullDate' OR a.publish_up <= '$now' )"
			. "\n AND ( a.publish_down = '$nullDate' OR a.publish_down >= '$now' )"
			;

		// User Filter
		switch ($params->get( 'user_id' ))
		{
			case 'by_me':
				$where .= ' AND (created_by = ' . $userId . ' OR modified_by = ' . $userId . ')';
				break;
			case 'not_me':
				$where .= ' AND (created_by <> ' . $userId . ' AND modified_by <> ' . $userId . ')';
				break;
		}
		
		// Ordering
		switch ($params->get( 'ordering' ))
		{
			case 'm_dsc':
				$ordering		= 'a.modified DESC, a.created DESC';
				break;
			case 'c_dsc':
			default:
				$ordering		= 'a.created DESC';
				break;
		}

		// select between Content Items, Static Content or both
		switch ($type)
		{
			case 2 :
				//Static Content only
				$query = "SELECT a.id, a.title, m.id AS my_itemid " .
					"\n FROM #__content AS a " .
					"\n LEFT OUTER JOIN #__menu AS m ON m.componentid = a.id " .
					"\n WHERE $where AND a.sectionid = 0" .
					"\n AND m.type = 'content_typed' ".
					($access ? "\n AND a.access <= " .$user->get('gid') : '').
					"\n ORDER BY $ordering";
	
				$db->setQuery($query, 0, $count);
				$rows = $db->loadObjectList();
				break;

			case 3 :
				// Both
				$query = "SELECT a.id, a.title, a.sectionid, a.catid, cc.access AS cat_access, s.access AS sec_access, cc.published AS cat_state, s.published AS sec_state" .
					"\n FROM #__content AS a" .
					"\n LEFT JOIN #__categories AS cc ON cc.id = a.catid" .
					"\n LEFT JOIN #__sections AS s ON s.id = a.sectionid" .
					"\n WHERE $where" .
					($access ? "\n AND a.access <= " .$user->get('gid') : '') .
					"\n ORDER BY $ordering";

				$db->setQuery( $query, 0, $count );
				$rows = $db->loadObjectList();
				break;

			case 1 :
			default :
				if ($catid)
				{
					$ids = explode( ',', $catid );
					JArrayHelper::toInteger( $ids );
					$catCondition = ' AND (a.catid=' . implode( ' OR a.catid=', $ids ) . ')';
				}
				if ($secid)
				{
					$ids = explode( ',', $secid );
					JArrayHelper::toInteger( $ids );
					$secCondition = ' AND (a.sectionid=' . implode( ' OR a.sectionid=', $ids ) . ')';
				}

				// Content Items only
				$query = "SELECT a.id, a.title, a.sectionid, a.catid" .
					"\n FROM #__content AS a" .
					($show_front == '0' ? "\n LEFT JOIN #__content_frontpage AS f ON f.content_id = a.id" : '') .
					"\n INNER JOIN #__categories AS cc ON cc.id = a.catid" .
					"\n INNER JOIN #__sections AS s ON s.id = a.sectionid" .
					"\n WHERE $where AND a.sectionid > 0" .
					($access ? "\n AND a.access <= " .$user->get('gid'). " AND cc.access <= " .$user->get('gid'). " AND s.access <= " .$user->get('gid') : '').
					($catid ? "\n $catCondition" : '').
					($secid ? "\n $secCondition" : '').
					($show_front == '0' ? "\n AND f.content_id IS NULL" : '').
					"\n AND s.published = 1" .
					"\n AND cc.published = 1" .
					"\n ORDER BY $ordering";
				$db->setQuery($query, 0, $count);
				$rows = $db->loadObjectList();
				break;
		}

		$i 	   = 0;
		$lists = array();
		foreach ( $rows as $row )
		{
			// get Itemid
			switch ( $type )
			{
				case 2:
					$Itemid = $row->my_itemid;
					break;

				case 3:
					if (($row->cat_state == 1 || $row->cat_state == '') && ($row->sec_state == 1 || $row->sec_state == '') && ($row->cat_access <= $user->get('gid') || $row->cat_access == '' || !$access) && ($row->sec_access <= $user->get('gid') || $row->sec_access == '' || !$access))
					{
						if ($row->sectionid) {
							$row->my_itemid = JContentHelper::getItemid($row->id);
						} else {
							$row->my_itemid = null;
						}
					}
					break;

				case 1:
				default:
					$row->my_itemid = JContentHelper::getItemid($row->id);
					break;
			}

			// & xhtml compliance conversion
			$row->title = ampReplace( $row->title );

			$link = sefRelToAbs( 'index.php?option=com_content&amp;view=article&amp;id='. $row->id . '&amp;Itemid='. $row->my_itemid );

			$lists[$i]->link	= $link;
			$lists[$i]->text	= $row->title;
			$i++;
		}

		return $lists;
	}
}