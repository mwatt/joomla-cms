<?php
/**
 * @version $Id: category.html.php 3393 2006-05-05 23:26:10Z Jinx $
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

jimport( 'joomla.application.view');

/**
 * HTML View class for the Content component
 *
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class JContentViewCategory extends JView
{
	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	var $_viewName = 'Category';

	/**
	 * Display the document
	 */
	function display()
	{
		$document	= &$this->getDocument();
		switch ($document->getType())
		{
			case 'feed':
				$this->displayFeed();
				break;
			default:
				$this->displayHtml();
				break;
		}
	}

	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	function displayHtml()
	{
		// Initialize some variables
		$app		= &$this->getApplication();
		$user		= &$app->getUser();
		$doc		= &$app->getDocument();
		$mParams	= &JComponentHelper::getMenuParams();
		$menus		= JMenu::getInstance();
		$menu		= &$menus->getCurrent();
		$Itemid		= $menu->id;
		$gid 		= $user->get('gid');

		// Model workaround
		$ctrl	= &$this->getController();
		$section = & $ctrl->getModel('category', 'JContentModel');
		$this->setModel($section, true);

		// Get some data from the model
		$category			= & $this->get( 'Category' );
		$other_categories	= & $this->get( 'Siblings' );
		$items				= & $this->get( 'Content' );

		// Request variables
		$task 	= JRequest::getVar('task');
		$id 	= JRequest::getVar('id');
		$option = JRequest::getVar('option');

		//add alternate feed link
		$link    = $app->getBaseURL() .'feed.php?option=com_content&task='.$task.'&id='.$id.'&Itemid='.$Itemid;
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$doc->addHeadLink($link.'&format=rss', 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$doc->addHeadLink($link.'&format=atom', 'alternate', 'rel', $attribs);

		// Create a user access object for the user
		$access					= new stdClass();
		$access->canEdit		= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		// Set the page title and breadcrumbs
		$breadcrumbs = & $app->getPathWay();
		// Section
		$breadcrumbs->addItem($category->sectiontitle, sefRelToAbs('index.php?option=com_content&amp;task=section&amp;id='.$category->sectionid.'&amp;Itemid='.$Itemid));
		// Category
		$breadcrumbs->addItem($category->title, '');

		$app->SetPageTitle($menu->name);

		// include the template
		$cParams = &JComponentHelper::getControlParams();
		$template = $cParams->get( 'template_name', 'table' );
		$template = preg_replace( '#\W#', '', $template );
		$tmplPath = dirname( __FILE__ ) . '/tmpl/' . $template . '.php';
		if (!file_exists( $tmplPath ))
		{
			$tmplPath = dirname( __FILE__ ) . '/tmpl/table.php';
		}

		require($tmplPath);
	}

	function buildItemTable(& $items, & $pagination, & $params, & $lists, & $access, $cid, $sid, $order)
	{
		$app		= & $this->getApplication();
		$user		= & $app->getUser();
		$menus		= JMenu::getInstance();
		$menu		= &$menus->getCurrent();
		$Itemid	= $menu->id;

		$link = 'index.php?option=com_content&amp;task=category&amp;sectionid='.$sid.'&amp;id='.$cid.'&amp;Itemid='.$Itemid;
		?>
		<script language="javascript" type="text/javascript">
		function tableOrdering( order, dir, task ) {
			var form = document.adminForm;

			form.filter_order.value 	= order;
			form.filter_order_Dir.value	= dir;
			document.adminForm.submit( task );
		}
		</script>

		<form action="<?php echo sefRelToAbs($link); ?>" method="post" name="adminForm">

		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<?php

		if ($params->get('filter') || $params->get('display'))
		{
		?>
			<tr>
				<td colspan="5">
					<table>
					<tr>
					<?php

			if ($params->get('filter'))
			{
		?>
						<td align="left" width="100%" nowrap="nowrap">
							<?php

				echo JText::_('Filter').'&nbsp;';
		?>
							<input type="text" name="filter" value="<?php echo $lists['filter'];?>" class="inputbox" onchange="document.adminForm.submit();" />
						</td>
					<?php

			}
			if ($params->get('display'))
			{
		?>
						<td align="right" width="100%" nowrap="nowrap">
							<?php

				$filter = '';
				if ($lists['filter'])
				{
					$filter = '&amp;filter='.$lists['filter'];
				}

				$link = 'index.php?option=com_content&amp;task=category&amp;sectionid='.$sid.'&amp;id='.$cid.'&amp;Itemid='.$Itemid.$filter;

				echo '&nbsp;&nbsp;&nbsp;'.JText::_('Display Num').'&nbsp;';
				echo $pagination->getLimitBox($link);
		?>
						</td>
						<?php

			}
		?>
					</tr>
					</table>
				</td>
			</tr>
			<?php

		}
		if ($params->get('headings'))
		{
		?>
			<tr>
				<td class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>" width="5">
					<?php echo JText::_('Num'); ?>
				</td>
				<?php

			if ($params->get('title'))
			{
		?>
					<td class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>" width="45%">
						<?php mosCommonHTML::tableOrdering( 'Item Title', 'a.title', $lists ); ?>
					</td>
					<?php

			}
			if ($params->get('date'))
			{
		?>
					<td class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>" width="25%">
						<?php mosCommonHTML::tableOrdering( 'Date', 'a.created', $lists ); ?>
					</td>
					<?php

			}
			if ($params->get('author'))
			{
		?>
					<td class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>"  width="20%">
						<?php mosCommonHTML::tableOrdering( 'Author', 'author', $lists ); ?>
					</td>
					<?php

			}
			if ($params->get('hits'))
			{
		?>
					<td align="center" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>" width="5%" nowrap="nowrap">
						<?php mosCommonHTML::tableOrdering( 'Hits', 'a.hits', $lists ); ?>
					</td>
					<?php

			}
		?>
			</tr>
			<?php

		}

		$k = 0;
		$i = 0;
		foreach ($items as $row)
		{
			$row->created = mosFormatDate($row->created, $params->get('date_format'));
		?>
			<tr class="sectiontableentry<?php echo ($k+1) . $params->get( 'pageclass_sfx' ); ?>" >
				<td align="center">
					<?php echo $pagination->rowNumber( $i ); ?>
				</td>
				<?php

			if ($params->get('title'))
			{
				if ($row->access <= $user->get('gid'))
				{
					$link = sefRelToAbs('index.php?option=com_content&amp;task=view&amp;id='.$row->id.'&amp;Itemid='.$Itemid);
		?>
						<td>
							<a href="<?php echo $link; ?>">
								<?php echo $row->title; ?></a>
							<?php

					JContentHTMLHelper::editIcon($row, $params, $access);
		?>
						</td>
						<?php

				}
				else
				{
		?>
						<td>
							<?php

					echo $row->title.' : ';
					$link = sefRelToAbs('index.php?option=com_registration&amp;task=register');
		?>
							<a href="<?php echo $link; ?>">
								<?php echo JText::_( 'Register to read more...' ); ?></a>
						</td>
					<?php

				}
			}
			if ($params->get('date'))
			{
		?>
					<td>
						<?php echo $row->created; ?>
					</td>
					<?php

			}
			if ($params->get('author'))
			{
		?>
					<td >
						<?php echo $row->created_by_alias ? $row->created_by_alias : $row->author; ?>
					</td>
					<?php

			}
			if ($params->get('hits'))
			{
		?>
					<td align="center">
						<?php echo $row->hits ? $row->hits : '-'; ?>
					</td>
					<?php

			}
		?>
			</tr>
			<?php

			$k = 1 - $k;
			$i ++;
		}
		if ($params->get('navigation'))
		{
		?>
			<tr>
				<td colspan="5">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" colspan="4" class="sectiontablefooter<?php echo $params->get( 'pageclass_sfx' ); ?>">
					<?php

			$filter = '';
			if ($lists['filter'])
			{
				$filter = '&amp;filter='.$lists['filter'];
			}

			$link = 'index.php?option=com_content&amp;task=category&amp;sectionid='.$sid.'&amp;id='.$cid.'&amp;Itemid='.$Itemid.$filter;
			echo $pagination->writePagesLinks($link);
		?>
				</td>
			</tr>
			<tr>
				<td colspan="5" align="right">
					<?php echo $pagination->writePagesCounter(); ?>
				</td>
			</tr>
			<?php

		}
		?>
		</table>

		<input type="hidden" name="id" value="<?php echo $cid; ?>" />
		<input type="hidden" name="sectionid" value="<?php echo $sid; ?>" />
		<input type="hidden" name="task" value="<?php echo $lists['task']; ?>" />
		<input type="hidden" name="option" value="com_content" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}

	function _buildSortLists()
	{
		/*
		 * Table ordering values
		 */
		$filter					= JRequest::getVar('filter');
		$filter_order		= JRequest::getVar('filter_order');
		$filter_order_Dir	= JRequest::getVar('filter_order_Dir');
		$lists['task'] = 'category';
		$lists['filter'] = $filter;
		if ($filter_order_Dir == 'DESC')
		{
			$lists['order_Dir'] = 'ASC';
		}
		else
		{
			$lists['order_Dir'] = 'DESC';
		}
		$lists['order'] = $filter_order;

		return $lists;
	}

	function showItem( &$row, &$access, $showImages = false )
	{
		require_once( JPATH_COM_CONTENT . '/helpers/article.php' );
		JContentArticleHelper::showItem( $this, $row, $access, $showImages );
	}

	function showLinks(& $rows, $links, $total, $i = 0)
	{
		require_once( JPATH_COM_CONTENT . '/helpers/article.php' );
		JContentArticleHelper::showLinks( $rows, $links, $total, $i );
	}

	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	function displayFeed()
	{
		$app =& $this->getApplication();
		$doc =& $app->getDocument();

		// Initialize some variables
		$menus		= JMenu::getInstance();
		$menu		= &$menus->getCurrent();
		$params		= &JComponentHelper::getMenuParams();
		$Itemid		= $menu->id;

		// Get some data from the model
		$rows = & $this->get( 'Content' );
		$limit		= '10';

		JRequest::setVar('limit', $limit);
		$category = & $this->get( 'Category' );
		$rows 	  = & $this->get( 'Content' );

		foreach ( $rows as $row )
		{
			// strip html from feed item title
			$title = htmlspecialchars( $row->title );
			$title = html_entity_decode( $title );

			// url link to article
			// & used instead of &amp; as this is converted by feed creator
			$itemid = $app->getItemid( $row->id );
			if ($itemid) {
				$_Itemid = '&Itemid='. $itemid;
			}

			$link = 'index.php?option=com_content&task=view&id='. $row->id . $_Itemid;
			$link = sefRelToAbs( $link );

			// strip html from feed item description text
			$description = $row->introtext;
			@$date = ( $row->created ? date( 'r', $row->created ) : '' );

			// load individual item creator class
			$item = new JFeedItem();
			$item->title 		= $title;
			$item->link 		= $link;
			$item->description 	= $description;
			$item->date			= $date;
			$item->category   	= $category->title;

			// loads item info into rss array
			$doc->addItem( $item );
		}
	}
}
?>