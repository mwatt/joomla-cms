<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Modules
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* @package Joomla
* @subpackage Modules
*/
class TOOLBAR_modules {
	/**
	* Draws the menu for a New module
	*/
	function _NEW($client)	{
		JMenuBar::title( JText::_( 'Module Manager' ) . ' - <span>' . JText::_( 'New Module' ) . '</span>', 'module.png' );
		JMenuBar::customX( 'edit', 'next.png', 'next_f2.png', 'Next', true );
		JMenuBar::cancel();
		JMenuBar::help( 'screen.modules.new' );
	}

	/**
	* Draws the menu for Editing an existing module
	*/
	function _EDIT( $client ) {
		$cid 		= JRequest::getVar( 'cid', array(0));
		$moduleType = JRequest::getVar( 'module' );

		JMenuBar::title( JText::_( 'Module Manager' ) .' - <span>' . JText::_( 'Edit Module' ) . '</span>', 'module.png' );

		if($moduleType == 'custom') {
			JMenuBar::Preview('index3.php?option=com_modules&client='.$client.'&pollid='.$cid[0]);
		}
		if ($cid[0]) {
			JMenuBar::trash('remove', 'Delete', false);
		}
		JMenuBar::save();
		JMenuBar::apply();
		if ( $cid[0] ) {
			// for existing items the button is renamed `close`
			JMenuBar::cancel( 'cancel', JText::_( 'Close' ) );
		} else {
			JMenuBar::cancel();
		}
		JMenuBar::help( 'screen.modules.edit' );
	}
	function _DEFAULT($client) {

		//JMenuBar::title( JText::_( 'Module Manager' ).': <small><small>[' .JText::_( $client->name ) .']</small></small>', 'module.png' );
		JMenuBar::title( JText::_( 'Module Manager' ), 'module.png' );
		JMenuBar::publishList();
		JMenuBar::unpublishList();
		JMenuBar::custom( 'copy', 'copy.png', 'copy_f2.png', 'Copy', true );
		JMenuBar::deleteList();
		JMenuBar::editListX();
		JMenuBar::addNewX();
		JMenuBar::help( 'screen.modules' );
	}
}
?>