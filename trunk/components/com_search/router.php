<?php
/**
* @version		$Id: request.php 5850 2006-11-25 19:21:42Z Jinx $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

function SearchBuildRoute(&$query)
{
	$segments = array();
	if(isset($query['searchword'])) {
		$segments[] = $query['searchword'];
		unset($query['searchword']);
	}

	return $segments;
}

function SearchParseRoute($segments)
{
	global $mainframe;
	
	//Get the router
	$router =& $mainframe->getRouter();
	
	$searchword	= array_shift($segments);

	$router->setVar('searchword', $searchword);
}
?>