<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Banners
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

require_once( JApplicationHelper::getPath( 'toolbar_html' ) );

switch ($task)
{
	case 'newclient'  :
	case 'editclient' :
	case 'editclientA':
		TOOLBAR_bannerClient::_EDIT();
		break;

	case 'listclients':
		TOOLBAR_bannerClient::_DEFAULT();
		break;

	case 'add':
	case 'edit':
	case 'editA':
		TOOLBAR_banners::_EDIT();
		break;

	default:
		TOOLBAR_banners::_DEFAULT();
		break;
}
?>