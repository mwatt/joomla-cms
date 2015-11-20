<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$buttons = $displayData;

// Override jModalClose and SqueezeBox.close for B/C
JFactory::getDocument()->addScriptDeclaration(
		"
		function jModalClose() {
			SqueezeBox.close();
			jQuery('.modal, .in ').modal('hide');
		}

		var SqueezeBox;
		if (SqueezeBox != undefined)
		{
			bsClose = function(){
				jQuery('.modal, .in ').modal('hide');
			}
			SqueezeBox.close.bind(bsClose);
		} else {
			var SqueezeBox = {};
			SqueezeBox.close = function(){
			jQuery('.modal, .in ').modal('hide');
			}
		}
		"
);

?>
<div id="editor-xtd-buttons" class="btn-toolbar pull-left">
	<?php if ($buttons) : ?>
		<?php foreach ($buttons as $button) : ?>
			<?php echo JLayoutHelper::render('joomla.editors.buttons.button', $button); ?>
		<?php endforeach; ?>
		<?php foreach ($buttons as $button) : ?>
			<?php echo JLayoutHelper::render('joomla.editors.buttons.modal', $button); ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>