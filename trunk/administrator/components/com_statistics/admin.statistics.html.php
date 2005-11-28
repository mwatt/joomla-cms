<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Statistics
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
* @package Joomla
* @subpackage Statistics
*/
class HTML_statistics {
	function show( &$browsers, &$platforms, $tldomains, $bstats, $pstats, $dstats, $sorts, $option ) {
		global $mosConfig_live_site;

		$tab = mosGetParam( $_REQUEST, 'tab', 'tab1' );
		$width = 400;	// width of 100%
		$tabs = new mosTabs(1);
		?>
		<style type="text/css">
		.bar_1{ background-color: #8D1B1B; border: 2px ridge #B22222; }
		.bar_2{ background-color: #6740E1; border: 2px ridge #4169E1; }
		.bar_3{ background-color: #8D8D8D; border: 2px ridge #D2D2D2; }
		.bar_4{ background-color: #CC8500; border: 2px ridge #FFA500; }
		.bar_5{ background-color: #5B781E; border: 2px ridge #6B8E23; }
		</style>
		<form action="index2.php" method="post" name="adminForm">
		<?php
		$title = JText::_( 'Browsers' );
		$tabs->startPane("statsPane");
		$tabs->startTab( $title, "browsers-page" );
		?>
		<table class="adminlist">
		<tr>
			<th class="title">&nbsp;<?php echo JText::_( 'Browser' ); ?>&nbsp;<?php echo $sorts['b_agent'];?></th>
			<th>&nbsp;</th>
			<th width="100" class="title">% <?php echo $sorts['b_hits'];?></th>
			<th width="100" class="title"><?php echo JText::_( 'NUM' ); ?></th>
		</tr>
		<?php
		$c = 1;
		if (is_array($browsers) && count($browsers) > 0) {
			$k = 0;
			foreach ($browsers as $b) {
				$f = $bstats->totalhits > 0 ? $b->hits / $bstats->totalhits : 0;
				$w = $width * $f;
			?>
			<tr class="row<?php echo $k;?>">
				<td width="200">
					&nbsp;<?php echo $b->agent; ?>&nbsp;
				</td>
				<td  width="<?php echo $width+10;?>">
					<div>&nbsp;<img src="<?php echo $mosConfig_live_site; ?>/components/com_poll/images/blank.png" class="bar_<?php echo $c; ?>" height="6" width="<?php echo $w; ?>"></div>
				</td>
				<td>
					<?php printf( "%.2f%%", $f * 100 );?>
				</td>
				<td>
					<?php echo $b->hits;?>
				</td>
			</tr>
			<?php
			$c = $c % 5 + 1;
			$k = 1 - $k;
			}
		}
		?>
		<tr>
			<th colspan="4">&nbsp;</th>
		</tr>
		</table>
		<?php
		$title = JText::_( 'OS Stats' );
		$tabs->endTab();
		$tabs->startTab( $title, "os-page" );
		?>
		<table class="adminlist">
		<tr>
			<th class="title">&nbsp;<?php echo JText::_( 'Operating System' ); ?>&nbsp;<?php echo $sorts['o_agent'];?></th>
			<th>&nbsp;</th>
			<th width="100" class="title">% <?php echo $sorts['o_hits'];?></th>
			<th width="100" class="title"><?php echo JText::_( 'NUM' ); ?></th>
		</tr>
		<?php
		$c = 1;
		if (is_array($platforms) && count($platforms) > 0) {
			$k = 0;
			foreach ($platforms as $p) {
				$f = $pstats->totalhits > 0 ? $p->hits / $pstats->totalhits : 0;
				$w = $width * $f;
				?>
				<tr class="row<?php echo $k;?>">
					<td width="200">
					&nbsp;<?php echo $p->agent; ?>&nbsp;
					</td>
					<td  width="<?php echo $width+10;?>">
					<div>&nbsp;<img src="<?php echo $mosConfig_live_site; ?>/components/com_poll/images/blank.png" class="bar_<?php echo $c; ?>" height="6" width="<?php echo $w; ?>"></div>
					</td>
					<td>
					<?php printf( "%.2f%%", $f * 100 );?>
					</td>
					<td>
					<?php echo $p->hits;?>
					</td>
				</tr>
				<?php
				$c = $c % 5 + 1;
				$k = 1 - $k;
			}
		}
		?>
		<tr>
			<th colspan="4">&nbsp;</th>
		</tr>
		</table>
		<?php
		$title = JText::_( 'Domain Stats' );
		$tabs->endTab();
		$tabs->startTab( $title, "domain-page" );
		?>
		<table class="adminlist">
		<tr>
			<th class="title">&nbsp;<?php echo JText::_( 'Domain' ); ?>&nbsp;<?php echo $sorts['d_agent'];?></th>
			<th>&nbsp;</th>
			<th width="100" class="title">% <?php echo $sorts['d_hits'];?></th>
			<th width="100" class="title"><?php echo JText::_( 'NUM' ); ?></th>
		</tr>
		<?php
		$c = 1;
		if (is_array($tldomains) && count($tldomains) > 0) {
			$k = 0;
			foreach ($tldomains as $b) {
				$f = $dstats->totalhits > 0 ? $b->hits / $dstats->totalhits : 0;
				$w = $width * $f;
				?>
				<tr class="row<?php echo $k;?>">
					<td width="200">
						&nbsp;<?php echo $b->agent; ?>&nbsp;
					</td>
					<td  width="<?php echo $width+10;?>">
						<div>&nbsp;<img src="<?php echo $mosConfig_live_site; ?>/components/com_poll/images/blank.png" class="bar_<?php echo $c; ?>" height="6" width="<?php echo $w; ?>"></div>
					</td>
					<td>
						<?php printf( "%.2f%%", $f * 100 );?>
					</td>
					<td>
						<?php echo $b->hits;?>
					</td>
				</tr>
				<?php
				$c = $c % 5 + 1;
				$k = 1 - $k;
			}
		}
		?>
		<tr>
			<th colspan="4">&nbsp;</th>
		</tr>
		</table>
		<?php
		$tabs->endTab();
		$tabs->endPane();
		?>
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="tab" value="<?php echo $tab;?>" />
		<input type="hidden" name="task" value="<?php echo $task;?>" />
		<input type="hidden" name="op" value="bod" />
		</form>
		<?php
	}

	function pageImpressions( &$rows, $pageNav, $option, $task ) {
		global $mosConfig_live_site;
		?>
		<form action="index2.php" method="post" name="adminForm">
		<table class="adminlist">
		<tr>
			<th style="text-align:right"><?php echo JText::_( 'NUM' ); ?></th>
			<th class="title"><?php echo JText::_( 'Title' ); ?></th>
			<th align="center" nowrap="nowrap"><?php echo JText::_( 'Page Impressions' ); ?></th>
		</tr>
		<?php
		$i = $pageNav->limitstart;
		$k = 0;
		foreach ($rows as $row) {
			?>
			<tr class="row<?php echo $k;?>">
				<td align="right">
					<?php echo ++$i; ?>
				</td>
				<td>
					&nbsp;<?php echo $row->title." (".$row->created.")"; ?>&nbsp;
				</td>
				<td align="center">
					<?php echo $row->hits; ?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</table>
		<?php echo $pageNav->getListFooter(); ?>
	  	<input type="hidden" name="option" value="<?php echo $option;?>" />
	  	<input type="hidden" name="task" value="<?php echo $task;?>" />
	  	<input type="hidden" name="op" value="pi" />
		</form>
		<?php
	}

	function showSearches( &$rows, $pageNav, $option, $task ) {
		global $mainframe, $mosConfig_live_site;

		?>
		<form action="index2.php" method="post" name="adminForm">
		<table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminheading">
			<tr>
				<td>
				<span class="componentheading"><?php echo JText::_( 'logging is' ); ?> :
				<?php echo $mainframe->getCfg( 'enable_log_searches' ) ? '<b><font color="green">'. JText::_( 'Enabled' ) .'</font></b>' : '<b><font color="red">'. JText::_( 'Disabled' ) .'</font></b>' ?>
				</span>
				</td>
			</tr>
		</table>
		<table class="adminlist">
		<tr>
			<th style="text-align:right"><?php echo JText::_( 'NUM' ); ?></th>
			<th class="title"><?php echo JText::_( 'Search Text' ); ?></th>
			<th nowrap="nowrap"><?php echo JText::_( 'Times Requested' ); ?></th>
			<th nowrap="nowrap"><?php echo JText::_( 'Results Returned' ); ?></th>
		</tr>
		<?php
		$k = 0;
		for ($i=0, $n = count($rows); $i < $n; $i++) {
			$row =& $rows[$i];
			?>
			<tr class="row<?php echo $k;?>">
				<td align="right">
				<?php echo $i+1+$pageNav->limitstart; ?>
				</td>
				<td><?php echo $row->search_term;?></td>
				<td><?php echo $row->hits; ?></td>
				<td align="center"><?php echo $row->returns; ?></td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
	</table>
	<?php echo $pageNav->getListFooter(); ?>
  	<input type="hidden" name="option" value="<?php echo $option;?>" />
  	<input type="hidden" name="task" value="<?php echo $task;?>" />
  	<input type="hidden" name="op" value="set" />
	</form>
	<?php
	}
}
?>
