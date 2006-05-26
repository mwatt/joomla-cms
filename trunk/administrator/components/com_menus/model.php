<?php
/**
 * @version $Id: admin.menus.php 3504 2006-05-15 05:25:43Z eddieajau $
 * @package Joomla
 * @subpackage Menus
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport( 'joomla.application.model' );

/**
 * @package Joomla
 * @subpackage Menus
 * @author Andrew Eddie
 */
class JMenuModel extends JModel
{
	/** @var object JTable object */
	var $_table = null;

	/**
	 * Returns the internal table object
	 * @return JTable
	 */
	function &getTable()
	{
		if ($this->_table == null)
		{
			jimport( 'joomla.database.table.menu' );

			$db = &$this->getDBO();
			$this->_table = new JTableMenu( $db );
		}
		return $this->_table;
	}

	/**
	 * Delete one or more menu items
	 * @param mixed int or array of id values
	 */
	function delete( $ids )
	{
		if (!is_array( $ids ))
		{
			$ids = array( $ids );
		}

		$db = &$this->getDBO();
		
		if (count( $ids ))
		{
			// Delete associated module and template mappings
			$where = 'WHERE menuid = ' . implode( ' OR menuid = ', $ids );

			$query = 'DELETE FROM #__modules_menu '
				. $where;
			$db->setQuery( $query );
			if (!$db->query())
			{
				$this->setError( $menuTable->getErrorMsg() );
				return false;				
			}

			$query = 'DELETE FROM #__templates_menu '
				. $where;
			$db->setQuery( $query );
			if (!$db->query())
			{
				$this->setError( $menuTable->getErrorMsg() );
				return false;				
			}

			// Delete the menu items
			$where = 'WHERE id = ' . implode( ' OR id = ', $ids );
			
			$query = 'DELETE FROM #__menu ' . $where;
			$db->setQuery( $query );
			if (!$db->query())
			{
				$this->setError( $db->getErrorMsg() );
				return false;
			}
		}
		return true;
	}

	/**
	 * Delete menu items by type
	 */
	function deleteByType( $type = '' )
	{
		$db = &$this->getDBO();
		
		$query = 'SELECT id' .
				' FROM #__menu' .
				' WHERE menutype = ' . $db->Quote( $type );
		$db->setQuery( $query );
		$ids = $db->loadResultArray();
		
		if ($db->getErrorNum())
		{
			$this->setError( $db->getErrorMsg() );
			return false;
		}

		return $this->delete( $ids );
	}

	/**
	 * Get a list of the menu_types records
	 * @return array An array of records as objects
	 */
	function getMenuTypeList()
	{
		$db = $this->getDBO();
		$query = 'SELECT * FROM #__menu_types';
		$db->setQuery( $query );
		return $db->loadObjectList();
	}

	/**
	 * Get a list of the menutypes
	 * @return array An array of menu type names
	 */
	function getMenuTypes()
	{
		$db = $this->getDBO();
		$query = 'SELECT menutype FROM #__menu_types';
		$db->setQuery( $query );
		return $db->loadResultArray();
	}

	/**
	 * Gets the componet table object related to this menu item
	 */
	function &getComponent()
	{
		jimport( 'joomla.database.table.component' );
		$db = $this->getDBO();
		$id = $this->_table->componentid;
		$component = new JTableComponent( $db );
		$component->load( $id );
		return $component;
	}

	/**
	 * Gets a list of components that can link to the menu
	 */
	function getComponentList()
	{
		$db = $this->getDBO();
		$query = "SELECT c.id, c.name, c.link"
		. "\n FROM #__components AS c"
		. "\n WHERE c.link <> '' AND parent = 0"
		. "\n ORDER BY c.name"
		;
		$db->setQuery( $query );
		$result = $db->loadObjectList( );
		return $result;
	}
}
?>