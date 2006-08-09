<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Users
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

// load the html view class
require_once( JApplicationHelper::getPath( 'front_html' ) );

// Get the task variable from the page request variables
$task = strtolower(JRequest::getVar('task'));

/*
 * This is our main control structure for the component
 *
 * Each view is determined by the $task variable
 */
switch ($task) {

	case 'login' :
		LoginController::login();
		break;
	case 'logout' :
		LoginController::logout();
		break;
	default :
		LoginController::showLogin();
		break;
}

/**
 * Static class to hold controller functions for the Login component
 *
 * @static
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla
 * @subpackage	Login
 * @since		1.5
 */

class LoginController
{
	function showLogin()
	{
		global $mainframe, $Itemid;

		// Initialize variables
		$user		=& JFactory::getUser();
		$menus		=& JMenu::getInstance();
		$menu		=& $menus->getItem( $Itemid );
		$params		=& $menus->getParams( $Itemid );
		$loginImage	= null;
		$logoutImage= null;

		// Set some default page parameters if not set
		$params->def( 'page_title', 				1 );
		$params->def( 'header_login', 				$menu->name );
		$params->def( 'header_logout', 				$menu->name );
		$params->def( 'pageclass_sfx', 				'' );
		$params->def( 'back_button', 				$mainframe->getCfg( 'back_button' ) );
		$params->def( 'login', 						'index.php' );
		$params->def( 'logout', 					'index.php' );
		$params->def( 'description_login', 			1 );
		$params->def( 'description_logout', 		1 );
		$params->def( 'description_login_text', 	JText::_( 'LOGIN_DESCRIPTION' ) );
		$params->def( 'description_logout_text',	JText::_( 'LOGOUT_DESCRIPTION' ) );
		$params->def( 'image_login', 				'key.jpg' );
		$params->def( 'image_logout', 				'key.jpg' );
		$params->def( 'image_login_align', 			'right' );
		$params->def( 'image_logout_align', 		'right' );
		$params->def( 'registration', 				$mainframe->getCfg( 'allowUserRegistration' ) );

		// Build login image if enabled
		if ( $params->get( 'image_login' ) != -1 ) {
			$image = 'images/stories/'. $params->get( 'image_login' );
			$loginImage = '<img src="'. $image  .'" align="'. $params->get( 'image_login_align' ) .'" hspace="10" alt="" />';
		}
		// Build logout image if enabled
		if ( $params->get( 'image_logout' ) != -1 ) {
			$image = 'images/stories/'. $params->get( 'image_logout' );
			$logoutImage = '<img src="'. $image .'" align="'. $params->get( 'image_logout_align' ) .'" hspace="10" alt="" />';
		}

		// Get some page variables
		$breadcrumbs = & $mainframe->getPathway();
		$document	 = & JFactory::getDocument();

		if ( $user->get('id') ) {
			$title = JText::_( 'Logout');

			// pathway item
			$breadcrumbs->setItemName(1, $title );
			// Set page title
			$document->setTitle( $title );

			LoginView::logout( $params, $logoutImage );
		} else {
			$title = JText::_( 'Login');

			// pathway item
			$breadcrumbs->setItemName(1, $title );
			// Set page title
			$document->setTitle( $title );

			LoginView::login( $params, $loginImage );
		}
	}

	function login()
	{
		global $mainframe;

		$username = JRequest::getVar( 'username' );
		$password = JRequest::getVar( 'password' );

		$error = $mainframe->login($username, $password);

		if(!JError::isError($error))
		{
			$return = JRequest::getVar( 'return' );

			/*
			 * checks for the presence of a return url and ensures that this url is not
			 * the registration or login pages
			 */
			if ( $return && !( strpos( $return, 'com_registration' ) || strpos( $return, 'com_login' ) ) ) {
				josRedirect( $return );
			}
		} else {
			LoginController::showLogin();
		}
	}

	function logout()
	{
		global $mainframe;

		$error = $mainframe->logout();

		if(!JError::isError($error))
		{
			$return = JRequest::getVar( 'return' );

			/*
			 * checks for the presence of a return url and ensures that this url is not
			 * the registration or login pages
			 */
			if ( $return && !( strpos( $return, 'com_registration' ) || strpos( $return, 'com_login' ) ) ) {
				josRedirect( $return );
			}
		} else {
			LoginController::showLogin();
		}
	}
}
?>