<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');


jimport('joomla.common.abstract.tree');
jimport('joomla.utilities.simplexml');

/**
 * mod_mainmenu Helper class
 *
 * @static
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Menus
 * @since		1.5
 */
class modMainMenuHelper
{
	function buildXML(&$params)
	{
		$menu = new JMenuTree($params);
		$items = &JMenu::getInstance();

		// Get Menu Items
		$rows = $items->getItems('menutype', $params->get('menutype'));

		// Build Menu Tree root down (orphan proof - child might have lower id than parent)
		$user =& JFactory::getUser();
		$ids = array();
		$ids[0] = true;

		// pop the first item until the array is empty
		while ( !is_null($row = array_shift($rows)))
		{
			if (array_key_exists($row->parent, $ids)) {
				if ($row->access <= $user->get('aid', 0)) {
					$menu->addNode($row);
					// record loaded parents
					$ids[$row->id] = true;
				}
			} else {
				// no parent yet so push item to back of list
				array_push($rows, $row);
			}
		}
		return $menu->toXML();
	}

	function &getXML($type, &$params, $decorator)
	{
		static $xmls;

		if (!isset($xmls[$type])) {
			$cache =& JFactory::getCache('mod_mainmenu');
			$string = $cache->call(array('modMainMenuHelper', 'buildXML'), $params);
			$xmls[$type] = $string;
		}

		// Get document
		$xml = JFactory::getXMLParser('Simple');
		$xml->loadString($xmls[$type]);
		$doc = &$xml->document;

		$menu	= &JMenu::getInstance();
		$active	= $menu->getActive();
		$start	= $params->get('startLevel');
		$end	= $params->get('endLevel');
		$sChild	= $params->get('showAllChildren');

		// Get subtree
		if ($start) {
			$found = false;
			$path = array_reverse($active->tree);
			for ($i=0,$n=count($path);$i<$n;$i++)
			{
				foreach ($doc->children() as $child)
				{
					if ($child->attributes('id') == $path[$i]) {
						$doc = &$child->ul[0];
						break;
					}
				}
				if ($i == $start) {
					$found = true;
					break;
				}
			}
			if (!$found) {
				$doc = new JSimpleXMLElement('ul');
			}
		}

		$doc->map($decorator, array('end'=>$end, 'children'=>$sChild));
		return $doc;
	}

	function render(&$params, $callback)
	{
		switch ( $params->get( 'menu_style', 'list' ) )
		{
			case 'list_flat' :
				// Include the legacy library file
				require_once(dirname(__FILE__).DS.'legacy.php');
				mosShowHFMenu($params, 1);
				break;

			case 'horiz_flat' :
				// Include the legacy library file
				require_once(dirname(__FILE__).DS.'legacy.php');
				mosShowHFMenu($params, 0);
				break;

			case 'vert_indent' :
				// Include the legacy library file
				require_once(dirname(__FILE__).DS.'legacy.php');
				mosShowVIMenu($params);
				break;

			default :
				// Include the new menu class
				// require_once(dirname(__FILE__).DS.'menu.php');
				$xml = modMainMenuHelper::getXML($params->get('menutype'), $params, $callback);
				echo $xml->asXML();
				break;
		}
	}
}

/**
 * Main Menu Tree Class.
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Menus
 * @since		1.5
 */
class JMenuTree extends JTree
{
	/**
	 * Node/Id Hash for quickly handling node additions to the tree.
	 */
	var $_nodeHash = array();

	/**
	 * Menu parameters
	 */
	var $_params = null;

	/**
	 * Menu parameters
	 */
	var $_buffer = null;

	function __construct(&$params)
	{
		$this->_params =& $params;
		$this->_root =& new JMenuNode(0, 'ROOT');
		$this->_nodeHash[0] =& $this->_root;
		$this->_current = & $this->_root;
	}

	function addNode($item)
	{
		// Get menu item data
		$data = $this->_getItemData($item);

		// Create the node and add it
		$node =& new JMenuNode($item->id, $item->name, $data);

		if (isset($item->mid)) {
			$nid = $item->mid;
		} else {
			$nid = $item->id;
		}
		$this->_nodeHash[$nid] =& $node;
		$this->_current =& $this->_nodeHash[$item->parent];

		if ($this->_current) {
			$this->addChild($node, true);
		} else {
			// sanity check
			JError::raiseError( 500, 'Orphan Error. Could not find parent for Item '.$item->id );
		}
	}

	function toXML()
	{
		// Initialize variables
		$this->_current =& $this->_root;

		// Recurse through children if they exist
		while ($this->_current->hasChildren())
		{
			$this->_buffer .= '<ul>';
			foreach ($this->_current->getChildren() as $child)
			{
				$this->_current = & $child;
				$this->_getLevelXML(0);
			}
			$this->_buffer .= '</ul>';
		}
		return $this->_buffer;
	}

	function _getLevelXML($depth)
	{
		$depth++;

		// Start the item
		$this->_buffer .= '<li level="'.$depth.'" id="'.$this->_current->id.'">';

		// Append item data
		$this->_buffer .= $this->_current->link;

		// Recurse through item's children if they exist
		while ($this->_current->hasChildren())
		{
			$this->_buffer .= '<ul>';
			foreach ($this->_current->getChildren() as $child)
			{
				$this->_current = & $child;
				$this->_getLevelXML($depth);
			}
			$this->_buffer .= '</ul>';
		}

		// Finish the item
		$this->_buffer .= '</li>';
	}

	function _getItemData($item)
	{
		$data = null;

		// Menu Link is a special type that is a link to another item
		if ($item->type == 'menulink')
		{
			$menu = &JMenu::getInstance();
			if ($tmp = $menu->getItem($item->link)) {
				$tmp->name	 = $item->name;
				$tmp->mid	 = $item->id;
				$tmp->parent = $item->parent;
			} else {
				return false;
			}
		} else {
			$tmp = $item;
		}

		switch ($tmp->type)
		{
			case 'separator' :
				return '<span class="separator">'.$tmp->name.'</span>';
				break;

			case 'url' :
				if ((strpos($tmp->link, 'index.php?') !== false) && (strpos($tmp->link, 'Itemid=') === false)) {
					$tmp->url = $tmp->link.'&amp;Itemid='.$tmp->id;
				} else {
					$tmp->url = $tmp->link;
				}
				break;

			default :
				$tmp->url = 'index.php?Itemid='.$tmp->id;
				break;
		}

		// Print a link if it exists
		if ($tmp->url != null)
		{
			// Handle SSL links
			$iParams =& new JParameter($tmp->params);
			$iSecure = $iParams->def('secure', 0);
			if (strcasecmp(substr($tmp->url, 0, 4), 'http')) {
				$tmp->url = JRoute::_($tmp->url, $iSecure);
			} else {
				$tmp->url = ampReplace($tmp->url);
			}

			switch ($tmp->browserNav)
			{
				default:
				case 0:
					// _top
					$data = '<a href="'.$tmp->url.'">'.$tmp->name.'</a>';
					break;
				case 1:
					// _blank
					$data = '<a href="'.$tmp->url.'" target="_blank">'.$tmp->name.'</a>';
					break;
				case 2:
					// window.open
					$attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,'.$this->_params->get('window_open');

					// hrm...this is a bit dickey
					$link = str_replace('index.php', 'index2.php', $tmp->url);
					$data = '<a href="javascript:void window.open(\''.$link.'\',\'targetWindow\',\''.$attribs.'\');">'.$tmp->name.'</a>';
					break;
			}
		} else {
			$data = '<a>'.$tmp->name.'</a>';
		}

		return $data;
	}
}

/**
 * Main Menu Tree Node Class.
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Menus
 * @since		1.5
 */
class JMenuNode extends JNode
{
	/**
	 * Node Title
	 */
	var $title = null;

	/**
	 * Node Link
	 */
	var $link = null;

	/**
	 * CSS Class for node
	 */
	var $class = null;

	function __construct($id, $title, $link = null, $class = null)
	{
		$this->id		= $id;
		$this->title	= $title;
		$this->link		= $link;
		$this->class	= $class;
	}
}

function modMainMeuXMLCallback(&$node, $args)
{
	$menu	= &JMenu::getInstance();
	$active	= $menu->getActive();
	$path	= array_reverse($active->tree);

	if (($args['end']) && ($node->attributes('level') >= $args['end'])) {
		$children = &$node->children();
		foreach ($node->children() as $child)
		{
			if ($child->name() == 'ul') {
				$node->removeChild($child);
			}
		}
	}

	if (($node->name() == 'li') && isset($node->ul)) {
		$node->addAttribute('class', 'parent');
	}
	if (in_array($node->attributes('id'), $path)) {
		if ($node->attributes('class')) {
			$node->addAttribute('class', $node->attributes('class').' active');
		} else {
			$node->addAttribute('class', 'active');
		}
	} else {
		if (isset($args['children']) && !$args['children']) {
			$children = &$node->children();
			foreach ($node->children() as $child)
			{
				if ($child->name() == 'ul') {
					$node->removeChild($child);
				}
			}
		}
	}
	if ($node->attributes('id') == $path[0]) {
		$node->a[0]->removeAttribute('href');
		$node->addAttribute('id', 'current');
	} else {
		$node->removeAttribute('id');
	}
	$node->removeAttribute('level');
}
?>