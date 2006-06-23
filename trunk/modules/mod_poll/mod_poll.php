<?php
/**
* @version $Id$
* @package Joomla
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

if (!defined('_JOS_POLL_MODULE')) {
	/** ensure that functions are declared only once */
	define('_JOS_POLL_MODULE', 1);

	/**
	 * @param int The current menu item
	 */
	function show_poll_vote_form($Itemid, &$params) {
		global $mainframe;

		$db =& $mainframe->getDBO();
		$query = "SELECT p.id, p.title" .
				"\n FROM #__polls AS p, #__poll_menu AS pm" .
				"\n WHERE (pm.menuid = ".(int) $Itemid." OR pm.menuid = 0)" .
				"\n AND p.id = pm.pollid" .
				"\n AND p.published = 1";

		$db->setQuery($query);
		$polls = $db->loadObjectList();

		// try to find poll component's Itemid
		$query = "SELECT id"
		. "\n FROM #__menu"
		. "\n WHERE type = 'components'"
		. "\n AND published = 1"
		. "\n AND link = 'index.php?option=com_poll'"
		;
		$db->setQuery( $query );
		$_Itemid = $db->loadResult();
		
		if ($_Itemid) {
			$_Itemid = '&amp;Itemid='. $_Itemid;
		}			
		
		if ($db->getErrorNum()) {
			echo $db->stderr(true);
			return;
		}

		foreach ($polls as $poll) {
			if ($poll->id && $poll->title) {
				$query = "SELECT id, text" .
						"\n FROM #__poll_data" .
						"\n WHERE pollid = $poll->id" .
						"\n AND text <> ''" .
						"\n ORDER BY id";
				$db->setQuery($query);

				if (!($options = $db->loadObjectList())) {
					echo "MD ".$db->stderr(true);
					return;
				}
				poll_vote_form_html($poll, $options, $_Itemid, $params);
			}
		}
	}

	/**
	 * @param object Poll object
	 * @param array
	 * @param int The current menu item
	 */
	function poll_vote_form_html(&$poll, $options, $_Itemid, &$params) {
		$tabclass_arr 		= array ('sectiontableentry2', 'sectiontableentry1');
		$tabcnt 			= 0;
		$moduleclass_sfx 	= $params->get('moduleclass_sfx');

		$cookiename 		= "voted$poll->id";
		$voted 				= mosGetParam( $_COOKIE, $cookiename, 'z' );
		?>
		<script language="javascript" type="text/javascript">
		<!--
		function submitbutton() {
			var form 		= document.pollxtd;
			var radio		= form.voteid;
			var radioLength = radio.length;
			var check 		= 0;

			if ( '<?php echo $voted; ?>' != 'z' ) {
				alert('<?php echo JText::_( 'You already voted for this poll today!' ); ?>');
				return;
			}
			for(var i = 0; i < radioLength; i++) {
				if(radio[i].checked) {
					form.submit();
					check = 1;
				}
			}
			if (check == 0) {
				alert('<?php echo JText::_( 'WARNSELECT' ); ?>');
			}
		}
		//-->
		</script>
		<form name="form2" method="post" action="<?php echo sefRelToAbs("index.php?option=com_poll$_Itemid"); ?>">

		<table width="95%" border="0" cellspacing="0" cellpadding="1" align="center" class="poll<?php echo $moduleclass_sfx; ?>">
		<thead>
		<tr>
			<td style="font-weight: bold;">
				<?php echo $poll->title; ?>
			</td>
		</tr>
		</thead>
		<tr>
			<td align="center">
				<table class="pollstableborder<?php echo $moduleclass_sfx; ?>" cellspacing="0" cellpadding="0" border="0">
				<?php
				for ($i = 0, $n = count($options); $i < $n; $i ++) {
					?>
					<tr>
						<td class="<?php echo $tabclass_arr[$tabcnt]; ?><?php echo $moduleclass_sfx; ?>" valign="top">
							<input type="radio" name="voteid" id="voteid<?php echo $options[$i]->id;?>" value="<?php echo $options[$i]->id;?>" alt="<?php echo $options[$i]->id;?>" />
						</td>
						<td class="<?php echo $tabclass_arr[$tabcnt]; ?><?php echo $moduleclass_sfx; ?>" valign="top">
							<label for="voteid<?php echo $options[$i]->id;?>">
								<?php echo stripslashes($options[$i]->text); ?>
							</label>
						</td>
					</tr>
					<?php
					if ($tabcnt == 1) {
						$tabcnt = 0;
					} else {
						$tabcnt ++;
					}
				}
				?>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<div align="center">
					<input type="submit" name="task_button" class="button" value="<?php echo JText::_('Vote'); ?>" />
					&nbsp;
					<input type="button" name="option" class="button" value="<?php echo JText::_('Results'); ?>" onclick="document.location.href='<?php echo sefRelToAbs("index.php?option=com_poll&amp;task=results&amp;id=$poll->id$_Itemid"); ?>';" />
				</div>
			</td>
		</tr>
		</table>

		<input type="hidden" name="id" value="<?php echo $poll->id;?>" />
		<input type="hidden" name="task" value="vote" />
		</form>
		<?php
	}
}

show_poll_vote_form($Itemid, $params);
?>