<?php
/**
 * @version $Id: article.html.php 3545 2006-05-18 00:25:36Z Jinx $
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.view');

/**
 * HTML Article Element View class for the Content component
 *
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class JContentViewElement extends JView
{
	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	var $_viewName = 'Element';

	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	function display()
	{
		// Initialize variables
		$app		= &$this->get('Application');
		$url 		= $app->isAdmin() ? $app->getSiteURL() : $app->getBaseURL();
		$db			= &$app->getDBO();
		$nullDate	= $db->getNullDate();

		$document	= &$this->getDocument();
		$document->setTitle('Article Selection');
		$document->addScript($url.'includes/js/joomla/popup.js');
		$document->addStyleSheet($url.'includes/js/joomla/popup.css');

		$limitstart = JRequest::getVar('limitstart', '0', '', 'int');

		$lists = $this->_getLists();

		//Ordering allowed ?
		$ordering = ($lists['order'] == 'section_name' && $lists['order_Dir'] == 'ASC');

		$rows = &$this->get('List');
		$page = &$this->get('Pagination');
		mosCommonHTML::loadOverlib();
		?>
		<form action="index.php?option=com_content&amp;task=element&amp;tmpl=component.html" method="post" name="adminForm">

			<table>
				<tr>
					<td width="100%">
						<?php echo JText::_( 'Filter' ); ?>:
						<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
						<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
						<button onclick="getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
					</td>
					<td nowrap="nowrap">
						<?php
						echo $lists['sectionid'];
						echo $lists['catid'];
						?>
					</td>
				</tr>
			</table>

			<table class="adminlist" cellspacing="1">
			<thead>
				<tr>
					<th width="5">
						<?php echo JText::_( 'Num' ); ?>
					</th>
					<th class="title">
						<?php mosCommonHTML::tableOrdering( 'Title', 'c.title', $lists ); ?>
					</th>
					<th width="7%">
						<?php mosCommonHTML::tableOrdering( 'Access', 'groupname', $lists ); ?>
					</th>
					<th width="2%" class="title">
						<?php mosCommonHTML::tableOrdering( 'ID', 'c.id', $lists ); ?>
					</th>
					<th class="title" width="8%" nowrap="nowrap">
						<?php mosCommonHTML::tableOrdering( 'Section', 'section_name', $lists ); ?>
					</th>
					<th  class="title" width="8%" nowrap="nowrap">
						<?php mosCommonHTML::tableOrdering( 'Category', 'cc.name', $lists ); ?>
					</th>
					<th align="center" width="10">
						<?php mosCommonHTML::tableOrdering( 'Date', 'c.created', $lists ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<td colspan="15">
					<?php echo $page->getListFooter(); ?>
				</td>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			for ($i=0, $n=count( $rows ); $i < $n; $i++)
			{
				$row = &$rows[$i];

				$link 	= '';
				$date	= mosFormatDate( $row->created, JText::_( 'DATE_FORMAT_LC4' ) );
				$access	= mosCommonHTML::AccessProcessing( $row, $i, $row->state );
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $page->rowNumber( $i ); ?>
					</td>
		    			<?php
		    			if ( $row->title_alias ) {
		                    ?>
		                    <td>
		                    <?php
		    			}
		    			else{
							echo "<td>";
		                }
						?>
						<a onclick="window.parent.jSelectArticle('<?php echo $row->id; ?>', '<?php echo $row->title; ?>');">
							<?php echo htmlspecialchars($row->title, ENT_QUOTES); ?></a>
					</td>
					<td align="center">
						<?php echo $access;?>
					</td>
					<td>
						<?php echo $row->id; ?>
					</td>
						<td>
							<a href="<?php echo $row->sect_link; ?>" title="<?php echo JText::_( 'Edit Section' ); ?>">
								<?php echo $row->section_name; ?></a>
						</td>
					<td>
						<a href="<?php echo $row->cat_link; ?>" title="<?php echo JText::_( 'Edit Category' ); ?>">
							<?php echo $row->name; ?></a>
					</td>
					<td nowrap="nowrap">
						<?php echo $date; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
			</table>

		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}

	function _getLists()
	{
		// Initialize variables
		$app	= &$this->get('Application');
		$db		= &$this->get('DBO');
		$filter	= null;

		// Get some variables from the request
		$sectionid			= JRequest::getVar( 'sectionid', -1, '', 'int' );
		$redirect			= $sectionid;
		$option				= JRequest::getVar( 'option' );
		$filter_order		= $app->getUserStateFromRequest("articleelement.filter_order", 'filter_order', '');
		$filter_order_Dir	= $app->getUserStateFromRequest("articleelement.filter_order_Dir", 'filter_order_Dir', '');
		$filter_state		= $app->getUserStateFromRequest("articleelement.filter_state", 'filter_state', '');
		$catid				= $app->getUserStateFromRequest("articleelement.catid", 'catid', 0);
		$filter_authorid	= $app->getUserStateFromRequest("articleelement.filter_authorid", 'filter_authorid', 0);
		$filter_sectionid	= $app->getUserStateFromRequest("articleelement.filter_sectionid", 'filter_sectionid', -1);
		$limit				= $app->getUserStateFromRequest('limit', 'limit', $app->getCfg('list_limit'));
		$limitstart			= $app->getUserStateFromRequest("articleelement.limitstart", 'limitstart', 0);
		$search				= $app->getUserStateFromRequest("articleelement.search", 'search', '');
		$search				= $db->getEscaped(trim(JString::strtolower($search)));

		// get list of categories for dropdown filter
		$query = "SELECT cc.id AS value, cc.title AS text, section" .
				"\n FROM #__categories AS cc" .
				"\n INNER JOIN #__sections AS s ON s.id = cc.section ".$filter .
				"\n ORDER BY s.ordering, cc.ordering";
		$lists['catid'] = JContentHelper::filterCategory($query, $catid);

		// get list of sections for dropdown filter
		$javascript = 'onchange="document.adminForm.submit();"';
		$lists['sectionid'] = mosAdminMenus::SelectSection('filter_sectionid', $filter_sectionid, $javascript);

		// table ordering
		if ($filter_order_Dir == 'DESC') {
			$lists['order_Dir'] = 'ASC';
		} else {
			$lists['order_Dir'] = 'DESC';
		}
		$lists['order'] = $filter_order;

		// search filter
		$lists['search'] = $search;

		return $lists;
	}
}
?>