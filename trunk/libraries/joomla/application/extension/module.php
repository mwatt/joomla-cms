<?php
/**
* @version $Id: plugin.php 1567 2005-12-28 17:03:11Z Jinx $
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
* Module helper class
*
* @static
* @author		Johan Janssens <johan.janssens@joomla.org>
* @package		Joomla.Framework
* @subpackage	Application
* @since		1.5
*/
class JModuleHelper
{
	/**
	 * Get module by name
	 *
	 * @access public
	 * @param string 	$name	The name of the module
	 * @return object	The Module object
	 */
	function &getModule($name)
	{
		$result = null;

		$modules =& JModuleHelper::_load();

		$total = count($modules);
		for ($i = 0; $i < $total; $i++) {
			if ($modules[$i]->name == $name)
			{

				$result =& $modules[$i];
				break;
			}
		}

		return $result;
	}

	/**
	 * Get modules by position
	 *
	 * @access public
	 * @param string 	$position	The position of the module
	 * @return array	An array of module objects
	 */
	function &getModules($position)
	{
		$result = array();

		$modules =& JModuleHelper::_load();

		$total = count($modules);
		for($i = 0; $i < $total; $i++) {
			if($modules[$i]->position == $position) {
				$result[] =& $modules[$i];
			}
		}

		return $result;
	}
	
	function renderModule($module, $params = array())
	{
		jimport('joomla.factory');
		
		global $mainframe;
		global $Itemid, $task, $option, $my;

		$user 		=& $mainframe->getUser();
		$database   =& $mainframe->getDBO();
		$acl  		=& JFactory::getACL();

		//For backwards compatibility extract the config vars as globals
		$registry =& JFactory::getConfig();
		foreach (get_object_vars($registry->toObject()) as $k => $v) {
			$name = 'mosConfig_'.$k;
			$$name = $v;
		}

		$style   = isset($params['style']) ? $params['style'] : $module->style;
		$outline = isset($params['outline']) ? $params['outline'] : false;

		//get module parameters
		$params = new JParameter( $module->params );

		//get module path
		$path = JPATH_BASE . '/modules/'.$module->module.'/'.$module->module.'.php';

		//load the module
		if (!$module->user && file_exists( $path ))
		{
			$lang =& $mainframe->getLanguage();
			$lang->load($module->module);

			ob_start();
			require $path;
			$module->content = ob_get_contents();
			ob_end_clean();
		}

		$contents = '';
		ob_start();
			modules_html::module( $module, $params, $style, $outline);
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}


	/**
	 * Load published modules
	 *
	 * @access private
	 * @return array
	 */
	function &_load()
	{
		global $mainframe, $Itemid;

		static $modules;

		if (isset($modules)) {
			return $modules;
		}

		$user	=& $mainframe->getUser();
		$db		=& $mainframe->getDBO();
		$gid	= $user->get('gid');

		$modules = array();

		$wheremenu = isset($Itemid)? "\n AND ( mm.menuid = '". $Itemid ."' OR mm.menuid = 0 )" : "";

		$query = "SELECT id, title, module, position, content, showtitle, control, params"
			. "\n FROM #__modules AS m"
			. "\n LEFT JOIN #__modules_menu AS mm ON mm.moduleid = m.id"
			. "\n WHERE m.published = 1"
			. "\n AND m.access <= '". $gid ."'"
			. "\n AND m.client_id = '". $mainframe->getClientId() ."'"
			. $wheremenu
			. "\n ORDER BY position, ordering";

		$db->setQuery( $query );
		$modules = $db->loadObjectList();

		$total = count($modules);
		for($i = 0; $i < $total; $i++) {
			//determine if this is a user module
			$file = $modules[$i]->module;
			$user = substr( $file, 0, 4 )  == 'mod_' ?  0 : 1;
			$modules[$i]->user  = $user;
			// CHECK: custom module name is given by the title field, otherwise it's just 'om' ??
			$modules[$i]->name  = $user ? $modules[$i]->title : substr( $file, 4 );
			$modules[$i]->style = null;
		}

		return $modules;
	}

}
?>