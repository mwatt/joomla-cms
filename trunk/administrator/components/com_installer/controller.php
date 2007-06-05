<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Installer
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights
 * reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');
jimport('joomla.client.helper');

/**
 * Installer Controller
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class InstallerController extends JController
{
	/**
	 * Display the extension installer form
	 *
	 * @access	public
	 * @return	void
	 * @since	1.5
	 */
	function installform()
	{
		$model	= &$this->getModel( 'Install' );
		$view	= &$this->getView( 'Install');

		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		$view->assignRef('ftp', $ftp);

		$view->setModel( $model, true );
		$view->display();
	}

	/**
	 * Install an extension
	 *
	 * @access	public
	 * @return	void
	 * @since	1.5
	 */
	function doInstall()
	{
		$model	= &$this->getModel( 'Install' );
		$view	= &$this->getView( 'Install' );

		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		$view->assignRef('ftp', $ftp);

		if ($model->install()) {
			$cache = &JFactory::getCache('mod_menu');
			$cache->clean();
		}

		$view->setModel( $model, true );
		$view->display();
	}

	/**
	 * Manage an extension type (List extensions of a given type)
	 *
	 * @access	public
	 * @return	void
	 * @since	1.5
	 */
	function manage()
	{
		$type	= JRequest::getVar('type', 'components');
		$model	= &$this->getModel( $type );
		$view	= &$this->getView( $type );

		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		$view->assignRef('ftp', $ftp);

		$view->setModel( $model, true );
		$view->display();
	}

	/**
	 * Enable an extension (If supported)
	 *
	 * @access	public
	 * @return	void
	 * @since	1.5
	 */
	function enable()
	{
		$type	= JRequest::getVar('type', 'components');
		$model	= &$this->getModel( $type );
		$view	= &$this->getView( $type );

		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		$view->assignRef('ftp', $ftp);

		if (method_exists($model, 'enable')) {
			$eid = JRequest::getVar('eid', array(), '', 'array');
			$model->enable($eid);
		}

		$view->setModel( $model, true );
		$view->display();
	}

	/**
	 * Disable an extension (If supported)
	 *
	 * @access	public
	 * @return	void
	 * @since	1.5
	 */
	function disable()
	{
		$type	= JRequest::getVar('type', 'components');
		$model	= &$this->getModel( $type );
		$view	= &$this->getView( $type );

		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		$view->assignRef('ftp', $ftp);

		if (method_exists($model, 'disable')) {
			$eid = JRequest::getVar('eid', array(), '', 'array');
			$model->disable($eid);
		}

		$view->setModel( $model, true );
		$view->display();
	}

	/**
	 * Remove an extension (Uninstall)
	 *
	 * @access	public
	 * @return	void
	 * @since	1.5
	 */
	function remove()
	{
		$type	= JRequest::getVar('type', 'components');
		$model	= &$this->getModel( $type );
		$view	= &$this->getView( $type );

		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		$view->assignRef('ftp', $ftp);

		$eid = JRequest::getVar('eid', array(), '', 'array');
		$result = $model->remove($eid);

		$view->setModel( $model, true );
		$view->display();
	}
}