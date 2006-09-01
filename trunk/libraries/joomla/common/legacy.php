<?php
/**
* @version $Id: globals.php 3996 2006-06-12 03:44:31Z spacemonkey $
* @package Joomla.Legacy
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Import library dependencies
jimport( 'joomla.common.legacy.classes' );
jimport( 'joomla.common.legacy.functions' );

/**
 * Legacy define, _ISO defined not used anymore. All output is forced as utf-8
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
DEFINE('_ISO','charset=utf-8');

/**
 * Legacy constant, use _JEXEC instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
define( '_VALID_MOS', 1 );

/**
 * Legacy constant, use _JEXEC instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
define( '_MOS_MAMBO_INCLUDED', 1 );

/**
 * Legacy global, use JVersion->getLongVersion() instead
 *
 * @name $_VERSION
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
$_VERSION = new JVersion();
$version = $_VERSION->PRODUCT .' '. $_VERSION->RELEASE .'.'. $_VERSION->DEV_LEVEL .' '
. $_VERSION->DEV_STATUS
.' [ '.$_VERSION->CODENAME .' ] '. $_VERSION->RELDATE .' '
. $_VERSION->RELTIME .' '. $_VERSION->RELTZ;

/**
 * Legacy global, use JFactory::getDBO() instead
 *
 * @name $database
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
$conf =& JFactory::getConfig();
$GLOBALS['database'] = new database($conf->getValue('config.host'), $conf->getValue('config.user'), $conf->getValue('config.password'), $conf->getValue('config.db'), $conf->getValue('config.dbprefix'));
$GLOBALS['database']->debug($conf->getValue('config.debug'));

/**
 * Legacy global, use JFactory::getUser() [JUser object] instead
 *
 * @name $my
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
$user			= & JFactory::getUser();
$GLOBALS['my']	= $user->getTable();

/**
 * Legacy global, use JApplication::getTemplate() instead
 *
 * @name $cur_template
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
global $mainframe;
$GLOBALS['cur_template']	= $mainframe->getTemplate();


/**
 * Legacy global, use JFactory::getUser() instead
 *
 * @name $acl
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
$GLOBALS['acl'] =& JFactory::getACL();

/**
 * Load the site language file (the old way - to be deprecated)
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
global $mosConfig_lang;
$file = JPATH_SITE .'/language/' . $mosConfig_lang .'.php';
if (file_exists( $file )) {
	require_once( $file);
} else {
	$file = JPATH_SITE .'/language/english.php';
	if (file_exists( $file )) {
		require_once( $file );
	}
}

/**
 *  Legacy global
 * 	use JApplicaiton->registerEvent and JApplication->triggerEvent for event handling
 *  use JPlugingHelper::importPlugin to load bot code
 *  @deprecated As of version 1.5
 */
$_MAMBOTS = new mosMambotHandler();
?>