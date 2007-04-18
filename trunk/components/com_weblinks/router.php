<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

function WeblinksBuildRoute(&$query)
{
	$segments = array();

	if(isset($query['catid']))
	{
		$segments[] = $query['catid'];
		unset($query['catid']);
	};

	if(isset($query['id']))
	{
		$segments[] = $query['id'];
		unset($query['id']);
	};

	unset($query['view']);

	return $segments;
}

function WeblinksParseRoute($segments)
{
	$vars = array();
	
	//Get the active menu item
	$menu =& JMenu::getInstance();
	$item =& $menu->getActive();

	// Count route segments
	$count = count($segments);

	//Handle View and Identifier
	switch($item->query['view'])
	{
		case 'categories' :
		{
			if($count == 1) {
				$vars['view'] = 'category';
			}

			if($count == 2) {
				$vars['view'] = 'weblink';
			}

			$vars['id'] = $segments[$count-1];

		} break;

		case 'category'   :
		{
			$vars['id']   = $segments[$count-1];
			$vars['view'] = 'weblink';

		} break;
	}
	
	return $vars;
}
?>