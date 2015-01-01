<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Routing class from com_users
 *
 * @since  3.2
 */
class UsersRouter extends JComponentRouterAdvanced
{
	function __construct($app = null, $menu = null)
	{
		$this->registerView(new JComponentRouterViewconfiguration('login'));
		$this->registerView(new JComponentRouterViewconfiguration('profile'));
		$this->registerView(new JComponentRouterViewconfiguration('registration'));
		$this->registerView(new JComponentRouterViewconfiguration('remind'));
		$this->registerView(new JComponentRouterViewconfiguration('reset'));

		parent::__construct($app, $menu);

		$this->attachRule(new JComponentRouterRulesMenu($this));
		require_once JPATH_SITE . '/components/com_users/helpers/legacyrouter.php';
		$this->attachRule(new UsersRouterRulesLegacy($this));
	}
}

/**
 * Users router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$query  REQUEST query
 *
 * @return  array  Segments of the SEF url
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function usersBuildRoute(&$query)
{
	$app = JFactory::getApplication();
	$router = new UsersRouter($app, $app->getMenu());

	return $router->build($query);
}

/**
 * Convert SEF URL segments into query variables
 *
 * @param   array  $segments  Segments in the current URL
 *
 * @return  array  Query variables
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function usersParseRoute($segments)
{
	$app = JFactory::getApplication();
	$router = new UsersRouter($app, $app->getMenu());

	return $router->parse($segments);
}
