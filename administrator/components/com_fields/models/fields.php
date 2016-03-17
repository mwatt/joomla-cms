<?php
/**
 * @package    Fields
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2015 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\Registry\Registry;

class FieldsModelFields extends JModelList
{

	public function __construct ($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
					'id',
					'a.id',
					'title',
					'a.title',
					'type',
					'a.type',
					'alias',
					'a.alias',
					'published',
					'a.published',
					'access',
					'a.access',
					'access_level',
					'language',
					'a.language',
					'ordering',
					'a.ordering',
					'checked_out',
					'a.checked_out',
					'checked_out_time',
					'a.checked_out_time',
					'created_time',
					'a.created_time',
					'created_user_id',
					'a.created_user_id',
					'tag',
					'category_title'
			);
		}

		parent::__construct($config);
	}

	protected function populateState ($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();
		$context = $this->context;

		$context = $app->getUserStateFromRequest('com_fields.fields.filter.context', 'context', 'com_content.article', 'cmd');

		$this->setState('filter.context', $context);
		$parts = explode('.', $context);

		// Extract the component name
		$this->setState('filter.component', $parts[0]);

		// Extract the optional section name
		$this->setState('filter.section', (count($parts) > 1) ? $parts[1] : null);

		$search = $this->getUserStateFromRequest($context . '.search', 'filter_search');
		$this->setState('filter.search', $search);

		$level = $this->getUserStateFromRequest($context . '.filter.level', 'filter_level');
		$this->setState('filter.level', $level);

		$access = $this->getUserStateFromRequest($context . '.filter.access', 'filter_access');
		$this->setState('filter.access', $access);

		$published = $this->getUserStateFromRequest($context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$language = $this->getUserStateFromRequest($context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		$tag = $this->getUserStateFromRequest($this->context . '.filter.tag', 'filter_tag', '');
		$this->setState('filter.tag', $tag);

		// List state information.
		parent::populateState('a.ordering', 'asc');

		// Force a language
		$forcedLanguage = $app->input->get('forcedLanguage');

		if (! empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}
	}

	protected function getStoreId ($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.context');
		$id .= ':' . serialize($this->getState('filter.assigned_cat_ids'));
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	protected function getListQuery ()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'));
		$query->from('#__fields AS a');

		// Join over the language
		$query->select('l.title AS language_title')->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level')->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the users for the author.
		$query->select('ua.name AS author_name')->join('LEFT', '#__users AS ua ON ua.id = a.created_user_id');

		// Join over the categories.
		$query->select('c.title as category_title')->join('LEFT', '#__categories AS c ON c.id = a.catid');

		// Filter by context
		if ($context = $this->getState('filter.context'))
		{
			$query->where('a.context = ' . $db->quote($context));
		}

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			if (is_array($access))
			{
				JArrayHelper::toInteger($access);
				$query->where('a.access in (' . implode(',', $access) . ')');
			}
			else
			{
				$query->where('a.access = ' . (int) $access);
			}
		}
		if (($categories = $this->getState('filter.assigned_cat_ids')) && $context)
		{
			$categories = (array) $categories;
			$condition = "a.assigned_cat_ids = '' or find_in_set(0, a.assigned_cat_ids) ";
			$parts = FieldsHelper::extract($context);
			if ($parts)
			{
				// Get the category
				$cat = JCategories::getInstance(str_replace('com_', '', $parts[0]));
				if ($cat)
				{
					foreach ($categories as $assignedCatIds)
					{
						// Check if we have the actual category
						$parent = $cat->get($assignedCatIds);
						if ($parent)
						{
							$condition .= 'or find_in_set(' . (int) $parent->id . ',a.assigned_cat_ids) ';

							// Traverse the tree up to get all the fields which
							// are attached to a parent
							while ($parent->getParent() && $parent->getParent()->id != 'root')
							{
								$parent = $parent->getParent();
								$condition .= 'or find_in_set(' . (int) $parent->id . ',a.assigned_cat_ids) ';
							}
						}
					}
				}
			}
			$query->where('(' . $condition . ')');
		}

		// Implement View Level Access
		if (! $user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (! empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			elseif (stripos($search, 'author:') === 0)
			{
				$search = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
				$query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ' OR a.note LIKE ' . $search . ')');
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where('a.language in (' . $db->quote($language) . ',' . $db->quote('*') . ')');
		}

		// Filter by a single tag.
		$tagId = $this->getState('filter.tag');

		if (is_numeric($tagId))
		{
			$query->where($db->quoteName('tagmap.tag_id') . ' = ' . (int) $tagId)
				->join('LEFT',
					$db->quoteName('#__contentitem_tag_map', 'tagmap') . ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' .
							 $db->quoteName('a.id') . ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote($context . '.field'));
		}

		// Add the list ordering clause
		$listOrdering = $this->getState('list.ordering', 'a.ordering');
		$listDirn = $db->escape($this->getState('list.direction', 'ASC'));

		if ($listOrdering == 'a.access')
		{
			$query->order('a.access ' . $listDirn);
		}
		else
		{
			$query->order($db->escape($listOrdering) . ' ' . $listDirn);
		}

		// Echo nl2br(str_replace('#__', 'j_', $query)); //die();
		return $query;
	}

	protected function _getList ($query, $limitstart = 0, $limit = 0)
	{
		$result = parent::_getList($query, $limitstart, $limit);

		if (is_array($result))
		{
			foreach ($result as $field)
			{
				$field->fieldparams = new Registry($field->fieldparams);
				$field->params = new Registry($field->params);
			}
		}

		return $result;
	}

	public function getFilterForm ($data = array(), $loadData = true)
	{
		$form = parent::getFilterForm($data, $loadData);
		if ($form)
		{
			$path = JPATH_ADMINISTRATOR . '/components/' . $this->getState('filter.component') . '/models/forms/filter_fields.xml';
			if (file_exists($path))
			{
				// Load all children that's why we need to define the xpath
				if (! $form->loadFile($path, true, '/form/*'))
				{
					throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
				}
			}

			// If the context has multiple sections, this is the input field
			// to display them
			$form->setValue('section', 'custom', JFactory::getApplication()->input->getCmd('context'));
		}
		return $form;
	}
}
