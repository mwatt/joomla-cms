<?php
/**
 * @version $Id$
 * @package  Joomla
 * @subpackage Banners
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.controller' );

class BannerController extends JController
{
	function click()
	{
		$bid = JRequest::getVar( 'bid', 0, '', 'int' );
		if ($bid)
		{
			$model = &$this->getModel( 'Banner', 'Model' );
			$model->click( $bid );
			$this->setRedirect( $model->getUrl( $bid ) );
		}
	}
}

$controller = new BannerController( 'click' );
$controller->setModelPath( dirname( __FILE__ ) . '/models' );
$controller->execute( $task );
$controller->redirect();
?>