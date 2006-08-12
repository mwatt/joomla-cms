<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Cache
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
$user =& JFactory::getUser();
if (!$user->authorize( 'com_cache', 'manage' )) {
	$mainframe->redirect( 'index.php?', JText::_('ALERTNOTAUTH') );
}

// Load the html output class and the model class
require_once (JApplicationHelper::getPath('admin_html'));
require_once (JApplicationHelper::getPath('class'));

$cid = JRequest::getVar( 'eid', array(0), 'post', 'array' );

/*
 * This is our main control structure for the component
 *
 * Each view is determined by the $task variable
 */
switch ( JRequest::getVar( 'task' ) )
{
	case 'delete':
		CacheController::deleteCache($cid);
		CacheController::showCache();
		break;

	default :
		CacheController::showCache();
		break;
}

/**
 * Static class to hold controller functions for the Cache component
 *
 * @static
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla
 * @subpackage	Weblinks
 * @since		1.5
 */
class CacheController
{
	/**
	 * Show the cache
	 *
	 * @since	1.5
	 */
	function showCache()
	{
		global $mainframe, $option;

		$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

		$limit 			= $mainframe->getUserStateFromRequest( "limit"							 , 'limit'		, $mainframe->getCfg('list_limit') );
		$limitstart 	= $mainframe->getUserStateFromRequest( "$option.view.limitstart", 'limitstart'	, 0 );

		$cmData = new CacheData($client->path.DS.'cache');

		jimport('joomla.presentation.pagination');
		$pageNav = new JPagination( $cmData->getGroupCount(), $limitstart, $limit );

		CacheView::displayCache( $cmData->getRows( $limitstart, $limit ), $client, $pageNav );
	}

	function deleteCache($cid)
	{
		$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

		$cmData = new CacheData($client->path.DS.'cache');
		$cmData->cleanCacheList( $cid );
	}
}