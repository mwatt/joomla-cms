<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Weblinks
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Load the html output class and the model class
 */
require_once (JApplicationHelper::getPath('front_html'));
require_once (JApplicationHelper::getPath('class'));

// First thing we want to do is set the page title
$mainframe->setPageTitle(JText::_('Web Links'));

// Next, let's get the breadcrumbs object so that we can manipulate it
$breadcrumbs =& $mainframe->getPathWay();

// Now that we have the breadcrumb object, let's set the component name in the pathway
$breadcrumbs->setItemName(1, JText::_('Web Links'));

// Get some common variables from the $_REQUEST global
$id    = intval(mosGetParam($_REQUEST, 'id', 0));
$catid = intval(mosGetParam($_REQUEST, 'catid', 0));
$task  = mosGetParam($_REQUEST, 'task', '');

/*
 * This is our main control structure for the component
 *
 * Each view is determined by the $task variable
 */
switch ($task) {
	case 'new' :
		WeblinksController::editWebLink(0);
		break;

	case 'edit' :
		/*
		 * Disabled until ACL system is implemented.  When enabled the $id variable
		 * will be passed instead of a 0
		 */
		WeblinksController::editWebLink(0);
		break;

	case 'save' :
		WeblinksController::saveWebLink();
		break;

	case 'cancel' :
		WeblinksController::cancelWebLink();
		break;

	case 'view' :
		WeblinksController::showItem($id, $catid);
		break;

	default :
		WeblinksController::showCategory($catid);
		break;
}
/**
 * Static class to hold controller functions for the Weblink component
 *
 * @static
 * @package Joomla
 * @subpackage Weblinks
 * @since 1.1
 */
class WeblinksController {

	/**
	 * Show a web link category
	 *
	 * @param int $catid Web Link category id
	 * @since 1.0
	 */
	function showCategory($catid) {
		global $mainframe, $Itemid;

		// Get some objects from the JApplication
		$db = & $mainframe->getDBO();
		$my = & $mainframe->getUser();
		$breadcrumbs = & $mainframe->getPathWay();

		/*
		 * Query to retrieve all categories that belong under the web links section
		 * and that are published.
		 */
		$query = 	"SELECT *, COUNT(a.id) AS numlinks FROM #__categories AS cc".
					"\n LEFT JOIN #__weblinks AS a ON a.catid = cc.id".
					"\n WHERE a.published = 1".
					//"\n AND a.approved = 1".
					"\n AND section = 'com_weblinks'".
					"\n AND cc.published = 1".
					"\n AND cc.access <= $my->gid".
					"\n GROUP BY cc.id".
					"\n ORDER BY cc.ordering";

		$db->setQuery($query);
		$categories = $db->loadObjectList();

		if ($catid) {

			// Initialize variables
			$rows = array ();

			/*
			 * We need to get a list of all weblinks in the given category
			 */
			$query = 	"SELECT id, url, title, description, date, hits, params".
						"\n FROM #__weblinks".
						"\n WHERE catid = $catid".
						"\n AND published = 1".
						//"\n AND approved = 1".
						"\n AND archived = 0".
						"\n ORDER BY ordering";

			$db->setQuery($query);
			$rows = $db->loadObjectList();

			/*
			 * Let's load a category model for the current category
			 */
			$category =& JModel::getInstance('category', $db );

			if (!$category->load($catid)) {
				JError::raiseError('SOME_ERROR_CODE', 'WeblinksController::showCategory: Unable to load the category', 'Category ID: '.$catid);
			}

		}

		// Load Parameters
		$menu =& JModel::getInstance('menu', $db );
		$menu->load($Itemid);
		
		$params = new JParameters($menu->params);
		$params->def('page_title', 1);
		$params->def('header', $menu->name);
		$params->def('pageclass_sfx', '');
		$params->def('headings', 1);
		$params->def('hits', $mainframe->getCfg('hits'));
		$params->def('item_description', 1);
		$params->def('other_cat_section', 1);
		$params->def('other_cat', 1);
		$params->def('description', 1);
		$params->def('description_text', JText::_('WEBLINKS_DESC'));
		$params->def('image', '-1');
		$params->def('weblink_icons', '');
		$params->def('image_align', 'right');
		$params->def('back_button', $mainframe->getCfg('back_button'));

		if (!$catid) {
			/*
			 * If we are at the WebLink component root (no category id set) certain
			 * defaults need to be set based on parameter values.
			 */

			// Handle the type
			$params->set('type', 'section');

			// Handle the page description
			if (empty ($category->description)) {
				if ($params->get('description')) {
					$category->description = $params->get('description_text');
				}
			}

			// Handle the page image
			if (empty ($category->image)) {
				// The description field of the category is empty
				if ($params->get('image') != -1) {
					$category->image = 'images/stories/'.$params->get('image');
					$category->image_position = $params->get('image_align');
					
					// Define image tag attributes
					$imgAttribs['align'] = $category->image_position;
					$imgAttribs['hspace'] = '6';
					
					// Use the static HTML library to build the image tag
					$category->imgTag = mosHTML::Image($category->image, JText::_('Web Links'), $imgAttribs);
				}
			}
		} else {
			/*
			 * If a category id is set (in a category) we don't need to set most values
			 * because they are defined in the category model from the database tuple.
			 */

			// Handle the type
			$params->set('type', 'category');
		}

		// Handle page header, page title, and breadcrumbs
		if (empty ($category->name)) {
			/*
			 * We do not have a name set for the category, so we should get the default
			 * information from the parameters.
			 */
			$category->name = $params->get('header');

			// Set page title
			$mainframe->SetPageTitle($menu->name);
		} else {
			/*
			 * A name is set for the current category so let's use it.
			 */

			// Set page title based on category name
			$mainframe->setPageTitle($menu->name.' - '.$category->name);

			// Add breadcrumbs item based on category name
			$breadcrumbs->addItem($category->name, '');
		}

		// used to show table rows in alternating colours
		$tabclass = array ('sectiontableentry1', 'sectiontableentry2');

		WeblinksView::showCategory($categories, $rows, $catid, $category, $params, $tabclass);
	}

	/**
	 * Log the hit and redirect to the link
	 *
	 * @param int $id Web Link id
	 * @param int $catid Web Link category id
	 * @since 1.0
	 */
	function showItem($id, $catid) {
		global $mainframe, $Itemid;

		// Get some objects from the JApplication
		$db = & $mainframe->getDBO();

		$weblink = & new JWeblinkModel($db);
		$weblink->load($id);

		// Record the hit
		$weblink->hit();

		if ( $weblink->url ) {
			// redirects to url if matching id found
			mosRedirect($weblink->url);
		} else {		
			// redirects to weblink category page if no matching id found
			WeblinksController::showCategory($catid);
		}
	}

	/**
	 * Edit a web link record
	 *
	 * @param int $id Web Link id to edit
	 * @since 1.0
	 */
	function editWebLink($id) {
		global $mainframe, $Itemid;

		// Get some objects from the JApplication
		$db = & $mainframe->getDBO();
		$my = & $mainframe->getUser();
		$breadcrumbs = & $mainframe->getPathWay();

		// Make sure you are logged in
		if ($my->gid < 1) {
			mosNotAuth();
			return;
		}

		// Create and load a weblink model
		$row = new JWebLinkModel($db);
		$row->load($id);

		// Is this link checked out?  If not by me fail
		if ($row->isCheckedOut($my->id)) {
			mosRedirect("index2.php?option=$option", 'The module $row->title is currently being edited by another administrator.');
		}

		// Edit or Create?
		if ($id) {
			/*
			 * The web link already exists so we are editing it.  Here we want to
			 * manipulate the pathway and pagetitle to indicate this, plus we want
			 * to check the web link out so no one can edit it while we are editing it
			 */
			$row->checkout($my->id);

			// Set page title
			$mainframe->setPageTitle( JText::_('Web Links').' - '.JText::_( 'Edit' ));

			// Add breadcrumbs item
			$breadcrumbs->addItem(JText::_( 'Edit' ), '');
		} else {
			/*
			 * The web link does not already exist so we are creating a new one.  Here
			 * we want to manipulate the pathway and pagetitle to indicate this.  Also,
			 * we need to initialize some values.
			 */
			$row->published = 0;
			$row->approved = 1;
			$row->ordering = 0;

			// Set page title
			$mainframe->setPageTitle( JText::_('Web Links').' - '.JText::_( 'New' ));

			// Add pathway item
			$breadcrumbs->addItem(JText::_( 'New' ), '');
		}

		/*
			// make the select list for the image positions
			$yesno[] = mosHTML::makeOption( '0', 'No' );
			$yesno[] = mosHTML::makeOption( '1', 'Yes' );
			// build the html select list
			$applist = mosHTML::selectList( $yesno, 'approved', 'class="inputbox" size="2"', 'value', 'text', $row->approved );
			// build the html select list for ordering
			$query = "SELECT ordering AS value, title AS text"
			. "\n FROM #__weblinks"
			. "\n WHERE catid='$row->catid'"
			. "\n ORDER BY ordering"
			;
			$lists['ordering'] 			= mosAdminMenus::SpecificOrdering( $row, $id, $query, 1 );
		*/

		// build list of categories
		$lists['catid'] = mosAdminMenus::ComponentCategory('catid', $mainframe->getOption(), intval($row->catid));

		WeblinksView::editWeblink($row, $lists);
	}

	/**
	 * Cancel the editing of a web link
	 *
	 * @since 1.0
	 */
	function cancelWebLink() {
		global $mainframe, $Itemid;

		// Get some objects from the JApplication
		$db = & $mainframe->getDBO();
		$my = & $mainframe->getUser();

		// Must be logged in
		if ($my->gid < 1) {
			mosNotAuth();
			return;
		}

		// Create and load a web link model
		$row = new JWeblinkModel($db);
		$row->load(intval(mosGetParam($_POST, 'id', 0)));

		// Checkin the weblink
		$row->checkin();

		// Get some standard variables and redirect
		$Itemid = mosGetParam($_POST, 'Returnid', '');
		$referer = mosGetParam($_POST, 'referer', '');
		mosRedirect($referer);
	}

	/**
	 * Saves the record on an edit form submit
	 *
	 * @since 1.0
	 */
	function saveWeblink() {
		global $mainframe, $Itemid;

		// Get some objects from the JApplication
		$db = & $mainframe->getDBO();
		$my = & $mainframe->getUser();

		// Must be logged in
		if ($my->gid < 1) {
			mosNotAuth();
			return;
		}

		// Create a web link model
		$row = new JWeblinkModel($db);

		// Bind the $_POST array to the web link model
		if (!$row->bind($_POST, "published")) {
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit ();
		}

		// Is the web link a new one?
		$isNew = $row->id < 1;

		// Create the timestamp for the date
		$row->date = date('Y-m-d H:i:s');
	
		// until full edit capabilities are given for weblinks - limit saving to new weblinks only
		$row->id = 0;
		
		// Make sure the web link model is valid
		if (!$row->check()) {
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit ();
		}

		// Store the web link model to the database
		if (!$row->store()) {
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit ();
		}

		// Check the model in so it can be edited.... we are done with it anyway
		$row->checkin();

		/*
				// Send notification emails to the administrators
				$query = "SELECT email, name"
				. "\n FROM #__users"
				. "\n WHERE gid = 25"
				. "\n AND sendemail = 1"
				;
				$database->setQuery( $query );
				if(!$database->query()) {
					echo $database->stderr( true );
					return;
				}

				$adminRows = $database->loadObjectList();
				foreach( $adminRows as $adminRow) {
					mosSendAdminMail($adminRow->name, $adminRow->email, "", "Weblink", $row->title, $my->username );
				}
		*/

		$msg = $isNew ? JText::_('THANK_SUB') : '';
		mosRedirect('index.php', $msg);
	}
}
?>