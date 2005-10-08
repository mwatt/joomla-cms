<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Menus
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* Display wrapper
* @package Joomla
* @subpackage Menus
*/
class wrapper_menu_html {


	function edit( &$menu, &$lists, &$params, $option ) {
		global $mosConfig_live_site;
		global $_LANG;
		?>
		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>
		<script language="Javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/overlib_mini.js"></script>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			if ( pressbutton == 'cancel' ) {
				submitform( pressbutton );
				return;
			}
			var form = document.adminForm;
			if ( form.name.value == "" ) {
				alert( "<?php echo $_LANG->_( 'This Menu item must have a title' ); ?>" );
			} else {
				<?php
				if ( !$menu->id ) {
					?>
					if ( form.url.value == "" ){
						alert( "<?php echo $_LANG->_( 'You must provide a url.' ); ?>" );
					} else {
						submitform( pressbutton );
					}
					<?php
				} else {
					?>
					submitform( pressbutton );
					<?php
				}
				?>
			}
		}
		</script>
		<form action="index2.php" method="post" name="adminForm">
		<table class="adminheading">
		<tr>
			<th>
			<?php echo $menu->id ? $_LANG->_( 'Edit' ) : $_LANG->_( 'Add' );?> <?php echo $_LANG->_( 'Menu Item :: Wrapper' ); ?>
			</th>
		</tr>
		</table>

		<table width="100%">
		<tr valign="top">
			<td width="60%">
				<table class="adminform">
				<tr>
					<th colspan="2">
					<?php echo $_LANG->_( 'Details' ); ?>
					</th>
				</tr>
				<tr>
					<td width="10%" align="right" valign="top">
					<?php echo $_LANG->_( 'Name' ); ?>:
					</td>
					<td width="200px">
					<input type="text" name="name" size="30" maxlength="100" class="inputbox" value="<?php echo $menu->name; ?>"/>
					</td>
				</tr>
				<tr>
					<td width="20%" align="right">
					<?php echo $_LANG->_( 'Wrapper Link' ); ?>:
					</td>
					<td width="80%">
					<input class="inputbox" type="text" name="url" size="50" maxlength="250" value="<?php echo @$menu->url; ?>" />
					</td>
				</tr>
				<tr>
					<td width="10%" align="right">
					<?php echo $_LANG->_( 'Url' ); ?>:
					</td>
					<td width="80%">
                    <?php echo ampReplace($lists['link']); ?>
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo $_LANG->_( 'Parent Item' ); ?>:
					</td>
					<td colspan="2">
					<?php echo $lists['parent'];?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<?php echo $_LANG->_( 'Ordering' ); ?>:
					</td>
					<td colspan="2">
					<?php echo $lists['ordering']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<?php echo $_LANG->_( 'Access Level' ); ?>:
					</td>
					<td colspan="2">
					<?php echo $lists['access']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<?php echo $_LANG->_( 'Published' ); ?>:
					</td>
					<td colspan="2">
					<?php echo $lists['published']; ?>
					</td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				</table>
			</td>
			<td width="40%">
				<table class="adminform">
				<tr>
					<th>
					<?php echo $_LANG->_( 'Parameters' ); ?>
					</th>
				</tr>
				<tr>
					<td>
					<?php echo $params->render();?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="id" value="<?php echo $menu->id; ?>" />
		<input type="hidden" name="menutype" value="<?php echo $menu->menutype; ?>" />
		<input type="hidden" name="type" value="<?php echo $menu->type; ?>" />
		<input type="hidden" name="link" value="<?php echo $menu->link; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
	}
}
?>