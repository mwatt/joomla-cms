<?php
/**
* version $Id$
* @package Joomla
* @subpackage Newsfeeds
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
*
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.application.view');

/**
 * HTML View class for the Newsfeeds component
 *
 * @static
 * @package Joomla
 * @subpackage Newsfeeds
 * @since 1.0
 */
class NewsfeedsViewCategory extends JView
{
	function __construct()
	{
		$this->setViewName('category');
		$this->setTemplatePath(dirname(__FILE__).DS.'tmpl');
	}

	function display()
	{
		$this->_loadTemplate('table');
	}

	function items( )
	{
		global $Itemid;

		if (!count( $this->items ) ) {
			return;
		}

		$catid = $this->request->catid;

		//create pagination
		jimport('joomla.presentation.pagination');
		$this->pagination = new JPagination($this->data->total, $this->request->limitstart, $this->request->limit);

		$this->data->link = "index.php?option=com_newsfeeds&amp;task=category&amp;catid=$catid&amp;Itemid=$Itemid";

		$k = 0;
		for($i = 0; $i <  count($this->items); $i++)
		{
			$item =& $this->items[$i];

			$item->link =  sefRelToAbs('index.php?option=com_newsfeeds&amp;task=view&amp;feedid='. $item->id .'&amp;Itemid='. $Itemid);

			$item->odd   = $k;
			$item->count = $i;
			$k = 1 - $k;
		}

		// Define image tag attributes
		if (isset ($this->category->image))
		{
			$attribs['align'] = '"'.$this->category->image_position.'"';
			$attribs['hspace'] = '"6"';

			// Use the static HTML library to build the image tag
			$this->data->image = mosHTML::Image('/images/stories/'.$this->category->image, JText::_('News Feeds'), $attribs);
		}

		$this->_loadTemplate('_table_items');
	}
}
?>