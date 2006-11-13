<?php
/**
 * @version $Id: article.php 5379 2006-10-09 22:39:40Z Jinx $
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

jimport('joomla.application.component.model');

/**
 * Weblinks Component Weblink Model
 *
 * @author Johan Janssens <johan.janssens@joomla.org>
 * @package Joomla
 * @subpackage Weblinks
 * @since 1.5
 */
class WeblinksModelWeblink extends JModel
{
	/**
	 * Weblink id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Weblink data
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();

		global $Itemid;

		// Get the paramaters of the active menu item
		$params =& JSiteHelper::getMenuParams();

		$id = JRequest::getVar('id', $params->get( 'weblink_id', 0 ), '', 'int');
		$this->setId($id);

	}

	/**
	 * Method to set the weblink identifier
	 *
	 * @access	public
	 * @param	int Weblink identifier
	 */
	function setId($id)
	{
		// Set weblink id and wipe data
		$this->_id	    = $id;
		$this->_data = null;
	}

	/**
	 * Method to get a weblink
	 *
	 * @since 1.5
	 */
	function &getData()
	{
		$state =& $this->getState();
		
		// Load the weblink data
		if ($this->_loadData())
		{
			// Initialize some variables
			$user = &JFactory::getUser();

			// Make sure the category is published
			if (!$this->_data->published) {
				JError::raiseError(404, JText::_("Resource Not Found"));
				return false;
			}

			// Check to see if the category is published
			if (!$this->_data->cat_pub) {
				JError::raiseError( 404, JText::_("Resource Not Found") );
				return;
			}

			// Check whether category access level allows access
			if ($this->_data->cat_access > $user->get('gid')) {
				JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
				return;
			}
		} 
		else  $this->_initData();
		
		// Act on the task if needed
		switch($state->get('task')) 
		{
			case 'display' :
				$this->incrementHit();
		}
		
		return $this->_data;
	}

	/**
	 * Method to increment the hit counter for the weblink
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function incrementHit()
	{
		global $mainframe;

		if ($this->_id)
		{
			$weblink = & JTable::getInstance('weblink', 'Table');
			$weblink->hit($this->_id, $mainframe->getCfg('enable_log_items'));
			return true;
		}
		return false;
	}

	/**
	 * Tests if weblink is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	1.5
	 */
	function isCheckedOut( $uid=0 )
	{
		if ($this->_loadData())
		{
			if ($uid) {
				return ($this->_data->checked_out && $this->_weblink->checked_out != $uid);
			} else {
				return $this->_data->checked_out;
			}
		}
	}

	/**
	 * Method to checkin/unlock the weblink
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function checkin()
	{
		if ($this->_id)
		{
			$weblink = & JTable::getInstance('weblink', 'Table');
			return $weblink->checkin($this->_id);
		}
		return false;
	}

	/**
	 * Method to checkout/lock the weblink
	 *
	 * @access	public
	 * @param	int	$uid	User ID of the user checking the article out
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function checkout($uid = null)
	{
		if ($this->_id)
		{
			// Make sure we have a user id to checkout the article with
			if (is_null($uid)) {
				$user	=& JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$weblink = & JTable::getInstance('weblink', 'Table');
			return $weblink->checkout($uid, $this->_id);
		}
		return false;
	}

	/**
	 * Method to load content weblink data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = "SELECT w.*, cc.title AS category," .
					"\n cc.published AS cat_pub, cc.access AS cat_access".
					"\n FROM #__weblinks AS w" .
					"\n LEFT JOIN #__categories AS cc ON cc.id = w.catid" .
					"\n WHERE w.id = $this->_id";
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			return (boolean) $this->_data;
		}
		return true;
	}
	
	/**
	 * Method to initialise the weblink data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _initData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$weblink->id				= 0;
			$weblink->catid				= 0;
			$weblink->sid				= 0;
			$weblink->title				= null;
			$weblink->url				= null;
			$weblink->description		= null;
			$weblink->date				= null;
			$weblink->hits				= 0;
			$weblink->published			= 0;
			$weblink->checked_out		= 0;
			$weblink->checked_out_time	= 0;
			$weblink->ordering			= 0;
			$weblink->archived			= 0;
			$weblink->approved			= 0;
			$weblink->params			= null;
			$weblink->category			= null;
			$this->_data				= $weblink;
			return (boolean) $this->_data;
		}
		return true;
	}
}
?>