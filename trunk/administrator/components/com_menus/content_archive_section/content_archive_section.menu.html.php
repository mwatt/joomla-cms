<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Menus
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Writes the edit form for new and existing content item
*
* A new record is defined when <var>$row</var> is passed with the <var>id</var>
* property set to 0.
* @package Joomla
* @subpackage Menus
*/
class content_archive_section_menu_html {

	function editSection( &$menu, &$lists, &$params, $option )
	{
		mosCommonHTML::loadOverlib();
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}
			var form = document.adminForm;

			if ( getSelectedValue( 'adminForm', 'componentid' ) < 0 ) {
				alert( "<?php echo JText::_( 'You must select a Section', true ); ?>" );
				return;
			}

			if ( form.name.value == '' ) {
				if ( form.componentid.value == 0 ) {
					form.name.value = "All Sections";
				} else {
					form.name.value = form.componentid.options[form.componentid.selectedIndex].text;
				}
			}
			form.link.value = "index.php?option=com_content&task=archivesection&id=" + form.componentid.value;
			submitform( pressbutton );
		}
		</script>
		<form action="index2.php" method="post" name="adminForm">

		<table width="100%">
			<tr valign="top">
				<td width="60%">
					<table class="adminform">
					<?php menuHTML::MenuOutputTop( $lists, $menu, 'Blog - Content Section Archive', 1 ); ?>
					<tr>
						<td valign="top" align="right">
						<?php echo JText::_( 'Section' ); ?>:
						</td>
						<td>
						<?php echo $lists['componentid']; ?>
						</td>
					</tr>
					<?php menuHTML::MenuOutputBottom( $lists, $menu ); ?>
					</table>
				</td>
				<td width="40%">
					<?php menuHTML::MenuOutputParams( $params, $menu ); ?>
				</td>
			</tr>
		</table>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="id" value="<?php echo $menu->id; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $menu->id; ?>" />
		<input type="hidden" name="menutype" value="<?php echo $menu->menutype; ?>" />
		<input type="hidden" name="type" value="<?php echo $menu->type; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
	}
}
?>