<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div id="template-manager-css" class="form-horizontal">
	<div class="control-group">
		<label for="new_name" class="control-label hasTooltip" title="<?php echo JHtml::tooltipText(JText::_('COM_TEMPLATES_NEW_FILE_NAME')); ?>"><?php echo JText::_('COM_TEMPLATES_NEW_FILE_NAME')?></label>
		<div class="controls">
			<input class="input-xlarge" type="text" name="new_name" required />
		</div>
	</div>
</div>
