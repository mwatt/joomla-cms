<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Content component
 *
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class ContentViewCategory extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $option, $Itemid;

		// Initialize some variables
		$user	  =& JFactory::getUser();
		$document =& JFactory::getDocument();
		$pathway  = & $mainframe->getPathWay();

		// Get the menu object of the active menu item
		$menu    =& JSiteHelper::getCurrentMenuItem();
		$params  =& JSiteHelper::getMenuParams();

		// Request variables
		$task 	    = JRequest::getVar('task');
		$limit		= JRequest::getVar('limit', $params->get('display_num'), '', 'int');
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		// Get some data from the model
		$items	  = & $this->get( 'Content' );
		$category = & $this->get( 'Category' );
		$category->total = count($items);

		//add alternate feed link
		$link    = JURI::base() .'feed.php?option=com_content&task='.$task.'&id='.$category->id.'&Itemid='.$Itemid;
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink($link.'&format=rss', 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink($link.'&format=atom', 'alternate', 'rel', $attribs);

		// Create a user access object for the user
		$access					= new stdClass();
		$access->canEdit		= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		// Section
		$pathway->addItem($category->sectiontitle, sefRelToAbs('index.php?option=com_content&amp;task=section&amp;id='.$category->sectionid.'&amp;Itemid='.$Itemid));
		// Category
		$pathway->addItem($category->title, '');

		$mainframe->setPageTitle($menu->name);

		$intro		= $params->def('intro', 	4);
		$leading	= $params->def('leading', 	1);
		$links		= $params->def('link', 		4);

		$params->def('title',			1);
		$params->def('hits',			$mainframe->getCfg('hits'));
		$params->def('author',			!$mainframe->getCfg('hideAuthor'));
		$params->def('date',			!$mainframe->getCfg('hideCreateDate'));
		$params->def('date_format',		JText::_('DATE_FORMAT_LC'));
		$params->def('navigation',		2);
		$params->def('display',			1);
		$params->def('display_num',		$mainframe->getCfg('list_limit'));
		$params->def('empty_cat',		0);
		$params->def('cat_items',		1);
		$params->def('cat_description',0);
		$params->def('pageclass_sfx',	'');
		$params->def('headings',		1);
		$params->def('filter',			1);
		$params->def('filter_type',		'title');
		$params->set('intro_only', 		1);

		if ($params->def('page_title', 1)) {
			$params->def('header', $menu->name);
		}

		$limit	= $intro + $leading + $links;
		$i		= $limitstart;

		jimport('joomla.presentation.pagination');
		$pagination = new JPagination(count($items), $limitstart, $limit);
		$link = 'index.php?option=com_content&amp;task=category&amp;sectionid='.$category->sectionid.'&amp;id='.$category->id.'&amp;Itemid='.$Itemid;

		$this->assign('limit'	  , $limit);
		$this->assign('limitstart',	$limitstart);
		$this->assign('link'	  , $link);

		$this->assignRef('items'     , $items);
		$this->assignRef('category'  , $category);
		$this->assignRef('params'    , $params);
		$this->assignRef('user'      , $user);
		$this->assignRef('access'    , $access);
		$this->assignRef('pagination', $pagination);

		parent::display($tpl);
	}

	function getIcon(&$article, $type, $attribs = array())
	{
		global $Itemid, $mainframe;

		$url  = '';
		$text = '';

		switch($type)
		{
			case 'new' :
			{
				$url = 'index.php?option=com_content&amp;task=new&amp;sectionid='.$article->sectionid.'&amp;Itemid='.$Itemid;

				if ($this->params->get('icons')) {
					$text = mosAdminMenus::ImageCheck('new.png', '/images/M_images/', NULL, NULL, JText::_('New'), JText::_('New'). $article->id );
				} else {
					$text = JText::_('New').'&nbsp;';
				}

				$attribs['title']   = '"'.JText::_( 'New' ).'"';

			} break;

			case 'edit' :
			{
				if ($this->params->get('popup')) {
					return;
				}
				if ($article->state < 0) {
					return;
				}
				if (!$this->access->canEdit && !($this->access->canEditOwn && $article->created_by == $this->user->get('id'))) {
					return;
				}

				mosCommonHTML::loadOverlib();

				$url = 'index.php?option=com_content&amp;task=edit&amp;id='.$article->id.'&amp;Itemid='.$Itemid.'&amp;Returnid='.$Itemid;
				$text = mosAdminMenus::ImageCheck('edit.png', '/images/M_images/', NULL, NULL, JText::_('Edit'), JText::_('Edit'). $article->id );

				if ($article->state == 0) {
					$overlib = JText::_('Unpublished');
				} else {
					$overlib = JText::_('Published');
				}
				$date = mosHTML::Date($article->created);
				$author = $article->created_by_alias ? $article->created_by_alias : $article->author;

				$overlib .= '<br />';
				$overlib .= $article->groups;
				$overlib .= '<br />';
				$overlib .= $date;
				$overlib .= '<br />';
				$overlib .= $author;

				$attribs['onmouseover'] = "\"return overlib('".$overlib."', CAPTION, '".JText::_( 'Edit Item' )."', BELOW, RIGHT)\"";
				$attribs['onmouseover'] = "\"return nd();\"";

			} break;

			case 'pdf' :
			{
				$url   = 'index2.php?option=com_content&amp;view=article&amp;id='.$article->id.'&amp;format=pdf';
				$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

				// checks template image directory for image, if non found default are loaded
				if ($this->params->get('icons')) {
					$text = mosAdminMenus::ImageCheck('pdf_button.png', '/images/M_images/', NULL, NULL, JText::_('PDF'), JText::_('PDF'));
				} else {
					$text = JText::_('PDF').'&nbsp;';
				}

				$attribs['title']   = '"'.JText::_( 'PDF' ).'"';
				$attribs['onclick'] = "\"window.open('".$url."','win2','".$status."'); return false;\"";

			} break;

			case 'print' :
			{
				$url    = 'index2.php?option=com_content&amp;task=view&amp;id='.$article->id.'&amp;Itemid='.$Itemid.'&amp;pop=1&amp;page='.@ $this->request->limitstart;
				$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

				// checks template image directory for image, if non found default are loaded
				if ( $this->params->get( 'icons' ) ) {
					$text = mosAdminMenus::ImageCheck( 'printButton.png', '/images/M_images/', NULL, NULL, JText::_( 'Print' ), JText::_( 'Print' ) );
				} else {
					$text = JText::_( 'ICON_SEP' ) .'&nbsp;'. JText::_( 'Print' ) .'&nbsp;'. JText::_( 'ICON_SEP' );
				}

				$attribs['title']   = '"'.JText::_( 'Print' ).'"';
				$attribs['onclick'] = "\"window.open('".$url."','win2','".$status."'); return false;\"";

			} break;

			case 'email' :
			{
				$url   = 'index2.php?option=com_mailto&amp;link='.urlencode( JRequest::getURI());
				$status = 'width=400,height=300,menubar=yes,resizable=yes';

				if ($this->params->get('icons')) 	{
					$text = mosAdminMenus::ImageCheck('emailButton.png', '/images/M_images/', NULL, NULL, JText::_('Email'), JText::_('Email'));
				} else {
					$text = '&nbsp;'.JText::_('Email');
				}
				
				$attribs['title']   = '"'.JText::_( 'Email ' ).'"';
				$attribs['onclick'] = "\"window.open('".$url."','win2','".$status."'); return false;\"";
				
			} break;
		}

		return mosHTML::Link($url, $text, $attribs);
	}

	function &getItems()
	{
		global $mainframe, $Itemid;

		if (!count( $this->items ) ) {
			return;
		}

		//create select lists
		$lists	= $this->_buildSortLists();

		//create paginatiion
		if ($lists['filter']) {
			$this->data->link .= '&amp;filter='.$lists['filter'];
		}

		$k = 0;
		for($i = 0; $i <  count($this->items); $i++)
		{
			$item =& $this->items[$i];

			$item->link    = sefRelToAbs('index.php?option=com_content&amp;task=view&amp;id='.$item->id.'&amp;Itemid='.$Itemid);
			$item->created = mosHTML::Date($item->created, $this->params->get('date_format'));

			$item->odd   = $k;
			$item->count = $i;
			$k = 1 - $k;
		}

		$this->assign('lists'     , $lists);

		return $this->items;
	}

	function &getItem($index = 0, $params)
	{
		global $mainframe, $Itemid;

		// Initialize some variables
		$user		=& JFactory::getUser();
		$dispatcher	=& JEventDispatcher::getInstance();

		$SiteName	= $mainframe->getCfg('sitename');

		$task		= JRequest::getVar( 'task' );

		$linkOn		= null;
		$linkText	= null;

		// Get some parameters from global configuration
		$params->def('link_titles',	$mainframe->getCfg('link_titles'));
		$params->def('author',		!$mainframe->getCfg('hideAuthor'));
		$params->def('createdate',	!$mainframe->getCfg('hideCreateDate'));
		$params->def('modifydate',	!$mainframe->getCfg('hideModifyDate'));
		$params->def('print',		!$mainframe->getCfg('hidePrint'));
		$params->def('pdf',			!$mainframe->getCfg('hidePdf'));
		$params->def('email',		!$mainframe->getCfg('hideEmail'));
		$params->def('rating',		$mainframe->getCfg('vote'));
		$params->def('icons',		$mainframe->getCfg('icons'));
		$params->def('readmore',	$mainframe->getCfg('readmore'));
		$params->def('back_button', $mainframe->getCfg('back_button'));

		// Get some item specific parameters
		$params->def('image',			1);
		$params->def('section',			0);
		$params->def('section_link',	0);
		$params->def('category',		0);
		$params->def('category_link',	0);
		$params->def('introtext',		1);
		$params->def('pageclass_sfx',	'');
		$params->def('item_title',		1);
		$params->def('url',				1);
		$params->set('image',			1);

		$item =& $this->items[$index];

		// Process the content preparation plugins
		$item->text	= ampReplace($item->introtext);
		JPluginHelper::importPlugin('content');
		$results = $dispatcher->trigger('onPrepareContent', array (& $item, & $params, 0));

		// Build the link and text of the readmore button
		if (($params->get('readmore') && @ $item->readmore) || $params->get('link_titles'))
		{
			if ($params->get('intro_only'))
			{
				// checks if the item is a public or registered/special item
				if ($item->access <= $user->get('gid'))
				{
					$linkOn = sefRelToAbs("index.php?option=com_content&amp;task=view&amp;id=".$item->id."&amp;Itemid=".$Itemid);
					$linkText = JText::_('Read more...');
				}
				else
				{
					$linkOn = sefRelToAbs("index.php?option=com_registration&amp;task=register");
					$linkText = JText::_('Register to read more...');
				}
			}
		}

		$item->readmore_link = $linkOn;
		$item->readmore_text = $linkText;

		$item->print_link = $mainframe->getCfg('live_site').'/index2.php?option=com_content&amp;task=view&amp;id='.$this->item->id.'&amp;Itemid='.$Itemid.'&amp;pop=1';

		$item->event = new stdClass();
		$results = $dispatcher->trigger('onAfterDisplayTitle', array (& $item, & $params,0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onBeforeDisplayContent', array (& $item, & $params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onAfterDisplayContent', array (& $item, & $params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		return $item;
	}
	
	function _buildSortLists()
	{
		// Table ordering values
		$filter				= JRequest::getVar('filter');
		$filter_order		= JRequest::getVar('filter_order');
		$filter_order_Dir	= JRequest::getVar('filter_order_Dir');
		
		$lists['task'] = 'category';
		$lists['filter'] = $filter;
		
		if ($filter_order_Dir == 'DESC') {
			$lists['order_Dir'] = 'ASC';
		} else {
			$lists['order_Dir'] = 'DESC';
		}
		
		$lists['order'] = $filter_order;

		return $lists;
	}
}
?>