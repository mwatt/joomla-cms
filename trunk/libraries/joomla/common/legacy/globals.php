<?php
/**
* @version $Id$
* @package Joomla.Legacy
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.common.legacy.classes' );

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