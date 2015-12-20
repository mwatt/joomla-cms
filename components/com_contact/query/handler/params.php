<?php
/**
 * @package     Joomla.Framework
 * @subpackage  Service Layer
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Service\QueryHandlerBase;

final class ContactQueryHandlerParams extends QueryHandlerBase
{
	/**
	 * Query handler.
	 * 
	 * @param   ContactQueryParams  $query  A query.
	 * 
	 * @return  Registry containing merged parameters.
	 */
	public function handle(ContactQueryParams $query)
	{
		$model  = JModelLegacy::getInstance('Contact', 'ContactModel');
		$params = JComponentHelper::getParams('com_contact');

		// Get the contact from the model.
		$contact = $model->getItem($query->id);

		// Merge the params.
		$params->merge($contact->params);

		return $params;
	}
}
