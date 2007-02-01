<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport('joomla.application.component.controller');

/**
 * Weblinks Component Controller
 *
 * @package		Joomla
 * @subpackage	Weblinks
 * @since 1.5
 */
class WeblinksController extends JController
{
	/**
	 * Method to show a weblinks view
	 *
	 * @access	public
	 * @since	1.5
	 */
	function display()
	{
		//update the hit count for the weblink
		if(JRequest::getVar('view') == 'weblink') {
			$model =& $this->getModel('weblink');
			$model->hit();
		}

		parent::display();
	}
}

?>