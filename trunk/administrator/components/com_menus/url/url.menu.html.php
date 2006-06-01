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
class url_menu_html {

	function edit( $menu, $lists, $params, $option )
	{
		mosCommonHTML::loadOverlib();
		$types[] = mosHTML::makeOption( 'components', 'Component' );
		$types[] = mosHTML::makeOption( 'url', 'URL' );
		$types[] = mosHTML::makeOption( 'separator', 'Separator' );
		$types[] = mosHTML::makeOption( 'component_item_link', 'Other Menu Item' );
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			// do field validation
			if (trim(form.name.value) == ""){
				alert( "<?php echo JText::_( 'Link must have a name' ); ?>" );
			} else if (trim(form.link.value) == ""){
				alert( "<?php echo JText::_( 'You must provide a url.' ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		</script>

		<form action="index2.php" method="post" name="adminForm">

		<table width="100%">
		<tr valign="top">
			<td width="60%">
				<table class="adminform">
				<?php menuHTML::MenuOutputTop( $lists, $menu, 'Link - URL' ); ?>
				<tr>
					<td align="right">
					<?php echo JText::_( 'Link' ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="link" size="50" maxlength="250" value="<?php echo $menu->link; ?>" />
					</td>
				</tr>
				<?php menuHTML::MenuOutputBottom( $lists, $menu ); ?>
				<tr>
					<td valign="top" align="right">
					<?php echo JText::_( 'Type' ); ?>:
					</td>
					<td>
					<?php echo mosHTML::radioList( $types, 'type', '', $menu->type ); ?>
					</td>
				</tr>
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
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
	}
}
?>