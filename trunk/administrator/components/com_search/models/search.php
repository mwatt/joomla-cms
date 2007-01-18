<?php
/**
 * @version		$Id: search.php 6138 2007-01-02 03:44:18Z eddiea $
 * @package		Joomla
 * @subpackage	Search
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights
 * reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport( 'joomla.application.component.model' );

/**
 * @package		Joomla
 * @subpackage	Search
 * @author Hannes Papenberg
 */
class SearchModelSearch extends JModel
{
	/**
	 * Overridden constructor
	 * @access	protected
	 */
	function __construct()
	{
		parent::__construct();
	}

	function reset()
	{
		$db =& JFactory::getDBO();
		$db->setQuery( "DELETE FROM #__core_log_searches" );
		$db->query();
	}

	function getItems( )
	{
		global $mainframe, $option;
		$db	=& JFactory::getDBO();

		$filter_order	= $mainframe->getUserStateFromRequest( "com_ssearch.filter_order", 'filter_order', 	'hits' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( "com_search.filter_order_Dir", 'filter_order_Dir',	'' );
		$limit 		= $mainframe->getUserStateFromRequest( 'limit',	'limit', $mainframe->getCfg('list_limit') );
		$limitstart		= $mainframe->getUserStateFromRequest( "com_search.limitstart", 'limitstart',	0 );
		$search 		= $mainframe->getUserStateFromRequest( "com_search.search", 'search', '' );
		$search 		= $db->getEscaped( trim( JString::strtolower( $search ) ) );
		$where		= array();
		$showResults	= JRequest::getVar('search_results', 0);

		if ($search) {
			$where[] = "LOWER( search_term ) LIKE '%$search%'";
		}

		$where 		= ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );
		$orderby 	= "\n ORDER BY $filter_order $filter_order_Dir, hits DESC";

		// get the total number of records
		$query = "SELECT COUNT(*)"
		. "\n FROM #__core_log_searches"
		. $where;
		$db->setQuery( $query );
		$total = $db->loadResult();

		$query = "SELECT *"
		. "\n FROM #__core_log_searches"
		. $where
		. $orderby;
		$db->setQuery( $query, $pageNav->limitstart, $pageNav->limit );

		$rows = $db->loadObjectList();

		JPluginHelper::importPlugin( 'search' );

		for ($i=0, $n = count($rows); $i < $n; $i++) {
			// determine if number of results for search item should be calculated
			// by default it is `off` as it is highly query intensive
			if ( $showResults ) {
				$results = $mainframe->triggerEvent( 'onSearch', array( $rows[$i]->search_term ) );

				$count = 0;
				for ($j = 0, $n2 = count( $results ); $j < $n2; $j++) {
					$count += count( $results[$j] );
				}

				$rows[$i]->returns = $count;
			} else {
				$rows[$i]->returns = null;
			}
		}

		return $rows;
	}
}
?>