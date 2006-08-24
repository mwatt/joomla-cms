<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* Base class for a Joomla Model
*
* Acts as a Factory class for application specific objects and
* provides many supporting API functions.
*
* @abstract
* @package		Joomla.Framework
* @subpackage	Application
* @since		1.5
*/
class JModel extends JObject
{
	/**
	 * The model (base) name
	 *
	 * @var string
	 */
	var $_modelName;

	/**
	 * Database Connector
	 *
	 * @var object
	 */
	var $_db;

	/**
	 * An error message
	 *
	 * @var string
	 */
	var $_error;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		$this->_db = &JFactory::getDBO();
	}

	/**
	 * String representation
	 * @return string
	 * @since 1.5
	 */
	function __toString()
	{
		$result = get_class( $this );
		return $result;
	}

	/**
	 * Returns an object list
	 * @param string The query
	 * @param int Offset
	 * @param int The number of records
	 * @return array
	 * @access protected
	 * @since 1.5
	 */
	function &_getList( $query, $limitstart=0, $limit=0 )
	{
		$db = JFactory::getDBO();
		$db->setQuery( $query, $limitstart, $limit );
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Returns a record count for the query
	 * @param string The query
	 * @return int
	 * @access protected
	 * @since 1.5
	 */
	function _getListCount( $query )
	{
		$db = JFactory::getDBO();
		$db->setQuery( $query );
		$db->query();

		return $db->getNumRows();
	}

	/**
	 * Get instance
	 * @return JModelMenu
	 */
	function &getInstance( $modelName )
	{
		static $instance;

		if (!isset( $instance[$modelName] ))
		{
			$db = &JFactory::getDBO();
			$instance[$modelName] = new $modelName( $db );
		}
		return $instance[$modelName];
	}

	/**
	 * @return string The model name
	 */
	function getModelName()
	{
		return $this->_modelName;
	}

	/**
	 * Method to get current menu parameters
	 *
	 * @access	public
	 * @return	object JDatabase connector object
	 * @since 1.5
	 */
	function &getDBO()
	{
		return $this->_db;
	}

	/**
	 * Get the error message
	 * @return string The error message
	 * @since 1.5
	 */
	function getError() {
		return $this->_error;
	}

	/**
	 * Sets the error message
	 * @param string The error message
	 * @return string The new error message
	 * @since 1.5
	 */
	function setError( $value ) {
		$this->_error = $value;
		return $this->_error;
	}
}
?>