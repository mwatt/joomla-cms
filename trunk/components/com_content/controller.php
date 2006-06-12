<?php
/**
 * @version $Id: content.php 3912 2006-06-06 06:12:38Z eddieajau $
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

jimport('joomla.application.controller');

/**
 * Content Component Controller
 *
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class JContentController extends JController
{
	/**
	 * Method to show a section in list format
	 *
	 * @access	public
	 * @since	1.5
	 */
	function section000()
	{
		$this->setViewName( 'section', 'com_content', 'JContentView' );

		$mParams = &JComponentHelper::getMenuParams();

		// Set some parameter defaults
		// TODO: probably this needs to move into the view class
		$mParams->def('page_title', 			1);
		$mParams->def('pageclass_sfx', 		'');
		$mParams->def('other_cat_section', 	1);
		$mParams->def('empty_cat_section', 	0);
		$mParams->def('other_cat', 			1);
		$mParams->def('empty_cat', 			0);
		$mParams->def('cat_items', 			1);
		$mParams->def('cat_description', 	1);
		$mParams->def('back_button', 		$this->_app->getCfg('back_button'));
		$mParams->def('pageclass_sfx', 		'');

		// Get the view
		$view = & $this->getView();

		// Get/Create the model
		$model = & $this->getModel('Section', 'JContentModel');

		// Push the model into the view (as default)
		$view->setModel($model, true);
		// Display the view
		$view->display();

//		$cache = & JFactory::getCache('com_content', 'output');
//		if (!$cache->start(md5($id.'section'.$Itemid), 'com_content')) {
//			JViewContentHTML::showSection( $model );
//			$cache->end();
//		}
	}

	/**
	 * Method to show a category in table format
	 *
	 * @access	public
	 * @since	1.5
	 */
	function category000()
	{
		$this->setViewName( 'category', 'com_content', 'JContentView' );

		$mParams = &JComponentHelper::getMenuParams();

		// Set some parameter defaults
		// TODO: probably this needs to move into the view class
		$mParams->def('page_title',		1);
		$mParams->def('title',			1);
		$mParams->def('hits',			$this->_app->getCfg('hits'));
		$mParams->def('author',			!$this->_app->getCfg('hideAuthor'));
		$mParams->def('date',			!$this->_app->getCfg('hideCreateDate'));
		$mParams->def('date_format',		JText::_('DATE_FORMAT_LC'));
		$mParams->def('navigation',		2);
		$mParams->def('display',			1);
		$mParams->def('display_num',		$this->_app->getCfg('list_limit'));
		$mParams->def('other_cat',		1);
		$mParams->def('empty_cat',		0);
		$mParams->def('cat_items',		1);
		$mParams->def('cat_description',	0);
		$mParams->def('back_button',		$this->_app->getCfg('back_button'));
		$mParams->def('pageclass_sfx',	'');
		$mParams->def('headings',		1);
		$mParams->def('filter',			1);
		$mParams->def('filter_type',		'title');

		// Get the view
		$view = & $this->getView();

		// Get/Create the model
		$model = & $this->getModel('Category', 'JContentModel');

		// Push the model into the view (as default)
		$view->setModel($model, true);
		// Display the view
		$view->display();
	}

	/**
	 * Method to show a section as a blog
	 *
	 * @access	public
	 * @since	1.5
	 */
	function blogsection000()	// view=section&tpl=blog
	{
		$this->setViewName( 'blog', 'com_content', 'JContentView' );

		// Get the view
		$view = & $this->getView();

		// Get/Create the model
		$model = & $this->getModel('Section', 'JContentModel');

		// Push the model into the view (as default)
		$view->setModel($model, true);
		// Display the view
		$view->display();
	}

	/**
	 * Method to show a category as a blog
	 *
	 * @access	public
	 * @since	1.5
	 */
	function blogcategory000()	// view=category&tpl=blog
	{
		$this->setViewName( 'blog', 'com_content', 'JContentView' );

		// Get the view
		$view = & $this->getView();

		// Get/Create the model
		$model = & $this->getModel('Category', 'JContentModel');

		// Push the model into the view (as default)
		$view->setModel($model, true);
		// Display the view
		$view->display();
	}

	/**
	 * Method to show a section as an archive
	 *
	 * @access	public
	 * @since	1.5
	 */
	function archivesection()
	{
		$this->setViewName( 'archive', 'com_content', 'JContentView' );

		// Get the view
		$view = & $this->getView();

		// Get/Create the model
		$model = & $this->getModel('Section', 'JContentModel');

		// Push the model into the view (as default)
		$view->setModel($model, true);
		// Display the view
		$view->display();
	}

	/**
	 * Method to show a category as an archive
	 *
	 * @access	public
	 * @since	1.5
	 */
	function archivecategory()
	{
		$this->setViewName( 'archive', 'com_content', 'JContentView' );

		// Get the view
		$view = & $this->getView();

		// Get/Create the model
		$model = & $this->getModel('Category', 'JContentModel');

		// Push the model into the view (as default)
		$view->setModel($model, true);
		// Display the view
		$view->display();
	}

	/**
	 * Method to show an article as the main page display
	 *
	 * @access	public
	 * @since	1.5
	 */
	function display()
	{
		// TODO: What happen if the item doesn't exist?

		// Get control information from the request
		$cParams	= JComponentHelper::getControlParams();
		$viewName	= JRequest::getVar('view', $cParams->get( 'view_name' ));
		$modelName	= JRequest::getVar('model', $cParams->get( 'model_name', 'article' ));

		// interceptors to support legacy urls
		switch( $this->getTask())
		{
			//index.php?option=com_content&task=blogsection&id=0&Itemid=4
			case 'blogsection':
				$viewName = 'section';
				JRequest::setVar( 'tpl', 'blog' );
				break;
			case 'section':
				$viewName = 'section';
				JRequest::setVar( 'tpl', 'list' );
				break;
			case 'category':
				$viewName = 'category';
				JRequest::setVar( 'tpl', 'table' );
				break;
			case 'blogcategory':
				$viewName = 'section';
				JRequest::setVar( 'tpl', 'blog' );
				break;
			case 'view':
				$viewName = 'article';
				JRequest::setVar( 'tpl', 'article' );
				break;
		}

		// Create the view
		$this->setViewName( $viewName, 'com_content', 'JContentView' );
		$view = & $this->getView();

		// Get/Create the model
		$model = & $this->getModel($modelName, 'JContentModel');

		// Push the model into the view (as default)
		$view->setModel($model, true);

		// Display the view
		$view->display();
	}

	/**
	* Edits an article
	*
	* @access	public
	* @since	1.5
	*/
	function edit()
	{
		// Set the view name to article view
		$this->setViewName( 'article', 'com_content', 'JContentView' );

		// Create the view
		$view = & $this->getView();

		// Get/Create the model
		$model = & $this->getModel('Article', 'JContentModel');

		// Get the id of the article to display and set the model
		$id = JRequest::getVar('id', 0, '', 'int');
		$model->setId($id);

		// Push the model into the view (as default)
		$view->setModel($model, true);
		// Display the view
		$view->edit();
	}

	/**
	* Saves the content item an edit form submit
	*
	* @todo
	*/
	function save()
	{
		global $mainframe, $Itemid;

		/*
		 * Initialize variables
		 */
		$db			= & $mainframe->getDBO();
		$user		= & $mainframe->getUser();
		$nullDate	= $db->getNullDate();
		$task		= JRequest::getVar('task');

		/*
		 * Create a user access object for the user
		 */
		$access					= new stdClass();
		$access->canEdit		= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		$row = & JTable::getInstance('content', $db);
		if (!$row->bind($_POST))
		{
			JError::raiseError( 500, $row->getError());
		}

		$isNew = ($row->id < 1);
		if ($isNew)
		{
			// new record
			if (!($access->canEdit || $access->canEditOwn))
			{
				JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
			}
			$row->created 		= date('Y-m-d H:i:s');
			$row->created_by 	= $user->get('id');
		}
		else
		{
			// existing record
			if (!($access->canEdit || ($access->canEditOwn && $row->created_by == $user->get('id'))))
			{
				JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
			}
			$row->modified 		= date('Y-m-d H:i:s');
			$row->modified_by 	= $user->get('id');
		}
		
		/*
		* Append time if not added to publish date
		*/
		if (strlen(trim($row->publish_up)) <= 10) {
			$row->publish_up .= ' 00:00:00';
		}
		$row->publish_up = mosFormatDate($row->publish_up, '%Y-%m-%d %H:%M:%S', - $mainframe->getCfg('offset'));
		
		/*
		* Handle never unpublish date
		*/
		if (trim($row->publish_down) == 'Never' || trim( $row->publish_down ) == '') {
			$row->publish_down = $nullDate;
		} else {
			if (strlen(trim( $row->publish_down )) <= 10) {
				$row->publish_down .= ' 00:00:00';
			}
			$row->publish_down = mosFormatDate($row->publish_down, '%Y-%m-%d %H:%M:%S', - $mainframe->getCfg('offset'));
		}		

		$row->title = ampReplace($row->title);

		// Publishing state hardening for Authors
		if (!$access->canPublish)
		{
			if ($isNew)
			{
				// For new items - author is not allowed to publish - prevent them from doing so
				$row->state = 0;
			}
			else
			{
				// For existing items keep existing state - author is not allowed to change status
				$query = "SELECT state" .
						"\n FROM #__content" .
						"\n WHERE id = $row->id";
				$db->setQuery($query);
				$state = $db->loadResult();

				if ($state)
				{
					$row->state = 1;
				}
				else
				{
					$row->state = 0;
				}
			}
		}

		// Prepare content  for save
		JContentHelper::saveContentPrep($row);

		if (!$row->check())
		{
			JError::raiseError( 500, $row->getError());
		}
		$row->version++;
		if (!$row->store())
		{
			JError::raiseError( 500, $row->getError());
		}

		// manage frontpage items
		require_once (JApplicationHelper::getPath('class', 'com_frontpage'));
		$fp = new JTableFrontPage($db);

		if (JRequest::getVar('frontpage', false, '', 'boolean'))
		{

			// toggles go to first place
			if (!$fp->load($row->id))
			{
				// new entry
				$query = "INSERT INTO #__content_frontpage" .
						"\n VALUES ( $row->id, 1 )";
				$db->setQuery($query);
				if (!$db->query())
				{
					JError::raiseError( 500, $db->stderror());
				}
				$fp->ordering = 1;
			}
		}
		else
		{
			// no frontpage mask
			if (!$fp->delete($row->id))
			{
				$msg .= $fp->stderr();
			}
			$fp->ordering = 0;
		}
		$fp->reorder();

		$row->checkin();
		$row->reorder("catid = $row->catid");

		// gets section name of item
		$query = "SELECT s.title" .
				"\n FROM #__sections AS s" .
				"\n WHERE s.scope = 'content'" .
				"\n AND s.id = $row->sectionid";
		$db->setQuery($query);
		// gets category name of item
		$section = $db->loadResult();

		$query = "SELECT c.title" .
				"\n FROM #__categories AS c" .
				"\n WHERE c.id = $row->catid";
		$db->setQuery($query);
		$category = $db->loadResult();

		if ($isNew)
		{
			// messaging for new items
			require_once (JApplicationHelper::getPath('class', 'com_messages'));
			$query = "SELECT id" .
					"\n FROM #__users" .
					"\n WHERE sendEmail = 1";
			$db->setQuery($query);
			$users = $db->loadResultArray();
			foreach ($users as $user_id)
			{
				jimport('joomla.utilities.message');
				$msg = new JMessage($db);
				$msg->send($user->get('id'), $user_id, "New Item", sprintf(JText::_('ON_NEW_CONTENT'), $user->get('username'), $row->title, $section, $category));
			}
		}

		$msg = $isNew ? JText::_('THANK_SUB') : JText::_('Item successfully saved.');
		$msg = $user->get('usertype') == 'Publisher' ? JText::_('THANK_SUB') : $msg;
		switch ($task)
		{
			case 'apply' :
				$link = $_SERVER['HTTP_REFERER'];
				break;

			case 'apply_new' :
				$Itemid = JRequest::getVar('Returnid', $Itemid, 'post');
				$link = 'index.php?option=com_content&task=edit&id='.$row->id.'&Itemid='.$Itemid;
				break;

			case 'save' :
			default :
				$Itemid = JRequest::getVar('Returnid', '', 'post');
				if ($Itemid)
				{
					$link = 'index.php?option=com_content&task=view&id='.$row->id.'&Itemid='.$Itemid;
				}
				else
				{
					$link = JRequest::getVar('referer', '', 'post');
				}
				break;
		}
		josRedirect($link, $msg);
	}

	/**
	* Cancels an edit article operation
	*
	* @access	public
	* @since	1.5
	*/
	function cancel()
	{
		// Initialize some variables
		$db		= & $this->getDBO();
		$user	= & $this->_app->getUser();

		// At some point in the future these will be in a request object
		$Itemid	= JRequest::getVar('Returnid', '0', 'post');
		$referer	= JRequest::getVar('referer', '', 'post');

		// Get an article table object and bind post variabes to it [We don't need a full model here]
		$article = & JTable::getInstance('content', $db);
		$article->bind($_POST);

		if ($user->authorize('action', 'edit', 'content', 'all') || ($user->authorize('action', 'edit', 'content', 'own') && $article->created_by == $user->get('id'))) {
			$article->checkin();
		}

		// If the task was edit or cancel, we go back to the content item
		if ($this->_task == 'edit' || $this->_task == 'cancel') {
			$referer = 'index.php?option=com_content&task=view&id='.$article->id.'&Itemid='.$Itemid;
		}

		// If the task was not new, we go back to the referrer
		if ($referer && $article->id) {
			$this->setRedirect($referer);
		}
		else {
			$this->setRedirect('index.php');
		}
	}

	/**
	 * Shows the send email form for a content item
	 *
	 * @todo
	 * @since 1.0
	 */
	function emailform000()	// replaced by com_mailto
	{
		require_once (JApplicationHelper::getPath('front_html', 'com_content'));
		JViewContentHTML::emptyContainer( 'Temporarily Unavailable :: No need to report it broken ;)');
		return true;

		global $mainframe;

		/*
		 * Initialize variables
		 */
		$db		= & $mainframe->getDBO();
		$user	= & $mainframe->getUser();
		$uid		= JRequest::getVar('id', 0, '', 'int');

		/*
		 * Create a user access object for the user
		 */
		$access							= new stdClass();
		$access->canEdit			= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		$row = & JTable::getInstance('content', $db);
		$row->load($uid);

		if ($row->id === null || $row->access > $user->get('gid'))
		{
			JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
		}
		else
		{
			$query = "SELECT template" .
					"\n FROM #__templates_menu" .
					"\n WHERE client_id = 0" .
					"\n AND menuid = 0";
			$db->setQuery($query);
			$template = $db->loadResult();
			JViewContentHTML::emailForm($row->id, $row->title, $template);
		}

	}

	/**
	 * Builds and sends an email to a content item
	 *
	 * @access	public
	 * @since	1.5
	 */
	function emailsend000()	// replace by com_mailto
	{
		// Check to make sure that the validation variable was posted back
		$validate	= JRequest::getVar(mosHash('validate'), 0, 'post');
		if (!$validate) {
			JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
		}

		/*
		 * This obviously won't catch all attempts, but it does not hurt to make
		 * sure the request came from a client with a user agent string.
		 */
		if (!isset ($_SERVER['HTTP_USER_AGENT'])) {
			JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
		}

		/*
		 * This obviously won't catch all attempts either, but we ought to check
		 * to make sure that the request was posted as well.
		 */
		if (!$_SERVER['REQUEST_METHOD'] == 'POST') {
			JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
		}

		// An array of e-mail headers we do not want to allow as input
		$headers = array ('Content-Type:', 'MIME-Version:', 'Content-Transfer-Encoding:', 'bcc:', 'cc:');

		// An array of the input fields to scan for injected headers
		$fields = array ('email', 'yourname', 'youremail', 'subject',);

		/*
		 * Here is the meat and potatoes of the header injection test.  We
		 * iterate over the array of form input and check for header strings.
		 * If we fine one, send an unauthorized header and die.
		 */
		foreach ($fields as $field) {
			foreach ($headers as $header) {
				if (strpos($_POST[$field], $header) !== false) {
					JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
				}
			}
		}

		// Free up memory
		unset ($headers, $fields);

		// At some point tihs will all be in a request object
		$id				= JRequest::getVar('id', 0, '', 'int');
		$to				= JRequest::getVar('email', '', 'post');
		$from			= JRequest::getVar('youremail', $this->_app->getCfg('mailfrom'), 'post');
		$fromname	= JRequest::getVar('yourname', $this->_app->getCfg('fromname'), 'post');
		$subject		= JRequest::getVar('subject', sprintf(JText::_('Item sent by'), $fromname), 'post');

		jimport('joomla.utilities.mail');
		if (!JMailHelper::isEmailAddress($to) || !JMailHelper::isEmailAddress($from)) {
			JViewContentHTML::userInputError(JText::_('INALID_EMAIL_ADDRESS'));
			return false;
		}
		if (!JMailHelper::cleanAddress($to) || !JMailHelper::cleanAddress($from)) {
			JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
			return false;
		}

		$db = & $this->getDBO();
		$query = "SELECT template" .
				"\n FROM #__templates_menu" .
				"\n WHERE client_id = 0" .
				"\n AND menuid = 0";
		$db->setQuery($query);
		$template = $db->loadResult();

		// Get/Create the model
		$model = & $this->getModel('Article', 'JContentModel');

		// Send mail via the model
		$email = $model->sendEmail($to, $from, $fromname, $subject);

		if (!$email) {
			JViewContentHTML::userInputError(JText::_('EMAIL_ERR_NOINFO'));
		} else {
			JViewContentHTML::emailSent($email, $template);
		}
	}

	/**
	* Rates an article
	*
	* @access	public
	* @since	1.5
	*/
	function vote()
	{
		$url		= JRequest::getVar('url', '');
		$rating	= JRequest::getVar('user_rating', 0, '', 'int');
		$id		= JRequest::getVar('cid', 0, '', 'int');

		// Get/Create the model
		$model = & $this->getModel('Article', 'JContentModel');

		$model->setId($id);
		if ($model->storeVote($rating)) {
			$this->setRedirect($url, JText::_('Thanks for your vote!'));
		} else {
			$this->setRedirect($url, JText::_('You already voted for this poll today!'));
		}
	}

	/**
	 * Searches for an item by a key parameter
	 *
	 * @access	public
	 * @since	1.5
	 */
	function findkey()
	{
		/*
		 * Initialize variables
		 */
		$db			= & $this->getDBO();
		$keyref	= $db->getEscaped(JRequest::getVar('keyref'));

		$query = "SELECT id" .
				"\n FROM #__content" .
				"\n WHERE attribs LIKE '%keyref=$keyref%'";
		$db->setQuery($query);
		$id = $db->loadResult();
		if ($id > 0) {
			// Set the view name to article view
			$this->setViewName( 'article', 'com_content', 'JContentView' );

			// Create the view
			$view = & $this->getView();

			// Get/Create the model
			$model = & $this->getModel('Article', 'JContentModel');

			// Get the id of the article to display and set the model
			$id = JRequest::getVar('id', 0, '', 'int');
			$model->setId($id);

			// Push the model into the view (as default)
			$view->setModel($model, true);
			// Display the view
			$view->display();
		}
		else {
			JError::raiseError( 404, JText::_("Key Not Found") );
		}
	}
}

?>