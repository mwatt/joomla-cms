<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$doc = JFactory::getDocument();

// Output document as HTML5.
if (is_callable(array($doc, 'setHtml5')))
{
	$doc->setHtml5(true);
}
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
	<meta charset="utf-8" />
	<title><?php echo $this->title; ?> <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></title>
	<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo  $this->template; ?>/css/error.css" />
	<?php if ($app->get('debug_lang', '0') == '1' || $app->get('debug', '0') == '1') : ?>
		<!-- Load additional CSS styles for debug mode-->
		<link rel="stylesheet" href="<?php echo JUri::root(); ?>/media/cms/css/debug.css" />
	<?php endif; ?>
	<!-- Load additional CSS styles for rtl sites -->
	<?php if ($this->direction == 'rtl') : ?>
		<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo  $this->template; ?>/css/template_rtl.css" />
	<?php endif; ?>
	<!--[if lt IE 9]><script src="<?php echo $this->baseurl; ?>/media/jui/js/html5.js"></script><![endif]-->
</head>
<body class="errors">
	<div>
		<h1>
			<?php echo $this->error->getCode(); ?> - <?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?>
		</h1>
	</div>
	<div>
		<p><?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>
		<p><a href="index.php"><?php echo JText::_('JGLOBAL_TPL_CPANEL_LINK_TEXT'); ?></a></p>
		<?php if ($this->debug) : ?>
			<?php echo $this->renderBacktrace(); ?>
		<?php endif; ?>
	</div>
	<div class="clr"></div>
	<noscript>
			<?php echo JText::_('JGLOBAL_WARNJAVASCRIPT'); ?>
	</noscript>
</body>
</html>
