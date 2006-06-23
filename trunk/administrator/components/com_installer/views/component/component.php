<?php
/**
 * @version $Id: component.php 4008 2006-06-13 01:49:41Z webImagery $
 * @package Joomla
 * @subpackage Installer
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Static class to handle component view logic
 *
 * @author Louis Landry <louis.landry@joomla.org>
 * @static
 * @package Joomla
 * @subpackage Installer
 * @category Controller
 * @since 1.5
 */
class JInstallerExtensionTasks
{
	/**
	 * @param string The URL option
	 */
	function showInstalled()
	{
		global $mainframe;

		$option				= JRequest::getVar( 'option' );
		$limit 				= $mainframe->getUserStateFromRequest( 'limit', 'limit', $mainframe->getCfg('list_limit') );
		$limitstart 		= $mainframe->getUserStateFromRequest( "$option.limitstart", 'limitstart', 0 );

		/* Get a database connector */
		$db =& $mainframe->getDBO();

		$query = 	"SELECT *" .
					"\n FROM #__components" .
					"\n WHERE parent = 0" .
					"\n ORDER BY iscore, name";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		/* Get the component base directory */
		$baseDir = JPATH_ADMINISTRATOR .DS. 'components';

		$numRows = count($rows);
		for($i=0;$i < $numRows; $i++)
		{
			$row =& $rows[$i];

			 /* Get the component folder and list of xml files in folder */
			jimport('joomla.filesystem.folder');
			$folder = $baseDir.DS.$row->option;
			if (JFolder::exists($folder)) {
				$xmlFilesInDir = JFolder::files($folder, '.xml$');
			} else {
				$xmlFilesInDir = null;
			}

			if (count($xmlFilesInDir)) {
				foreach ($xmlFilesInDir as $xmlfile)
				{
					if ($data = JApplicationHelper::parseXMLInstallFile($folder.DS.$xmlfile)) {
						foreach($data as $key => $value) {
							$row->$key = $value;
						}
					}	
					$row->jname = JString::strtolower(str_replace(" ", "_", $row->name));
				}
			}
		}

		/* Take care of the pagination */
		jimport('joomla.presentation.pagination');
		$page = new JPagination( count( $rows ), $limitstart, $limit );
		$rows = array_slice( $rows, $page->limitstart, $page->limit );

		JInstallerScreens_component::showInstalled($rows, $page);
	}

}

/**
 * Static class to handle component view display
 *
 * @author Louis Landry <louis.landry@joomla.org>
 * @static
 * @package Joomla
 * @subpackage Installer
 * @category View
 * @since 1.5
 */
class JInstallerScreens_component 
{
	/**
	* @param array An array of records
	* @param string The URL option
	*/
	function showInstalled(&$rows, &$page)
	{
		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php?option=com_installer&amp;extension=component" method="post" name="adminForm">

			<?php
				if (count($rows)) {
				?>
				<table class="adminlist" cellspacing="1">
				<thead>
				<tr>
					<th class="title" width="10">
						<?php echo JText::_( 'Num' ); ?>
					</th>
					<th class="title" nowrap="nowrap">
						<?php echo JText::_( 'Currently Installed' ); ?>
					</th>
					<th width="5%" align="center">
						<?php echo JText::_( 'Enabled' ); ?>
					</th>
					<th width="10%" align="center">
						<?php echo JText::_( 'Version' ); ?>
					</th>
					<th width="15%">
						<?php echo JText::_( 'Date' ); ?>
					</th>
					<th width="25%"  class="title">
						<?php echo JText::_( 'Author' ); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
					<td colspan="6">
					<?php echo $page->getListFooter(); ?>
					</td>
				</tfoot>
				<tbody>
				<?php
				$rc = 0;
				for ($i = 0, $n = count($rows); $i < $n; $i ++) {
					$row = & $rows[$i];

					$img 	= $row->enabled ? 'tick.png' : 'publish_x.png';
					$task 	= $row->enabled ? 'disable' : 'enable';
					$alt 	= $row->enabled ? JText::_( 'Enabled' ) : JText::_( 'Disabled' );
					$action	= $row->enabled ? JText::_( 'disable' ) : JText::_( 'enable' );
					$href 	= "<a href=\"index2.php?option=com_installer&amp;extension=component&amp;task=".$task."&amp;eid[]=".$row->id."\"><img src=\"images/".$img."\" border=\"0\" title=\"".$action."\" alt=\"".$alt."\" /></a>";

					if (!$row->option) {
						$href = '<strong>X</strong>';
					}

					if ($row->iscore) {
						$cbd 	= 'disabled';
						$style 	= 'style="color:#999999;"';
					} else {
						$cbd 	= '';
						$style 	= '';
					}

					$author_info = @$row->authorEmail .'<br />'. @$row->authorUrl;
					?>
					<tr class="<?php echo "row$rc"; ?>" <?php echo $style; ?>>
						<td>
							<?php echo $page->rowNumber( $i ); ?>
						</td>
						<td>
							<input type="checkbox" id="cb<?php echo $i;?>" name="eid[]" value="<?php echo $row->id; ?>" onclick="isChecked(this.checked);" <?php echo $cbd; ?> />
							<span class="bold">
								<?php echo $row->name; ?>
							</span>
						</td>
						<td align="center">
							<?php echo $href; ?>
						</td>
						<td align="center">
							<?php echo @$row->version != '' ? $row->version : '&nbsp;'; ?>
						</td>
						<td>
							<?php echo @$row->creationdate != '' ? $row->creationdate : '&nbsp;'; ?>
						</td>
						<td>
							<span onmouseover="return overlib('<?php echo $author_info; ?>', CAPTION, '<?php echo JText::_( 'Author Information' ); ?>', BELOW, LEFT);" onmouseout="return nd();">									<?php echo @$row->author != '' ? $row->author : '&nbsp;'; ?>
							</span>
						</td>
					</tr>
					<?php
					$rc = 1 - $rc;
				}
				?>
				</tbody>
				</table>
				<?php
			} else {
				echo JText::_( 'There are no custom components installed' );
			}
			?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="option" value="com_installer" />
		<input type="hidden" name="extension" value="component" />
		</form>
		<?php
	}
}
?>