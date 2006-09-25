<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport( 'joomla.application.component.view');

/**
 * HTML Article View class for the Content component
 *
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class ContentViewArticle extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $Itemid;

		$user		=& JFactory::getUser();
		$document   =& JFactory::getDocument();
		$dispatcher	=& JEventDispatcher::getInstance();
		$pathway    =& $mainframe->getPathWay();

		// Initialize variables
		$article	=& $this->get('Article');
		$params		=& $article->parameters;

		if ($article->id == 0)
		{
			$id = JRequest::getVar( 'id' );
			return JError::raiseError( 404, JText::sprintf( 'Article #%d not found', $id ) );
		}

		$linkOn   = null;
		$linkText = null;

		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		// Handle BreadCrumbs

		if (!empty ($Itemid))
		{
			// Section
			if (!empty ($article->section)) {
				$pathway->addItem($article->section, sefRelToAbs('index.php?option=com_content&amp;task=section&amp;id='.$article->sectionid.'&amp;Itemid='.$Itemid));
			}
			// Category
			if (!empty ($article->category)) {
				$pathway->addItem($article->category, sefRelToAbs('index.php?option=com_content&amp;task=category&amp;sectionid='.$article->sectionid.'&amp;id='.$article->catid.'&amp;Itemid='.$Itemid));
			}
		}
		// Article
		$pathway->addItem($article->title, '');

		// Handle Page Title
		$document->setTitle($article->title);

		// Handle metadata
		$document->setDescription( $article->metadesc );
		$document->setMetadata('keywords', $article->metakey);

		// If there is a pagebreak heading or title, add it to the page title
		if (isset ($article->page_title)) {
			$document->setTitle($article->title.' '.$article->page_title);
		}

		// Create a user access object for the current user
		$access = new stdClass();
		$access->canEdit	= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn	= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish	= $user->authorize('action', 'publish', 'content', 'all');

		// Process the content plugins
		JPluginHelper::importPlugin('content');
		$results = $dispatcher->trigger('onPrepareContent', array (& $article, & $params, $limitstart));

		if ($params->get('readmore') || $params->get('link_titles'))
		{
			if ($params->get('intro_only'))
			{
				// Check to see if the user has access to view the full article
				if ($article->access <= $user->get('gid'))
				{
					$linkOn = sefRelToAbs("index.php?option=com_content&amp;task=view&amp;id=".$article->id."&amp;Itemid=".$Itemid);

					if (@$article->readmore) {
						// text for the readmore link
						$linkText = JText::_('Read more...');
					}
				}
				else
				{
					$linkOn = sefRelToAbs("index.php?option=com_registration&amp;task=register");

					if (@$article->readmore) {
						// text for the readmore link if accessible only if registered
						$linkText = JText::_('Register to read more...');
					}
				}
			}
		}

		if (intval($article->modified) != 0) {
			$article->modified = mosHTML::Date($article->modified);
		}

		if (intval($article->created) != 0) {
			$article->created = mosHTML::Date($article->created);
		}

		$article->readmore_link = $linkOn;
		$article->readmore_text = $linkText;

		$article->event = new stdClass();
		$results = $dispatcher->trigger('onAfterDisplayTitle', array ($article, &$params, $limitstart));
		$article->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onBeforeDisplayContent', array (& $article, & $params, $limitstart));
		$article->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onAfterDisplayContent', array (& $article, & $params, $limitstart));
		$article->event->afterDisplayContent = trim(implode("\n", $results));

		$this->assignRef('article', $article);
		$this->assignRef('params' , $params);
		$this->assignRef('user'   , $user);
		$this->assignRef('access' , $access);

		parent::display($tpl);
	}

	function getIcon($type, $attribs = array())
	{
		 global $Itemid, $mainframe;

		$url  = '';
		$text = '';

		$article = &$this->article;

		switch($type)
		{
			case 'pdf' :
			{
				$url   = 'index2.php?option=com_content&amp;view=article&amp;id='.$article->id.'&amp;format=pdf';
				$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

				// checks template image directory for image, if non found default are loaded
				if ($this->params->get('icons')) {
					$text = mosAdminMenus::ImageCheck('pdf_button.png', '/images/M_images/', NULL, NULL, JText::_('PDF'), JText::_('PDF'));
				} else {
					$text = JText::_('PDF').'&nbsp;';
				}

				$attribs['title']   = '"'.JText::_( 'PDF' ).'"';
				$attribs['onclick'] = "\"window.open('".$url."','win2','".$status."'); return false;\"";

			} break;

			case 'print' :
			{
				$url    = 'index2.php?option=com_content&amp;task=view&amp;id='.$article->id.'&amp;Itemid='.$Itemid.'&amp;pop=1&amp;page='.@ $this->request->limitstart;
				$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

				// checks template image directory for image, if non found default are loaded
				if ( $this->params->get( 'icons' ) ) {
					$text = mosAdminMenus::ImageCheck( 'printButton.png', '/images/M_images/', NULL, NULL, JText::_( 'Print' ), JText::_( 'Print' ) );
				} else {
					$text = JText::_( 'ICON_SEP' ) .'&nbsp;'. JText::_( 'Print' ) .'&nbsp;'. JText::_( 'ICON_SEP' );
				}

				$attribs['title']   = '"'.JText::_( 'Print' ).'"';
				$attribs['onclick'] = "\"window.open('".$url."','win2','".$status."'); return false;\"";

			} break;

			case 'email' :
			{
				$url   = 'index2.php?option=com_mailto&amp;link='.urlencode( JRequest::getURI());
				$status = 'width=400,height=300,menubar=yes,resizable=yes';

				if ($this->params->get('icons')) 	{
					$text = mosAdminMenus::ImageCheck('emailButton.png', '/images/M_images/', NULL, NULL, JText::_('Email'), JText::_('Email'));
				} else {
					$text = '&nbsp;'.JText::_('Email');
				}
				
				$attribs['title']   = '"'.JText::_( 'Email ' ).'"';
				$attribs['onclick'] = "\"window.open('".$url."','win2','".$status."'); return false;\"";
				
			} break;

			case 'edit' :
			{
				if ($this->params->get('popup')) {
					return;
				}
				if ($article->state < 0) {
					return;
				}
				if (!$this->access->canEdit && !($this->access->canEditOwn && $article->created_by == $this->user->get('id'))) {
					return;
				}

				mosCommonHTML::loadOverlib();

				$url = 'index.php?option=com_content&amp;task=edit&amp;id='.$article->id.'&amp;Itemid='.$Itemid.'&amp;Returnid='.$Itemid;
				$text = mosAdminMenus::ImageCheck('edit.png', '/images/M_images/', NULL, NULL, JText::_('Edit'), JText::_('Edit'). $article->id );

				if ($article->state == 0) {
					$overlib = JText::_('Unpublished');
				} else {
					$overlib = JText::_('Published');
				}
				$date = mosHTML::Date($article->created);
				$author = $article->created_by_alias ? $article->created_by_alias : $article->author;

				$overlib .= '<br />';
				$overlib .= $article->groups;
				$overlib .= '<br />';
				$overlib .= $date;
				$overlib .= '<br />';
				$overlib .= $author;

				$attribs['onmouseover'] = "\"return overlib('".$overlib."', CAPTION, '".JText::_( 'Edit Item' )."', BELOW, RIGHT)\"";
				$attribs['onmouseover'] = "\"return nd();\"";

			} break;
		}


		return mosHTML::Link($url, $text, $attribs);
	}

	function edit()
	{
		global $mainframe, $Itemid;

		// Initialize variables
		$document =& JFactory::getDocument();
		$user	  =& JFactory::getUser();

		// Make sure you are logged in
		if ($user->get('gid') < 1) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		$pathway =& $mainframe->getPathWay();

		// At some point in the future this will come from a request object
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		$returnid	= JRequest::getVar('Returnid', $Itemid, '', 'int');

		// Add the Calendar includes to the document <head> section
		$document->addStyleSheet('includes/js/calendar/calendar-mos.css');
		$document->addScript('includes/js/calendar/calendar_mini.js');
		$document->addScript('includes/js/calendar/lang/calendar-en.js');

		// Get the article from the model
		$article	=& $this->get('Article');
		$params		= $article->parameters;

		$isNew = ($article->id < 1);

		if ($isNew)
		{
			// TODO: Do we allow non-sectioned articles from the frontend??
			$article->sectionid = JRequest::getVar('sectionid', 0, '', 'int');
		}

		// Get the lists
		$lists = $this->_buildEditLists();

		// Load the JEditor object
		$editor =& JFactory::getEditor();

		// Ensure the row data is safe html
		mosMakeHtmlSafe($article);

		// Build the page title string
		$title = $article->id ? JText::_('Edit') : JText::_('New');

		// Set page title
		$document->setTitle($title);

		// Add pathway item
		$pathway->addItem($title, '');

		// Unify the introtext and fulltext fields and separated the fields by the {readmore} tag
		if (JString::strlen($article->fulltext) > 1) {
			$article->text = $article->introtext."<hr id=\"system-readmore\" />".$article->fulltext;
		} else {
			$article->text = $article->introtext;
		}

		$this->set('returnid', $returnid);
		$this->set('article' , $article);
		$this->set('params'  , $params);
		$this->set('lists'   , $lists);
		$this->set('editor'  , $editor);
		$this->set('user'    , $user);

		$this->display();
	}

	function _buildEditLists()
	{
		// Get the article and database connector from the model
		$article = & $this->get('Article');
		$db 	 = & JFactory::getDBO();

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
}
?>