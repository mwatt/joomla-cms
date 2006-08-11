<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Banners
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* Banner clients
* @package Joomla
*/
class JViewBannerClients 
{
	function showClients( &$rows, &$pageNav, $option, &$lists )
	{
		$user =& JFactory::getUser();
		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php?option=com_banners&amp;task=listclients" method="post" name="adminForm">

			<table>
			<tr>
				<td align="left" width="100%">
					<?php echo JText::_( 'Filter' ); ?>:
					<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
					<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
					<button onclick="getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
				</td>
				<td nowrap="nowrap">
				</td>
			</tr>
			</table>

			<table class="adminlist">
			<thead>
			<tr>
				<th width="20">
	           		<?php echo JText::_( 'Num' ); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
				</th>
				<th nowrap="nowrap" class="title">
					<?php mosCommonHTML::tableOrdering( 'Client Name', 'a.name', $lists, 'listclients' ); ?>
				</th>
				<th width="3%" nowrap="nowrap">
					<?php mosCommonHTML::tableOrdering( 'ID', 'a.cid', $lists, 'listclients' ); ?>
				</th>
				<th nowrap="nowrap" class="title" width="35%">
					<?php mosCommonHTML::tableOrdering( 'Contact', 'a.contact', $lists, 'listclients' ); ?>
				</th>
				<th align="center" nowrap="nowrap" width="5%">
					<?php mosCommonHTML::tableOrdering( 'No. of Active Banners', 'bid', $lists, 'listclients' ); ?>
				</th>
			</tr>
			</thead>
			<?php
			$k = 0;
			for ($i=0, $n=count( $rows ); $i < $n; $i++) {
				$row = &$rows[$i];

				$row->id 		= $row->cid;
				$link 			= ampReplace( 'index2.php?option=com_banners&task=editclient&hidemainmenu=1&cid[]='. $row->id );

				$checked 		= mosCommonHTML::CheckedOutProcessing( $row, $i );
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center">
						<?php echo $pageNav->rowNumber( $i ); ?>
					</td>
					<td>
						<?php echo $checked; ?>
					</td>
					<td>
						<?php
						if ( $row->checked_out && ( $row->checked_out != $user->get('id') ) ) {
							echo $row->name;
						} else {
							?>
							<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'Edit Banner Client' ); ?>">
								<?php echo $row->name; ?></a>
							<?php
						}
						?>
					</td>
					<td align="center">
						<?php echo $row->cid; ?>
					</td>
					<td>
						<?php echo $row->contact; ?>
					</td>
					<td align="center">
						<?php echo $row->bid;?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			<tfoot>
				<td colspan="6">
					<?php echo $pageNav->getListFooter(); ?>
				</td>
			</tfoot>
			</table>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="listclients" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}

	function bannerClientForm( &$row, $option ) 
	{
		mosMakeHtmlSafe( $row, ENT_QUOTES, 'extrainfo' );
		?>
		<script language="javascript" type="text/javascript">
		<!--
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancelclient') {
				submitform( pressbutton );
				return;
			}
			// do field validation
			if (form.name.value == "") {
				alert( "<?php echo JText::_( 'Please fill in the Client Name.', true ); ?>" );
			} else if (form.contact.value == "") {
				alert( "<?php echo JText::_( 'Please fill in the Contact Name.', true ); ?>" );
			} else if (form.email.value == "") {
				alert( "<?php echo JText::_( 'Please fill in the Contact Email.', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		//-->
		</script>

		<form action="index2.php" method="post" name="adminForm">

		<div class="col50">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'Details' ); ?></legend>

				<table class="admintable">
					<tr>
						<td width="20%" nowrap="nowrap">
							<label for="name">
								<?php echo JText::_( 'Client Name' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" name="name" id="name" size="40" maxlength="60" value="<?php echo $row->name; ?>" />
						</td>
					</tr>
					<tr>
						<td nowrap="nowrap">
							<label for="contact">
								<?php echo JText::_( 'Contact Name' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" name="contact" id="contact" size="40" maxlength="60" value="<?php echo $row->contact; ?>" />
						</td>
					</tr>
					<tr>
						<td nowrap="nowrap">
							<label for="email">
								<?php echo JText::_( 'Contact Email' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" name="email" id="email" size="40" maxlength="60" value="<?php echo $row->email; ?>" />
						</td>
					</tr>
					</table>
			</fieldset>
		</div>

		<div class="col50">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'Extra Info' ); ?></legend>

				<table class="admintable">
				<tr>
					<td valign="top">
						<textarea class="inputbox" name="extrainfo" id="extrainfo" cols="60" rows="10"><?php echo str_replace('&','&amp;',$row->extrainfo);?></textarea>
					</td>
				</tr>
				</table>
			</fieldset>
		</div>
		<div class="clr"></div>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="cid" value="<?php echo $row->cid; ?>" />
		<input type="hidden" name="client_id" value="<?php echo $row->cid; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}
}
?>