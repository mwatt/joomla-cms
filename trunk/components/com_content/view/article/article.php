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

/**
 * HTML Article View class for the Content component
 *
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class JContentViewArticle extends JView
{
	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	var $_viewName = 'Article';

	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	function display()
	{
		$document	= &$this->getDocument();
		switch ($document->getType())
		{
			case 'pdf':
				$this->displayPdf();
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
		// Initialize variables
		$app	= & $this->getApplication();
		$user	= & $app->getUser();
		$menus	= JMenu::getInstance();
		$menu	= &$menus->getCurrent();
		$Itemid	= $menu->id;
		$linkOn = null;
		$linkText = null;

		// At some point in the future this will be in a request object
		$page	= JRequest::getVar('limitstart', 0, '', 'int');
		$noJS	= JRequest::getVar('hide_js', 0, '', 'int');
		$type	= JRequest::getVar('format', 'html');

		// Get the article from the model
		$article	= & $this->get('Article');
		$params		= & $article->parameters;

		// Create a user access object for the current user
		$access = new stdClass();
		$access->canEdit	= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn	= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish	= $user->authorize('action', 'publish', 'content', 'all');

		// Handle BreadCrumbs
		$breadcrumbs = & $app->getPathWay();

		if (!empty ($Itemid)) {
			// Section
			if (!empty ($article->section)) {
				$breadcrumbs->addItem($article->section, sefRelToAbs('index.php?option=com_content&amp;task=section&amp;id='.$article->sectionid.'&amp;Itemid='.$Itemid));
			}
			// Category
			if (!empty ($article->category)) {
				$breadcrumbs->addItem($article->category, sefRelToAbs('index.php?option=com_content&amp;task=category&amp;sectionid='.$article->sectionid.'&amp;id='.$article->catid.'&amp;Itemid='.$Itemid));
			}
		}

		// Item
		$breadcrumbs->addItem($article->title, '');
		// Handle Page Title
		$doc = & $app->getDocument();
		$doc->setTitle($article->title);

		// Handle metadata
		$doc->setDescription( $article->metadesc );
		$doc->setMetadata('keywords', $article->metakey);

		// If there is a pagebreak heading or title, add it to the page title
		if (isset ($article->page_title)) {
			$doc->setTitle($article->title.' '.$article->page_title);
		}

		$cParams = &JComponentHelper::getControlParams();
		$template = JRequest::getVar( 'tpl', $cParams->get( 'template_name', 'article' ) );
		$template = preg_replace( '#\W#', '', $template );
		$tmplPath = dirname( __FILE__ ) . '/tmpl/' . $template . '.php';
		
		if (!file_exists( $tmplPath ))
		{
			$tmplPath = dirname( __FILE__ ) . '/tmpl/article.php';
		}

		require($tmplPath);
	}

	function edit()
	{
		// Initialize variables
		$app	= & $this->getApplication();
		$doc	= & $app->getDocument();
		$user	= & $app->getUser();
		$menus	= JMenu::getInstance();
		$menu	= &$menus->getCurrent();
		$Itemid	= $menu->id;

		// At some point in the future this will come from a request object
		$page		= JRequest::getVar('limitstart', 0, '', 'int');
		$noJS		= JRequest::getVar('hide_js', 0, '', 'int');
		$Returnid	= JRequest::getVar('Returnid', $Itemid, '', 'int');

		// Add the Calendar includes to the document <head> section
		$doc->addStyleSheet('includes/js/calendar/calendar-mos.css');
		$doc->addScript('includes/js/calendar/calendar_mini.js');
		$doc->addScript('includes/js/calendar/lang/calendar-en.js');

		// Get the article from the model
		$article	= & $this->get('Article');
		$params		= $article->parameters;

		// Get the lists
		$lists = $this->_buildEditLists();

		// Load the JEditor object
		jimport('joomla.presentation.editor');
		$editor = & JEditor::getInstance();

		// Load the mosTabs object
		$tabs = new mosTabs(0);

		// Load the Overlib library
		mosCommonHTML::loadOverlib();

		// Ensure the row data is safe html
		mosMakeHtmlSafe($article);

		// Build the page title string
		$title = $article->id ? JText::_('Edit') : JText::_('New');

		// Set page title
		$doc->setTitle($title);

		// Add pathway item
		$breadcrumbs = & $app->getPathway();
		$breadcrumbs->addItem($title, '');

		?>
	  	<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				window.top.document.popup.hide();
				return;
			}

			try {
				form.onsubmit();
			}
			catch(e){}
			// do field validation
			if (form.title.value == "") {
				alert ( "<?php echo JText::_( 'Content item must have a title', true ); ?>" );
			} else if (parseInt('<?php echo $article->sectionid;?>')) {
				// for content items
				if (getSelectedValue('adminForm','catid') < 1) {
					alert ( "<?php echo JText::_( 'Please select a category', true ); ?>" );
				} else {
		<?php
		// JavaScript for extracting editor text
		echo $editor->save( 'text' );
		?>
					submitform(pressbutton);
					window.top.document.popup.hide();
					delay(750);
					window.top.location.reload(true);
				}
			}
		}
		</script>
		<?php
		// Build overlib text
		$docinfo = '<table><tr><td>';
		$docinfo .= '<strong>'.JText::_('Expiry Date').':</strong> ';
		$docinfo .= '</td><td>';
		$docinfo .= $article->publish_down;
		$docinfo .= '</td></tr><tr><td>';
		$docinfo .= '<strong>'.JText::_('Version').':</strong> ';
		$docinfo .= '</td><td>';
		$docinfo .= $article->version;
		$docinfo .= '</td></tr><tr><td>';
		$docinfo .= '<strong>'.JText::_('Created').':</strong> ';
		$docinfo .= '</td><td>';
		$docinfo .= $article->created;
		$docinfo .= '</td></tr><tr><td>';
		$docinfo .= '<strong>'.JText::_('Last Modified').':</strong> ';
		$docinfo .= '</td><td>';
		$docinfo .= $article->modified;
		$docinfo .= '</td></tr><tr><td>';
		$docinfo .= '<strong>'.JText::_('Hits').':</strong> ';
		$docinfo .= '</td><td>';
		$docinfo .= $article->hits;
		$docinfo .= '</td></tr></table>';
		?>
		<form action="index.php" method="post" name="adminForm" onSubmit="javascript:setgood();">

		<table class="adminform" width="100%">
		<tr>
			<td>
				<div style="float: left;">
					<label for="title">
						<?php echo JText::_( 'Title' ); ?>:
					</label>
					<input class="inputbox" type="text" id="title" name="title" size="50" maxlength="100" value="<?php echo $article->title; ?>" />
					&nbsp;&nbsp;&nbsp;
					<?php /*echo mosToolTip('<table>'.$docinfo.'</table>', JText::_( 'Item Information', true ), '', '', '<strong>['.JText::_( 'Info', true ).']</strong>');*/ ?>
				</div>
				<div style="float: right;">
				<button type="button" onclick="javascript:submitbutton('save')">
					<?php echo JText::_('Save') ?>
				</button>
				<button type="button" onclick="javascript:submitbutton('cancel')" />
					<?php echo JText::_('Cancel') ?>
				</button>
				</div>
			</td>
		</tr>
		</table>

		<!-- Begin Article Parameters Section -->
		<!-- Images Tab -->
		<?php
		$title = JText::_('Editor');
		$tabs->startPane('content-pane');
		$tabs->startTab($title, 'editor-page');

		/*
		 * We need to unify the introtext and fulltext fields and have the
		 * fields separated by the {readmore} tag, so lets do that now.
		 */
		if (JString::strlen($article->fulltext) > 1) {
			$article->text = $article->introtext.'{readmore}'.$article->fulltext;
		} else {
			$article->text = $article->introtext;
		}
		// Display the editor
		// arguments (areaname, content, hidden field, width, height, rows, cols)
		echo $editor->display('text', $article->text, '655', '400', '70', '15');
		echo $editor->getButtons('text');
		?>

		<!-- Publishing Tab -->
		<?php
		$title = JText::_('Publishing');
		$tabs->endTab();
		$tabs->startTab($title, 'publish-page');
		?>

			<table class="adminform">
		<?php

		// If the document is in a section display the section and category dropdown
		if ($article->sectionid) {
		?>
			<tr>
				<td>
					<label for="catid">
						<?php echo JText::_( 'Section' ); ?>:
					</label>
				</td>
				<td>
					<strong>
						<?php echo $article->section;?>
					</strong>
				</td>
			</tr>
			<tr>
				<td>
					<label for="catid">
						<?php echo JText::_( 'Category' ); ?>:
					</label>
				</td>
				<td>
					<?php echo $lists['catid']; ?>
				</td>
			</tr>
			<?php
		}

		if ($user->authorize('action', 'publish', 'content', 'all')) {
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
					<input type="text" id="created_by_alias" name="created_by_alias" size="50" maxlength="100" value="<?php echo $article->created_by_alias; ?>" class="inputbox" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="publish_up">
						<?php echo JText::_( 'Start Publishing' ); ?>:
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" name="publish_up" id="publish_up" size="25" maxlength="19" value="<?php echo $article->publish_up; ?>" />
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
					<input class="inputbox" type="text" name="publish_down" id="publish_down" size="25" maxlength="19" value="<?php echo $article->publish_down; ?>" />
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

		<!-- Metadata Tab -->
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
					<textarea rows="5" cols="50" style="width:500px; height:120px" class="inputbox" id="metadesc" name="metadesc"><?php echo str_replace('&','&amp;',$article->metadesc); ?></textarea>
				</td>
			</tr>
			<tr>
				<td  valign="top">
					<label for="metakey">
						<?php echo JText::_( 'Keywords' ); ?>:
					</label>
				</td>
				<td>
					<textarea rows="5" cols="50" style="width:500px; height:50px" class="inputbox" id="metakey" name="metakey"><?php echo str_replace('&','&amp;',$article->metakey); ?></textarea>
				</td>
			</tr>
			</table>

		<!-- End Article Parameters Section -->
		<?php
		$tabs->endTab();
		$tabs->endPane();
		?>

		<input type="hidden" name="option" value="com_content" />
		<input type="hidden" name="Returnid" value="<?php echo $Returnid; ?>" />
		<input type="hidden" name="id" value="<?php echo $article->id; ?>" />
		<input type="hidden" name="version" value="<?php echo $article->version; ?>" />
		<input type="hidden" name="sectionid" value="<?php echo $article->sectionid; ?>" />
		<input type="hidden" name="created_by" value="<?php echo $article->created_by; ?>" />
		<input type="hidden" name="referer" value="<?php echo ampReplace( @$_SERVER['HTTP_REFERER'] ); ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}

	function _buildEditLists()
	{
		// Get the article and database connector from the model
		$article = & $this->get('Article');
		$db 	 = & $this->get('DBO');

		// Select List: Categories
		$lists['catid'] = mosAdminMenus::ComponentCategory('catid', $article->sectionid, intval($article->catid));

		// Select List: Category Ordering
		$query = "SELECT ordering AS value, title AS text"."\n FROM #__content"."\n WHERE catid = $article->catid"."\n ORDER BY ordering";
		$lists['ordering'] = mosAdminMenus::SpecificOrdering($article, $article->id, $query, 1);

		// Radio Buttons: Should the article be published
		$lists['state'] = mosHTML::yesnoradioList('state', '', $article->state);

		// Radio Buttons: Should the article be added to the frontpage
		$query = "SELECT content_id"."\n FROM #__content_frontpage"."\n WHERE content_id = $article->id";
		$db->setQuery($query);
		$article->frontpage = $db->loadResult();

		$lists['frontpage'] = mosHTML::yesnoradioList('frontpage', '', (boolean) $article->frontpage);

		// Select List: Group Access
		$lists['access'] = mosAdminMenus::Access($article);

		return $lists;
	}

	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	function displayPdf()
	{
		global $l;

		// Initialize some variables
		$app		= & $this->getApplication();
		$user		= & $app->getUser();
		$menus		= JMenu::getInstance();
		$menu		= &$menus->getCurrent();
		$article	= & $this->get( 'Article' );
		//$Itemid	= $menu->id;
		$params 	= & $article->parameters;

		$params->def('introtext', 1);
		$params->set('intro_only', 0);

		// show/hides the intro text
		if ($params->get('introtext')) {
			$article->text = $article->introtext. ($params->get('intro_only') ? '' : chr(13).chr(13).$article->fulltext);
		} else {
			$article->text = $article->fulltext;
		}

		// process the new plugins
		JPluginHelper::importPlugin('content');
		$app->triggerEvent('onPrepareContent', array (& $article, & $params, 0));
		//	$text = trim(implode("\n", $results));
		//				$results = $app->triggerEvent('onAfterDisplayTitle', array (& $article, & $params, $page));
		//			$text .= trim(implode("\n", $results));
		//
		//		$onBeforeDisplayContent = $app->triggerEvent('onBeforeDisplayContent', array (& $article, & $params, 0));
		//		$text .= trim(implode("\n", $onBeforeDisplayContent));

		//create new PDF document (document units are set by default to millimeters)
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);

		// set document information
		$pdf->SetCreator("Joomla!");
		$pdf->SetTitle("Joomla generated PDF");
		$pdf->SetSubject($article->title);
		$pdf->SetKeywords($article->metakey);

		// prepare header lines
		$headerText = $this->_getHeaderText($article, $params);

		$pdf->SetHeaderData('', 0, $article->title, $headerText);

		//set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); //set image scale factor

		$pdf->setHeaderFont(Array (PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array (PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		$pdf->setLanguageArray($l); //set language items

		//initialize document
		$pdf->AliasNbPages();

		$pdf->AddPage();

		//	$pdf->WriteHTML($article->introtext ."\n". $article->fulltext, true);
		$pdf->WriteHTML($article->text, true);

		//Close and output PDF document
		$pdf->Output("joomla.pdf", "I");
	}

	function _getHeaderText(& $article, & $params)
	{
		// Initialize some variables
		$db = & $this->get('DBO');
		$text = '';

		if ($params->get('author')) {
			// Display Author name
			if ($article->usertype == 'administrator' || $article->usertype == 'superadministrator') {
				$text .= "\n";
				$text .= JText::_('Written by').' '. ($article->created_by_alias ? $article->created_by_alias : $article->author);
			} else {
				$text .= "\n";
				$text .= JText::_('Contributed by').' '. ($article->created_by_alias ? $article->created_by_alias : $article->author);
			}
		}

		if ($params->get('createdate') && $params->get('author')) {
			// Display Separator
			$text .= "\n";
		}

		if ($params->get('createdate')) {
			// Display Created Date
			if (intval($article->created)) {
				$create_date = mosFormatDate($article->created);
				$text .= $create_date;
			}
		}

		if ($params->get('modifydate') && ($params->get('author') || $params->get('createdate'))) {
			// Display Separator
			$text .= "\n";
		}

		if ($params->get('modifydate')) {
			// Display Modified Date
			if (intval($article->modified)) {
				$mod_date = mosFormatDate($article->modified);
				$text .= JText::_('Last Updated').' '.$mod_date;
			}
		}
		//	$text .= "\n\n";
		return $text;
	}
}
?>