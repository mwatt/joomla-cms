<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Config
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Config
*/
class TOOLBAR_config {
	function _DEFAULT() {

		JMenuBar::title( JText::_( 'Global Configuration' ), 'config.png' );
		JMenuBar::save();
		JMenuBar::apply();
		JMenuBar::cancel();
		JMenuBar::help( 'screen.config' );
	}
}
?>