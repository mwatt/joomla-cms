<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// url of current page that user will be returned to after login
$url = mosGetParam($_SERVER, 'REQUEST_URI', null);

$user = & $mainframe->getUser();

// if return link does not contain https:// & http:// and to url
if (strpos($url, 'http:') !== 0 && strpos($url, 'https:') !== 0)
{
	$url = mosGetParam($_SERVER, 'HTTP_HOST', null).$url;

	// check if link is https://
	if (isset ($_SERVER['HTTPS']) && (!empty ($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'))
	{
		$return = 'https://'.$url;
	}
	else
	{
		// normal http:// link
		$return = 'http://'.$url;
	}
}
else
{
	$return = $url;
}

// converts & to &amp; for xtml compliance
$return = str_replace('&', '&amp;', $return);

$registration_enabled	= $mainframe->getCfg('allowUserRegistration');
$pretext				= $params->get('pretext');
$posttext				= $params->get('posttext');
$login					= $params->def('login', $return);
$logout					= $params->def('logout', $return);
$name					= $params->def('name', 1);
$greeting				= $params->def('greeting', 1);

if ($user->get('id'))
{
	// Logout output
	// ie HTML when already logged in and trying to logout
	if ($name)
	{
		$query = "SELECT name" .
				"\n FROM #__users" .
				"\n WHERE id = ".$user->get('id');
		$database->setQuery($query);
		$name = $database->loadResult();
	}
	else
	{
		$name = $user->get('username');
	}
	?>
	<form action="index.php" method="post" name="login">
	<?php
	if ($greeting)
	{
	?>
		<div>
			<?php echo sprintf( JText::_( 'HINAME' ), $name ); ?>
		</div>
		<?php
	}
	?>
	<div align="center">
		<input type="submit" name="Submit" class="button" value="<?php echo JText::_( 'BUTTON_LOGOUT'); ?>" />
	</div>

	<input type="hidden" name="option" value="com_login" />
	<input type="hidden" name="task" value="logout" />
	<input type="hidden" name="return" value="<?php echo sefRelToAbs( $logout ); ?>" />
	</form>
	<?php
}
else
{
	// Login output
	// ie HTML when not logged in and trying to login
	?>
	<form action="index.php" method="post" name="login" >

	<?php echo $pretext; ?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td>
			<label for="mod_login_username">
				<?php echo JText::_( 'Username' ); ?>
			</label>
			<br />
			<input name="username" id="mod_login_username" type="text" class="inputbox" alt="<?php echo JText::_( 'Username' ); ?>" size="10" />
			<br />
			<label for="mod_login_password">
				<?php echo JText::_( 'Password' ); ?>
			</label>
			<br />
			<input type="password" id="mod_login_password" name="passwd" class="inputbox" size="10" alt="<?php echo JText::_( 'Password' ); ?>" />
			<br />
			<?php
			if(JRequest::getVar('option') == 'com_login') {
				if(!JSession::get('guest')) {
					echo "<p>";
					echo JText::_( 'LOGIN_INCORRECT' );
					echo  "</p>";
				}
			}
			?>
			<input type="checkbox" name="remember" id="mod_login_remember" class="inputbox" value="yes" alt="<?php echo JText::_( 'Remember me' ); ?>" />
			<label for="mod_login_remember">
				<?php echo JText::_( 'Remember me' ); ?>
			</label>
			<br />
			<input type="submit" name="Submit" class="button" value="<?php echo JText::_( 'BUTTON_LOGIN'); ?>" />
		</td>
	</tr>
	<tr>
		<td>
			<a href="<?php echo sefRelToAbs( 'index.php?option=com_registration&amp;task=lostPassword' ); ?>">
				<?php echo JText::_( 'Lost Password?'); ?></a>
		</td>
	</tr>
	<?php

	if ($registration_enabled)
	{
	?>
		<tr>
			<td>
				<?php echo JText::_( 'No account yet?'); ?>
				<a href="<?php echo sefRelToAbs( 'index.php?option=com_registration&amp;task=register' ); ?>">
					<?php echo JText::_( 'Register'); ?></a>
			</td>
		</tr>
		<?php

	}
	?>
	</table>
	<?php echo $posttext; ?>

	<input type="hidden" name="option" value="com_login" />
	<input type="hidden" name="task" value="login" />
	<input type="hidden" name="return" value="<?php echo sefRelToAbs( $login ); ?>" />
	</form>
	<?php
}
?>