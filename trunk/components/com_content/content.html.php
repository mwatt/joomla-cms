<?php
/**
 * @version $Id$
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

/**
 * HTML View class for the Content component
 *
 * @static
 * @package Joomla
 * @subpackage Content
 * @since 1.0
 */
class JViewContentHTML {

	/**
	 * Draws a Content List Used by Content Category & Content Section
	 * 
	 * @since 1.5
	 */
	function showSection(& $model) {
		require_once (dirname(__FILE__).DS.'view'.DS.'section'.DS.'section.php');
		/*
		 * Need to cache this for speed
		 */
		JViewContentHTML_section::show($model);
	}

	/**
	* Draws a Content List
	* Used by Content Category & Content Section
	*/
	function showCategory(& $model, & $access, & $lists, $order) {
		require_once (dirname(__FILE__).DS.'view'.DS.'category'.DS.'category.php');
		/*
		 * Need to cache this for speed
		 */
		JViewContentHTML_category::show($model, $access, $lists, $order);
	}

	function showArchive(&$model, &$menu, &$access, $id) 
	{
		require_once (dirname(__FILE__).DS.'view'.DS.'archive'.DS.'archive.php');
		/*
		 * Need to cache this for speed
		 */
		JViewContentHTML_archive::show($model, $access, $menu, $id);
	}

	function showBlog(&$model, &$access, &$menu) 
	{
		require_once (dirname(__FILE__).DS.'view'.DS.'blog'.DS.'blog.php');
		/*
		 * Need to cache this for speed
		 */
		JViewContentHTML_blog::show($model, $access, $menu);
	}

	function showItem(&$model, &$access, $page) 
	{
		require_once (dirname(__FILE__).DS.'view'.DS.'item'.DS.'item.php');
		/*
		 * Need to cache this for speed
		 */
		JViewContentHTML_item::show($model, $access, $page);
	}

	/**
	* Writes the edit form for new and existing content item
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.
	* 
	* @return void
	* @since 1.0
	*/
	function editContent(& $row, $section, & $lists, & $images, & $access, $myid, $sectionid, $task, $Itemid) 	
	{
		global $mainframe, $Itemid;
		
		jimport( 'joomla.presentation.editor' );
		$editor =& JEditor::getInstance();

		// Require the toolbar
		require_once (JPATH_SITE.'/includes/HTML_toolbar.php');

		/*
		 * Initialize some variables
		 */
		$document = & $mainframe->getDocument();
		$Returnid = JRequest::getVar( 'Returnid', $Itemid, '', 'int' );
		$tabs = new mosTabs(0);

		$document->addStyleSheet('includes/js/calendar/calendar-mos.css');
		$document->addScript('includes/js/calendar/calendar_mini.js');
		$document->addScript('includes/js/calendar/lang/calendar-en.js');
		
		mosCommonHTML::loadOverlib();

		// Ensure the row data is safe html
		mosMakeHtmlSafe($row);

		?>
	  	<script language="javascript" type="text/javascript">
		onunload = WarnUser;
		var folderimages = new Array;
		<?php
		$i = 0;
		foreach ($images as $k => $items) {
			foreach ($items as $v) {
				echo "\n	folderimages[".$i ++."] = new Array( '$k','".addslashes($v->value)."','".addslashes($v->text)."' );";
			}
		}
		?>
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			// var goodexit=false;
			// assemble the images back into one field
			form.goodexit.value=1;
			var temp = new Array;
			for (var i=0, n=form.imagelist.options.length; i < n; i++) {
				temp[i] = form.imagelist.options[i].value;
			}
			form.images.value = temp.join( '\n' );
			try {
				form.onsubmit();
			}
			catch(e){}
			// do field validation
			if (form.title.value == "") {
				alert ( "<?php echo JText::_( 'Content item must have a title', true ); ?>" );
			} else if (parseInt('<?php echo $row->sectionid;?>')) {
				// for content items
				if (getSelectedValue('adminForm','catid') < 1) {
					alert ( "<?php echo JText::_( 'Please select a category', true ); ?>" );
				} else {
					<?php
					echo $editor->save( 'introtext' );
					echo $editor->save( 'fulltext' );
					?>
					submitform(pressbutton);
				}
			} else {
				// for static content
				<?php
				echo $editor->save( 'introtext' );
				?>
				submitform(pressbutton);
			}
		}

		function setgood(){
			document.adminForm.goodexit.value=1;
		}

		function WarnUser(){
			if (document.adminForm.goodexit.value==0) {
				alert('<?php echo JText::_( 'WARNUSER', true );?>');
				window.location="<?php echo sefRelToAbs("index.php?option=com_content&task=".$task."&sectionid=".$sectionid."&id=".$row->id."&Itemid=".$Itemid); ?>";
			}
		}
		</script>
		<?php
		$docinfo = '<table><tr><td>'; 
		$docinfo .= '<strong>'.JText::_('Expiry Date').':</strong> ';
		$docinfo .= '</td><td>'; 
		$docinfo .= $row->publish_down;
		$docinfo .= '</td></tr><tr><td>'; 
		$docinfo .= '<strong>'.JText::_('Version').':</strong> ';
		$docinfo .= '</td><td>'; 
		$docinfo .= $row->version;
		$docinfo .= '</td></tr><tr><td>'; 
		$docinfo .= '<strong>'.JText::_('Created').':</strong> ';
		$docinfo .= '</td><td>'; 
		$docinfo .= $row->created;
		$docinfo .= '</td></tr><tr><td>'; 
		$docinfo .= '<strong>'.JText::_('Last Modified').':</strong> ';
		$docinfo .= '</td><td>'; 
		$docinfo .= $row->modified;
		$docinfo .= '</td></tr><tr><td>'; 
		$docinfo .= '<strong>'.JText::_('Hits').':</strong> ';
		$docinfo .= '</td><td>'; 
		$docinfo .= $row->hits;
		$docinfo .= '</td></tr></table>'; 
		?>
		<form action="index.php" method="post" name="adminForm" onSubmit="javascript:setgood();">

		<div class="componentheading">
			<?php echo $row->id ? JText::_( 'Edit' ) : JText::_( 'New' );?>
			<?php echo JText::_( 'Content' );?>		
		</div>

		<table class="adminform" width="100%">
		<tr>
			<td>
				<div style="float: left;">
					<label for="title">
						<?php echo JText::_( 'Title' ); ?>:
					</label>
					<br />
					<input class="inputbox" type="text" id="title" name="title" size="50" maxlength="100" value="<?php echo $row->title; ?>" />
					&nbsp;&nbsp;&nbsp;
					<?php echo mosToolTip('<table>'.$docinfo.'</table>', JText::_( 'Item Information', true ), '', '', '<strong>['.JText::_( 'Info', true ).']</strong>'); ?>
				</div>
				<div style="float: right;">
					<?php
					// Toolbar Top
					mosToolBar::startTable();
					mosToolBar::save();
					mosToolBar::apply('apply_new');
					mosToolBar::cancel();
					mosToolBar::endtable();
					?>
				</div>
			</td>
		</tr>
		</table>
		
		<?php
		if ($row->sectionid) {
			?>
			<table class="adminform" width="100%">
			<tr>
				<td>
					<label for="catid">
						<?php echo JText::_( 'Section' ); ?>:
					</label>
					<strong>
						<?php echo $section;?>
					</strong>
				</td>
				<td>
					<label for="catid">
						<?php echo JText::_( 'Category' ); ?>:
					</label>
					<?php echo $lists['catid']; ?>
				</td>
			</tr>
			</table>
			<?php
		}
		?>
		
		<table class="adminform">
		<tr>
			<td>
				<?php
				if (intval($row->sectionid) > 0) {
					?>
					<?php echo JText::_( 'Intro Text' ) .' ('. JText::_( 'Required' ) .')'; ?>:
					<?php
				} else {
					?>
						<?php echo JText::_( 'Main Text' ) .' ('. JText::_( 'Required' ) .')'; ?>:
					<?php
				}
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php
				// parameters : areaname, content, hidden field, width, height, rows, cols
				echo $editor->display('introtext', $row->introtext, '600', '400', '70', '15');
				?>
			</td>
		</tr>
		<?php
		if (intval($row->sectionid) > 0) {
			?>
			<tr>
				<td>
					<?php echo JText::_( 'Main Text' ) .' ('. JText::_( 'Optional' ) .')'; ?>:
				</td>
			</tr>
			<tr>
				<td>
					<?php
					// parameters : areaname, content, hidden field, width, height, rows, cols
					echo $editor->display('fulltext', $row->fulltext, '600', '400', '70', '15');
					?>
				</td>
			</tr>
			<?php
		}
		?>
		</table>
		
		<?php
		// Toolbar Bottom
		mosToolBar::startTable();
		mosToolBar::save();
		mosToolBar::apply();
		mosToolBar::cancel();
		mosToolBar::endtable();
		?>
		
		<br />
		
		<?php
		$title = JText::_('Images');
		$tabs->startPane('content-pane');
		$tabs->startTab($title, 'images-page');
		?>
			<table width="100%" class="adminform">
			<tr>
				<td colspan="4">
					<label for="folders">
						<?php echo JText::_( 'Sub-folder' ); ?>
					</label>
					- <?php echo $lists['folders'];?>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="imagefiles">
						<?php echo JText::_( 'Gallery Images' ); ?>
					</label>
				</td>
				<td width="1%">
				</td>
				<td valign="top">
					<?php echo JText::_( 'Content Images' ); ?>
				</td>
				<td valign="top">
					<?php echo JText::_( 'Edit Image' ); ?>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<?php echo $lists['imagefiles'];?>
					<br />
					<input class="button" type="button" value="<?php echo JText::_( 'Insert' ); ?>" onclick="addSelectedToList('adminForm','imagefiles','imagelist')" />
				</td>
				<td width="2%">
					<input class="button" type="button" value=">>" onclick="addSelectedToList('adminForm','imagefiles','imagelist')" title="<?php echo JText::_( 'Add' ); ?>"/>
					<br/>
					<input class="button" type="button" value="<<" onclick="delSelectedFromList('adminForm','imagelist')" title="<?php echo JText::_( 'Remove' ); ?>"/>
				</td>
				<td valign="top">
					<?php echo $lists['imagelist'];?>
					<br />
					<input class="button" type="button" value="<?php echo JText::_( 'Up' ); ?>" onclick="moveInList('adminForm','imagelist',adminForm.imagelist.selectedIndex,-1)" />
					<br />
					<input class="button" type="button" value="<?php echo JText::_( 'Down' ); ?>" onclick="moveInList('adminForm','imagelist',adminForm.imagelist.selectedIndex,+1)" />
				</td>
				<td valign="top" width="100%">
					<table width="100%">
					<tr>
						<td align="right">
							<label for="_source">
								<?php echo JText::_( 'Source' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" id= "_source" name= "_source" value="" size="15" />
						</td>
					</tr>
					<tr>
						<td align="right" valign="top">
							<label for="_align">
								<?php echo JText::_( 'Align' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $lists['_align']; ?>
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="_alt">
								<?php echo JText::_( 'Alt Text' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" id="_alt" name="_alt" value="" size="15" />
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="_border">
								<?php echo JText::_( 'Border' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" id="_border" name="_border" value="" size="3" maxlength="1" />
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="_caption">
								<?php echo JText::_( 'Caption' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" id="_caption" name="_caption" value="" size="30" />
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="_caption_position">
								<?php echo JText::_( 'Caption Position' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $lists['_caption_position']; ?>
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="_caption_align">
								<?php echo JText::_( 'Caption Align' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $lists['_caption_align']; ?>
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="_width">
								<?php echo JText::_( 'Caption Width' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" id="_width" name="_width" value="" size="5" maxlength="5" />
						</td>
					</tr>
					<tr>
						<td align="right"></td>
						<td>
							<input class="button" type="button" value="<?php echo JText::_( 'Apply' ); ?>" onclick="applyImageProps()" />
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					<img name="view_imagefiles" src="images/M_images/blank.png" width="50" alt="<?php echo JText::_( 'No Image' ); ?>" />
				</td>
				<td>&nbsp;</td>
				<td>
					<img name="view_imagelist" src="images/M_images/blank.png" width="50" alt="<?php echo JText::_( 'No Image' ); ?>" />
				</td>
				<td>&nbsp;</td>
			</tr>
			</table>
			
		<?php
		$title = JText::_('Publishing');
		$tabs->endTab();
		$tabs->startTab($title, 'publish-page');
		?>
		
			<table class="adminform">
			<?php
			if ($access->canPublish) {
				?>
				<tr>
					<td >
						<label for="state">
							<?php echo JText::_( 'Published' ); ?>:
						</label>
					</td>
					<td>
						<?php echo $lists['state']; ?>
					</td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td width="120">
					<label for="frontpage">
						<?php echo JText::_( 'Show on Front Page' ); ?>:
					</label>
				</td>
				<td>
					<?php echo $lists['frontpage']; ?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="created_by_alias">
						<?php echo JText::_( 'Author Alias' ); ?>:
					</label>
				</td>
				<td>
					<input type="text" id="created_by_alias" name="created_by_alias" size="50" maxlength="100" value="<?php echo $row->created_by_alias; ?>" class="inputbox" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="publish_up">
						<?php echo JText::_( 'Start Publishing' ); ?>:
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" name="publish_up" id="publish_up" size="25" maxlength="19" value="<?php echo $row->publish_up; ?>" />
					<input type="reset" class="button" value="..." onclick="return showCalendar('publish_up', 'y-mm-dd');" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="publish_down">
						<?php echo JText::_( 'Finish Publishing' ); ?>:
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" name="publish_down" id="publish_down" size="25" maxlength="19" value="<?php echo $row->publish_down; ?>" />
					<input type="reset" class="button" value="..." onclick="return showCalendar('publish_down', 'y-mm-dd');" />
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="access">
						<?php echo JText::_( 'Access Level' ); ?>:
					</label>
				</td>
				<td>
					<?php echo $lists['access']; ?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="ordering">
						<?php echo JText::_( 'Ordering' ); ?>:
					</label>
				</td>
				<td>
					<?php echo $lists['ordering']; ?>
				</td>
			</tr>
			</table>
			
		<?php
		$title = JText::_('Metadata');
		$tabs->endTab();
		$tabs->startTab($title, 'meta-page');
		?>
			<table class="adminform">
			<tr>
				<td  valign="top">
					<label for="metadesc">
						<?php echo JText::_( 'Description' ); ?>:
					</label>
				</td>
				<td>
					<textarea rows="5" cols="50" style="width:500px; height:120px" class="inputbox" id="metadesc" name="metadesc"><?php echo str_replace('&','&amp;',$row->metadesc); ?></textarea>
				</td>
			</tr>
			<tr>
				<td  valign="top">
					<label for="metakey">
						<?php echo JText::_( 'Keywords' ); ?>:
					</label>
				</td>
				<td>
					<textarea rows="5" cols="50" style="width:500px; height:50px" class="inputbox" id="metakey" name="metakey"><?php echo str_replace('&','&amp;',$row->metakey); ?></textarea>
				</td>
			</tr>
			</table>
			
		<?php
		$tabs->endTab();
		$tabs->endPane();
		?>

		<input type="hidden" name="images" value="" />
		<input type="hidden" name="goodexit" value="0" />
		<input type="hidden" name="option" value="com_content" />
		<input type="hidden" name="Returnid" value="<?php echo $Returnid; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="version" value="<?php echo $row->version; ?>" />
		<input type="hidden" name="sectionid" value="<?php echo $row->sectionid; ?>" />
		<input type="hidden" name="created_by" value="<?php echo $row->created_by; ?>" />
		<input type="hidden" name="referer" value="<?php echo ampReplace( @$_SERVER['HTTP_REFERER'] ); ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}

	/**
	 * Writes Email form for sending a content item link to a friend
	 * 
	 * @param int 		$uid 		Content item id
	 * @param string 	$title 		Content item title
	 * @param string 	$template 	The current template
	 * @return void
	 * @since 1.0
	 */
	function emailForm($uid, $title, $template = '') 
	{
		global $mainframe;

		$mainframe->setPageTitle($title);
		$mainframe->addCustomHeadTag('<link rel="stylesheet" href="templates/'.$template.'/css/template_css.css" type="text/css" />');
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton() {
			var form = document.frontendForm;
			// do field validation
			if (form.email.value == "" || form.youremail.value == "") {
				alert( "<?php echo JText::_( 'EMAIL_ERR_NOINFO', true ); ?>" );
				return false;
			}
			return true;
		}
		</script>

		<form action="index2.php?option=com_content&amp;task=emailsend" name="frontendForm" method="post" onsubmit="return submitbutton();">
		<table cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td colspan="2">
				<?php echo JText::_( 'Email this to a friend.' ); ?>
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td width="130">
				<?php echo JText::_( 'Your friend`s Email' ); ?>:
			</td>
			<td>
				<input type="text" name="email" class="inputbox" size="25" />
			</td>
		</tr>
		<tr height="27">
			<td>
				<?php echo JText::_( 'Your Name' ); ?>:
			</td>
			<td>
				<input type="text" name="yourname" class="inputbox" size="25" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Your Email' ); ?>:
			</td>
			<td>
				<input type="text" name="youremail" class="inputbox" size="25" />
			</td>
		</tr>
		<tr>
			<td>
				&nbsp;<?php echo JText::_( 'Message subject' ); ?>:
			</td>
			<td>
				<input type="text" name="subject" class="inputbox" maxlength="100" size="40" />
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="submit" name="submit" class="button" value="<?php echo JText::_( 'Send email' ); ?>" />
				&nbsp;&nbsp;
				<input type="button" name="cancel" value="<?php echo JText::_( 'Cancel' ); ?>" class="button" onclick="window.close();" />
			</td>
		</tr>
		</table>

		<input type="hidden" name="id" value="<?php echo $uid; ?>" />
		<input type="hidden" name="<?php echo mosHash( 'validate' );?>" value="1" />
		</form>
		<?php


	}

	/**
	 * Writes Email sent popup
	 * 
	 * @param string $to 		Email recipient
	 * @param string $template 	The current template
	 * @return void
	 * @since 1.0
	 */
	function emailSent($to, $template = '') 
	{
		global $mainframe;

		$mainframe->addCustomHeadTag('<link rel="stylesheet" href="templates/'.$template.'/css/template_css.css" type="text/css" />');
		?>
		<span class="contentheading">
			<?php echo JText::_( 'This item has been sent to' )." $to";?>
		</span> 
		<br />
		<br />
		<br />
		<a href='javascript:window.close();'>
			<span class="small"><?php echo JText::_( 'Close Window' );?></span></a>
		<?php
	}

	/**
	 * Method to show an empty container if there is no data to display
	 * 
	 * @static
	 * @param string $msg The message to show
	 * @return void
	 * @since 1.5
	 */
	function emptyContainer($msg) {
		echo '<p><div align="center">'.$msg.'</div></p>';
	}
	
	/**
	 * Writes a user input error message and if javascript is enabled goes back
	 * to the previous screen to try again.
	 * 
	 * @param string $msg The error message to display
	 * @return void
	 * @since 1.5
	 */
	function userInputError($msg) {
		josErrorAlert($msg);
	}
}
?>