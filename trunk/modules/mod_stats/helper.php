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
defined( '_JEXEC' ) or die( 'Restricted access' );

class modStatsHelper
{
	function &getList( &$params )
	{
		global $mainframe;
		
		$db =& JFactory::getDBO();
		
		$serverinfo 		= $params->get( 'serverinfo' );
		$siteinfo 			= $params->get( 'siteinfo' );
		$counter 			= $params->get( 'counter' );
		$increase 			= $params->get( 'increase' );

		$i = 0;
		if ( $serverinfo ) 
		{
			$rows[$i]->title 	= JText::_( 'OS' );
			$rows[$i]->data 	= substr( php_uname(), 0, 7 );
			$i++;
			$rows[$i]->title 	= JText::_( 'PHP' );
			$rows[$i]->data 	= phpversion();
			$i++;
			$rows[$i]->title 	= JText::_( 'MySQL' );
			$rows[$i]->data 	= mysql_get_server_info();
			$i++;
			$rows[$i]->title 	= JText::_( 'Time' );
			$rows[$i]->data 	= date( 'H:i', time() + ( $mainframe->getCfg('offset') * 60 * 60 ) );
			$i++;
			$rows[$i]->title 	= JText::_( 'Caching' );
			$rows[$i]->data 	=  $mainframe->getCfg('caching') ? JText::_( 'Enabled' ):JText::_( 'Disabled' );
			$i++;
			$rows[$i]->title 	= JText::_( 'GZip' );
			$rows[$i]->data 	= $mainframe->getCfg('gzip') ? JText::_( 'Enabled' ):JText::_( 'Disabled' );;
			$i++;
		}

		if ( $siteinfo ) 
		{
			$query = "SELECT COUNT( id ) AS count_users"
			. "\n FROM #__users"
			;
			$db->setQuery( $query );
			$members = $db->loadResult();

			$query = "SELECT COUNT( id ) AS count_items"
			. "\n FROM #__content"
			. "\n WHERE state = '1'"
			;
			$db->setQuery( $query );
			$items = $db->loadResult();

			$query = "SELECT COUNT( id ) AS count_links"
			. "\n FROM #__weblinks"
			. "\n WHERE published = '1'"
			;
			$db->setQuery( $query );
			$links = $db->loadResult();

			if ( $members ) {
				$rows[$i]->title 	= JText::_( 'Members' );
				$rows[$i]->data 	= $members;
				$i++;
			}

			if ( $items ) {
				$rows[$i]->title 	= JText::_( 'Content' );
				$rows[$i]->data 	= $items;
				$i++;
			}

			if ( $links ) {
				$rows[$i]->title 	= JText::_( 'Web Links' );
				$rows[$i]->data 	= $links;
				$i++;
			}
		}

		if ( $mainframe->getCfg('enable_stats') && $counter ) 
		{
			$query = "SELECT SUM( hits ) AS count"
			. "\n FROM #__stats_agents"
			. "\n WHERE type = '1'";
			$db->setQuery( $query );
			$hits = $db->loadResult();

			$hits = $hits + $increase;
			if ( $hits == NULL ) {
				$hits = 0;
			}

			$rows[$i]->title 	= JText::_( 'Visitors' );
			$rows[$i]->data 	= $hits;
		}

		return $rows;
	}
}
