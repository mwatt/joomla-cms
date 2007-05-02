<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Content component
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ContentViewCategory extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $option;

		// Initialize some variables
		$user		=& JFactory::getUser();
		$document	=& JFactory::getDocument();
		$uri 		=& JFactory::getURI();
		$pathway	= & $mainframe->getPathWay();

		// Get the menu item object
		$menus = &JMenu::getInstance();
		$menu  = $menus->getActive();

		// Get the page/component configuration
		$params = &$mainframe->getPageParameters('com_content');

		// Request variables
		$task 		= JRequest::getVar('task');
		$limit       = $mainframe->getUserStateFromRequest('com_content.limit', 'limit', $params->def('display_num', 0));
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		// parameters
		$intro		= $params->def('num_intro_articles', 	0);
		$leading	= $params->def('num_leading_articles', 	0);
		$links		= $params->def('num_links', 			0);

		//In case we are in a blog view set the limit
		if($limit ==  0) $limit = $intro + $leading + $links;
		JRequest::setVar('limit', $limit);

		// Get some data from the model
		$items		= & $this->get( 'Data' );
		$total		= & $this->get( 'Total' );
		$category	= & $this->get( 'Category' );

		//add alternate feed link
		$link	= 'index.php?option=com_content&view=category&format=feed&id='.$category->id;
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);

		// Create a user access object for the user
		$access					= new stdClass();
		$access->canEdit		= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		//set breadcrumbs
		if($menu->query['view'] != 'category') {
			$pathway->addItem($category->title, '');
		}

		$mainframe->setPageTitle($menu->name);

		$params->def('date_format',	JText::_('DATE_FORMAT_LC'));
		$params->def('page_title', $menu->name);

		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);

		$this->assign('total',		$total);

		$this->assignRef('items',		$items);
		$this->assignRef('category',	$category);
		$this->assignRef('params',		$params);
		$this->assignRef('user',		$user);
		$this->assignRef('access',		$access);
		$this->assignRef('pagination',	$pagination);

		//Load the html helper for the view
		$this->loadHelper('html');

		parent::display($tpl);
	}

	function &getItems()
	{
		global $mainframe;

		if (!count( $this->items ) ) {
			$return = array();
			return $return;
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

			$item->link		= JRoute::_('index.php?view=article&catid='.$this->category->slug.'&id='.$item->slug);
			$item->created	= JHTML::Date($item->created, $this->params->get('date_format'));

			$item->odd		= $k;
			$item->count	= $i;
			$k = 1 - $k;
		}

		$this->assign('lists',	$lists);

		return $this->items;
	}

	function &getItem($index = 0, $aparams)
	{
		global $mainframe;

		// Initialize some variables
		$user		=& JFactory::getUser();
		$dispatcher	=& JEventDispatcher::getInstance();

		$SiteName	= $mainframe->getCfg('sitename');

		$task		= JRequest::getVar( 'task' );

		$linkOn		= null;
		$linkText	= null;

		// Get the page/component configuration
		$params = clone($mainframe->getPageParameters('com_content'));

		// Merge article parameters into the page configuration
		$params->merge($aparams);

		$item 		=& $this->items[$index];
		$item->text = $item->introtext;

		// Process the content preparation plugins
		JPluginHelper::importPlugin('content');
		$results = $dispatcher->trigger('onPrepareContent', array (& $item, & $params, 0));

		// Build the link and text of the readmore button
		if (($params->get('show_readmore') && @ $item->readmore) || $params->get('link_titles'))
		{
			// checks if the item is a public or registered/special item
			if ($item->access <= $user->get('aid', 0))
			{
				$linkOn = JRoute::_('index.php?view=article&catid='.$this->category->slug.'&id='.$item->slug);
				$linkText = JText::_('Read more...');
			}
			else
			{
				$linkOn = JRoute::_("index.php?option=com_user&task=register");
				$linkText = JText::_('Register to read more...');
			}
		}

		$item->readmore_link = $linkOn;
		$item->readmore_text = $linkText;

		$item->print_link = $mainframe->getCfg('live_site').'/index.php?option=com_content&view=article&id='.$item->slug.'&tmpl=component';

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