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
 * Plugin table
 *
 * @package 	Joomla.Framework
 * @subpackage 	Model
 * @since		1.0
 */
class JTablePlugin extends JTable
{
	/**
	 * Primary Key
	 *
	 *  @var int
	 */
	var $id = null;

	/**
	 *
	 *
	 * @var varchar
	 */
	var $name = null;

	/**
	 *
	 *
	 * @var varchar
	 */
	var $element = null;

	/**
	 *
	 *
	 * @var varchar
	 */
	var $folder = null;

	/**
	 *
	 *
	 * @var tinyint unsigned
	 */
	var $access = null;

	/**
	 *
	 *
	 * @var int
	 */
	var $ordering = null;

	/**
	 *
	 *
	 * @var tinyint
	 */
	var $published = null;

	/**
	 *
	 *
	 * @var tinyint
	 */
	var $iscore = null;

	/**
	 *
	 *
	 * @var tinyint
	 */
	var $client_id = null;

	/**
	 *
	 *
	 * @var int unsigned
	 */
	var $checked_out = null;

	/**
	 *
	 *
	 * @var datetime
	 */
	var $checked_out_time = null;

	/**
	 *
	 *
	 * @var text
	 */
	var $params = null;

	function __construct(& $db) {
		parent::__construct('#__plugins', 'id', $db);
	}

	/**
	* Overloaded bind function
	*
	* @acces public
	* @param array $hash named array
	* @return null|string	null is operation was satisfactory, otherwise returns an error
	* @see JTable:bind
	* @since 1.5
	*/
	function bind($array, $ignore = '')
	{
		$params = JRequest::getVar( 'params', array(), 'post', 'array' );

		if (is_array( $array['params'] )) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}
}
?>