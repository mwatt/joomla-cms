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

class modArchiveHelper
{
	function getList(&$params)
	{
		//get database
		$db =& JFactory::getDBO();

		$query = "SELECT MONTH( created ) AS created_month, created, id, sectionid, title, YEAR(created) AS created_year" .
			"\n FROM #__content" .
			"\n WHERE ( state = -1 AND checked_out = 0 AND sectionid > 0 )" .
			"\n GROUP BY created_year DESC, created_month DESC";
		$db->setQuery($query, 0, intval($params->get('count')));
		$rows = $db->loadObjectList();

		$i 	   = 0;
		$lists = array();
		foreach ( $rows as $row ) {
			$created_month	= mosFormatDate($row->created, "%m");
			$month_name		= mosFormatDate($row->created, "%B");
			$created_year	= mosFormatDate($row->created, "%Y");

			$lists[$i]->link	= sefRelToAbs('index.php?option=com_content&amp;task=archivecategory&amp;year='.$created_year.'&amp;month='.$created_month.'&amp;module=1');
			$lists[$i]->text	= $month_name.', '.$created_year;
			$i++;
		}
		return $lists;
	}
}