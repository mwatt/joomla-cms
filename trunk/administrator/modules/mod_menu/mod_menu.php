<?php
/**
* @version $Id:mod_menu.php 2463 2006-02-18 06:05:38Z webImagery $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Lets get some variables we will need to render the menu
 */
$lang 	= & $mainframe->getLanguage();
$doc 	= & $mainframe->getDocument();
$user 	= & $mainframe->getUser();
$hide 	= JRequest :: getVar( 'hidemainmenu', 0 );

// Add the javascript to the page head
/*
$doc->addScript('../includes/js/moofx/prototype.lite.js');
$doc->addScript('../includes/js/moofx/moo.fx.js');
$doc->addScript('../includes/js/moofx/moo.fx.pack.js');

$js = 	"\t\t\t//the main function, call to the effect object" .
		"\n\t\t\tfunction init(){" .
		"\n\n\t\t\t	var stretchers = document.getElementsByClassName('section_smenu'); //div that stretches" .
		"\n\t\t\t	var toggles = document.getElementsByClassName('title_smenu'); //h3s where I click on" .
		"\n\n\t\t\t	//accordion effect" .
		"\n\t\t\t	var smenuAccordion = new fx.Accordion(" .
		"\n\t\t\t	toggles, stretchers, {opacity: true, duration: 400}" .
		"\n\t\t\t	);" .
		"\n\n\t\t\t	//hash functions" .
		"\n\t\t\t	var found = false;" .
		"\n\t\t\t	toggles.each(function(h3, i){" .
		"\n\t\t\t		var div = Element.find(h3, 'nextSibling'); //element.find is located in prototype.lite" .
		"\n\t\t\t		if (window.location.href.indexOf(h3.title) > 0) {" .
		"\n\t\t\t			smenuAccordion.showThisHideOpen(div);" .
		"\n\t\t\t			found = true;" .
		"\n\t\t\t		}" .
		"\n\t\t\t	});" .
		"\n\t\t\t	if (!found) smenuAccordion.showThisHideOpen(stretchers[0]);" .
		"\n\t\t\t}";

$doc->addScriptDeclaration($js);
*/

/*
 * If we are disabling the menu, show the disabled menu... otherwise show the
 * full menu.
 */
echo "<div id=\"sidemenu\">";
if ($hide) {
	JAdminMenu :: showDisabled($user->get('usertype'));
} else {
	JAdminMenu :: show($user->get('usertype'));
}
echo "</div>";


/**
 * Admin Side Menu
 *
 * @package Joomla
 */
class JAdminMenu {
	/**
	* Show the menu
	* @param string The current user type
	*/
	function show($usertype = '') {
		global $mainframe;

		$lang 			= & $mainframe->getLanguage();
		$user 			= & $mainframe->getUser();
		$database 		= & $mainframe->getDBO();
		$enableStats 	= $mainframe->getCfg('enable_stats');
		$enableSearches = $mainframe->getCfg('enable_log_searches');
		$caching 		= $mainframe->getCfg('caching');

		// cache some acl checks
		$canCheckin 		= $user->authorize('com_checkin', 		'manage');
		$canConfig 			= $user->authorize('com_config', 		'manage');
		$manageTemplates 	= $user->authorize('com_templates', 	'manage');
		$manageTrash 		= $user->authorize('com_trash', 		'manage');
		$manageMenuMan 		= $user->authorize('com_menumanager', 	'manage');
		$manageLanguages 	= $user->authorize('com_languages', 	'manage');
		$installModules 	= $user->authorize('com_installer', 	'module');
		$editAllModules 	= $user->authorize('com_modules', 		'manage');
		$installPlugins 	= $user->authorize('com_installer', 	'plugin');
		$editAllPlugins 	= $user->authorize('com_plugins', 		'manage');
		$installComponents 	= $user->authorize('com_installer', 	'component');
		$editAllComponents 	= $user->authorize('com_components', 	'manage');
		$canMassMail 		= $user->authorize('com_massmail', 		'manage');
		$canManageUsers 	= $user->authorize('com_users', 		'manage');

		$query = "SELECT a.id, a.title, a.name, COUNT( DISTINCT c.id ) AS numcat, COUNT( DISTINCT b.id ) AS numarc" .
				"\n FROM #__sections AS a" .
				"\n LEFT JOIN #__categories AS c ON c.section = a.id" .
				"\n LEFT JOIN #__content AS b ON b.sectionid = a.id AND b.state = -1" .
				"\n WHERE a.scope = 'content'" .
				"\n GROUP BY a.id" .
				"\n ORDER BY a.ordering";
		$database->setQuery($query);
		$sections = $database->loadObjectList();
		$nonemptySections = 0;
		if (count($sections) > 0) {
			foreach ($sections as $section) {
				if ($section->numcat > 0) {
					$nonemptySections ++;
				}
			}
		}
		$menuTypes = mosAdminMenus :: menutypes();

	/*
	 * Home SubMenu
	 */
		$homeList[] = array( 'title' => JText::_('Control Panel'), 'link' => 'index2.php', 'class' => JAdminMenu::getIconClass('class:cpanel') );
		$homeList[] = array( 'title' => JText::_('Help'), 'link' => 'index2.php?option=com_admin&task=help', 'class' => JAdminMenu::getIconClass('class:help') );
		echo JAdminMenu::buildDiv ( JText::_('Home'), $homeList );

	/*
	 * Site SubMenu
	 */
		if ($canConfig)
		{
			$siteList[] = array( 'title' => JText::_('Global Configuration'), 'link' => 'index2.php?option=com_config&hidemainmenu=1', 'class' => JAdminMenu::getIconClass('class:config') );
		}
		if ($canManageUsers || $canMassMail) {
			$siteList[] = array( 'title' => JText::_('User Manager'), 'link' => 'index2.php?option=com_users&task=view', 'class' => JAdminMenu::getIconClass('class:users') );
		}
		$siteList[] = array( 'title' => JText::_('Media Manager'), 'link' => 'index2.php?option=com_media', 'class' => JAdminMenu::getIconClass('class:media') );
		if ($canConfig)	{
			$siteList[] = array( 'title' => JText::_('Messages'), 'link' => 'index2.php?option=com_messages', 'class' => JAdminMenu::getIconClass('class:inbox') );
		}
		if ($manageTemplates) {
			$siteList[] = array( 'title' => JText::_('Template Manager'), 'link' => 'index2.php?option=com_templates', 'class' => JAdminMenu::getIconClass('class:template') );
		}
		if ($manageLanguages) {
			$siteList[] = array( 'title' => JText::_('Language Manager'), 'link' => 'index2.php?option=com_languages', 'class' => JAdminMenu::getIconClass('class:language') );
		}
		if ($enableStats || $enableSearches ) {
			$siteList[] = array( 'title' => JText::_('Statistics'), 'link' => 'index2.php?option=com_stastistics', 'class' => JAdminMenu::getIconClass('class:globe1') );
		}
		echo JAdminMenu::buildDiv ( JText::_('Site'), $siteList );

	/*
	 * Menus SubMenu
	 */
		if ($manageMenuMan) {
			$menusList[] = array( 'title' => JText::_('Menu Manager'), 'link' => 'index2.php?option=com_menumanager', 'class' => JAdminMenu::getIconClass('class:menus') );
		}
		if ($manageTrash) {
			$menusList[] = array( 'title' => JText::_('Trash Manager'), 'link' => 'index2.php?option=com_trash&task=viewMenu', 'class' => JAdminMenu::getIconClass('class:trash') );
		}
		/*
		 * SPLIT HR
		 */
		foreach ($menuTypes as $menuType) {
			$menusList[] = array( 'title' => $menuType, 'link' => 'index2.php?option=com_menus&menutype='.$menuType, 'class' => JAdminMenu::getIconClass('class:menus') );
		}
		echo JAdminMenu::buildDiv ( JText::_('Menus'), $menusList );

	/*
	 * Content SubMenu
	 */
		$contentList[] = array( 'title' => JText::_('All Content Items'), 'link' => 'index2.php?option=com_content&sectionid=0', 'class' => JAdminMenu::getIconClass('class:edit') );
		$contentList[] = array( 'title' => JText::_('Static Content Manager'), 'link' => 'index2.php?option=com_typedcontent', 'class' => JAdminMenu::getIconClass('class:edit') );
		/*
		 * SPLIT HR
		 */
		$contentList[] = array( 'title' => JText::_('Section Manager'), 'link' => 'index2.php?option=com_sections&scope=content', 'class' => JAdminMenu::getIconClass('class:add-section') );
		$contentList[] = array( 'title' => JText::_('Category Manager'), 'link' => 'index2.php?option=com_categories&section=content', 'class' => JAdminMenu::getIconClass('class:add-section') );
		/*
		 * SPLIT HR
		 */
		$contentList[] = array( 'title' => JText::_('Frontpage Manager'), 'link' => 'index2.php?option=com_frontpage', 'class' => JAdminMenu::getIconClass('class:home') );
		$contentList[] = array( 'title' => JText::_('Archive Manager'), 'link' => 'index2.php?option=com_content&task=showarchive&sectionid=0', 'class' => JAdminMenu::getIconClass('class:edit') );
		if ($manageTrash) {
			/*
			 * SPLIT HR
			 */
			$contentList[] = array( 'title' => JText::_('Trash Manager'), 'link' => 'index2.php?option=com_trash&task=viewContent', 'class' => JAdminMenu::getIconClass('class:trash') );
			$contentList[] = array( 'title' => JText::_('Page Hits'), 'link' => 'index2.php?option=com_statistics&task=pageimp', 'class' => JAdminMenu::getIconClass('class:globe3') );
		}
		echo JAdminMenu::buildDiv ( JText::_('Content'), $contentList );

	/*
	 * Components SubMenu
	 */
		if ($installComponents) {
			$query = "SELECT *" .
					"\n FROM #__components" .
					"\n WHERE name <> 'frontpage'" .
					"\n AND name <> 'media manager'" .
					"\n ORDER BY ordering, name";
			$database->setQuery($query);
			$comps 	= $database->loadObjectList(); // component list
			$subs	 = array (); // sub menus
			// first pass to collect sub-menu items
			foreach ($comps as $row) {
				if ($row->parent) {
					if (!array_key_exists($row->parent, $subs)) {
						$subs[$row->parent] = array ();
					}
					$subs[$row->parent][] = $row;
				}
			}
			foreach ($comps as $row) {
				if ($editAllComponents | $user->authorize('administration', 'edit', 'components', $row->option)) {
					if ($row->parent == 0 && (trim($row->admin_menu_link) || array_key_exists($row->id, $subs))) {
						$alt 	= $row->admin_menu_alt;
						$link 	= $row->admin_menu_link ? "index2.php?$row->admin_menu_link" : "index2.php?option=$row->option";
						$componentsList[] = array( 'title' => JText::_($row->name), 'link' => $link, 'class' => $row->admin_menu_img );
					}
				}
			}
			echo JAdminMenu::buildDiv ( JText::_('Components'), $componentsList );
		}

	/*
	 * Modules SubMenu
	 */
		if ($installModules | $editAllModules) {
			if ($editAllModules) {
				$modulesList[] = array( 'title' => JText::_('Site Modules'), 'link' => 'index2.php?option=com_modules', 'class' => JAdminMenu::getIconClass('class:module') );
				$modulesList[] = array( 'title' => JText::_('Administrator Modules'), 'link' => 'index2.php?option=com_modules&client=admin', 'class' => JAdminMenu::getIconClass('class:module') );
			}
			echo JAdminMenu::buildDiv ( JText::_('Modules'), $modulesList );
		}

	/*
	 * Plugins SubMenu
	 */
		if ($installPlugins | $editAllPlugins) 	{
			if ($editAllPlugins) {
				$pluginsList[] = array( 'title' => JText::_('Site Plugins'), 'link' => 'index2.php?option=com_plugins', 'class' => JAdminMenu::getIconClass('class:module') );
			}
			echo JAdminMenu::buildDiv ( JText::_('Plugins'), $pluginsList );
		}

	/*
	 * Extensions SubMenu
	 */
		if ($installModules) {
			$extensionsList[] = array( 'title' => JText::_('Extension Manager'), 'link' => 'index2.php?option=com_installer', 'class' => JAdminMenu::getIconClass('class:install') );
			echo JAdminMenu::buildDiv ( JText::_('Extensions'), $extensionsList );
		}

	/*
	 * System SubMenu
	 */
		if ($canConfig)	{
			$systemList[] = array( 'title' => JText::_('System Info'), 'link' => 'index2.php?option=com_admin&task=sysinfo', 'class' => JAdminMenu::getIconClass('class:sysinfo') );;
			if ($canCheckin) {
				$systemList[] = array( 'title' => JText::_('Global Checkin'), 'link' => 'index2.php?option=com_checkin', 'class' => JAdminMenu::getIconClass('class:checkin') );
			}
			if ($caching) {
				$systemList[] = array( 'title' => JText::_('Clean Content Cache'), 'link' => 'index2.php?option=com_admin&task=clean_cache', 'class' => JAdminMenu::getIconClass('class:config') );
				$systemList[] = array( 'title' => JText::_('Clean All Cache'), 'link' => 'index2.php?option=com_admin&task=clean_all_cache', 'class' => JAdminMenu::getIconClass('class:config') );
			}
			echo JAdminMenu::buildDiv ( JText::_('System'), $systemList );
		}
	}

	/**
	* Show an disbaled version of the menu, used in edit pages
	*
	* @param string The current user type
	*/
	function showDisabled($usertype = '') {
		global $mainframe;

		$lang 	= & $mainframe->getLanguage();
		$user 	= & $mainframe->getUser();

		$canConfig 			= $user->authorize('com_config', 		'manage');
		$installModules 	= $user->authorize('com_installer', 	'module');
		$editAllModules 	= $user->authorize('com_modules', 		'manage');
		$installPlugins 	= $user->authorize('com_installer', 	'plugin');
		$editAllPlugins 	= $user->authorize('com_plugins', 		'manage');
		$installComponents 	= $user->authorize('com_installer', 	'component');
		$editAllComponents 	= $user->authorize('com_components',	'manage');
		$canMassMail 		= $user->authorize('com_massmail', 		'manage');
		$canManageUsers 	= $user->authorize('com_users', 		'manage');

		$text = JText :: _('Menu inactive for this Page', true);
	}
	
	function buildDiv ( $title, $list, $suffix = '-smenu' )
	{

		$txt = 	"<h3 class=\"title".$suffix."\" title=\"$title\">$title</h3>";		
		$txt .=	"<div class=\"section".$suffix."\">\n";
		
		/*
		 * Iterate through the link items for building the menu items
		 */
		foreach ($list as $item)
		{
			if (isset($item['active']) && $item['active'] == 1)
			{
				$sfx = $suffix.'_active';
			} else
			{
				$sfx = $suffix;
			}
			$txt .=	"<li class=\"item".$sfx." ".$item['class']."\">";
			$txt .= "<a href=\"".$item['link']."\">".$item['title']."</a>";
			$txt .=	"</li>"; 
		}
		$txt .=	"\n</div>";

		return $txt;		
	}

	/**
	 * Method to get the CSS class name for an icon identifier or create one if
	 * a custom image path is passed as the identifier
	 * 
	 * @access	public
	 * @param	string	$identifier	Icon identification string
	 * @return	string	CSS class name
	 * @since	1.1
	 */
	function getIconClass($identifier)
	{
		global $mainframe;
		
		static $classes;

		// Initialize the known classes array if it does not exist
		if (!is_array($classes))
		{
			$classes = array();
		}
		
		/*
		 * If we don't already know about the class... build it and mark it
		 * known so we don't have to build it again
		 */
		if (!isset($classes[$identifier]))
		{
			if (substr($identifier, 0, 6) == 'class:')
			{
				// We were passed a class name
				$class = substr($identifier, 6);
				$classes[$identifier] = "icon-16-$class";
			} else
			{
				// We were passed an image path... is it a themeoffice one?
				if (substr($identifier, 0, 15) == 'js/ThemeOffice/')
				{
					// Strip the filename without extension and use that for the classname
					$class = JFile::stripExt(basename($identifier));
					$classes[$identifier] = "icon-16-$class";
				} else
				{
					// Build the CSS class for the icon
					$class = JFile::makeSafe(JFile::stripExt(basename($identifier)));
					$style = ".icon-16-$class {\n" .
							"\tbackground: url($identifier) no-repeat;\n" .
							"}";
					
					// Add style to document head
					$doc = & $mainframe->getDocument();
					$doc->addStyleDeclaration($style);
					$classes[$identifier] = "icon-16-$class";
				}
			}
		}
		
		return $classes[$identifier];
	}
}
?>