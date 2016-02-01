<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Base Helper class.
 *
 * @since  3.2
 */
class JHelper
{
	/**
	 * Gets the current language
	 *
	 * @param   boolean  $detectBrowser  Flag indicating whether to use the browser language as a fallback.
	 *
	 * @return  string  The language string
	 *
	 * @since       3.2
	 * @deprecated  4.0 Use JLanguageHelper::getCurrentLanguage() instead.
	 */
	public function getCurrentLanguage($detectBrowser = true)
	{
		JLog::add(__METHOD__ . '() is deprecated, use JLanguageHelper::getCurrentLanguage() instead.', JLog::WARNING, 'deprecated');

		return JLanguageHelper::getCurrentLanguage($detectBrowser);
	}

	/**
	 * Gets the associated language ID
	 *
	 * @param   string  $langCode  The language code to look up
	 *
	 * @return  integer  The language ID
	 *
	 * @since   3.2
	 */
	public function getLanguageId($langCode)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('lang_id')
			->from('#__languages')
			->where($db->quoteName('lang_code') . ' = ' . $db->quote($langCode));
		$db->setQuery($query);

		$id = $db->loadResult();

		return $id;
	}

	/**
	 * Gets a row of data from a table
	 *
	 * @param   JTableInterface  $table  JTable instance for a row.
	 *
	 * @return  array  Associative array of all columns and values for a row in a table.
	 *
	 * @since   3.2
	 */
	public function getRowData(JTableInterface $table)
	{
		$fields = $table->getFields();
		$data = array();

		foreach ($fields as &$field)
		{
			$columnName = $field->Field;
			$value = $table->$columnName;
			$data[$columnName] = $value;
		}

		return $data;
	}

	/**
	 * Method to get an object containing all of the table columns and values.
	 *
	 * @param   JTableInterface  $table  JTable object.
	 *
	 * @return  stdClass  Contains all of the columns and values.
	 *
	 * @since   3.2
	 */
	public function getDataObject(JTableInterface $table)
	{
		$fields = $table->getFields();
		$dataObject = new stdClass;

		foreach ($fields as $field)
		{
			$fieldName = $field->Field;
			$dataObject->$fieldName = $table->get($fieldName);
		}

		return $dataObject;
	}
}
