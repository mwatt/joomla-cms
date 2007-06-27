<?php
/**
 * @version		$Id: view.html.php 7399 2007-05-14 04:10:09Z eddieajau $
 * @package		Joomla
 * @subpackage	User
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Users component
 *
 * @author		Rob Schley <rob.schley@joomla.org>
 * @package		Joomla
 * @subpackage	User
 * @since		1.5
 */
class UserViewReset extends JView
{
	/**
	 * Registry namespace prefix
	 *
	 * @var	string
	 */
	var $_namespace	= 'com_user.reset.';

	/**
	 * Display function
	 *
	 * @since 1.5
	 */
	function display($tpl = null)
	{
		jimport('joomla.html.html');

		global $mainframe;

		// Get the document object
		$doc = &JFactory::getDocument();
		$doc->addScript('includes/js/mootools.js');
		$doc->addScript('includes/js/joomla/validate.js');

		// Add the tooltip behavior
		JHTML::_('behavior.tooltip');

		// Get the layout
		$layout	= $this->getLayout();

		if ($layout == 'complete')
		{
			$username	= $mainframe->getUserState($this->_namespace.'id');
			$token		= $mainframe->getUserState($this->_namespace.'token');

			if (is_null($username) || is_null($token))
			{
				$mainframe->redirect('index.php?option=com_user&view=reset');
			}
		}

		parent::display($tpl);
	}
}
