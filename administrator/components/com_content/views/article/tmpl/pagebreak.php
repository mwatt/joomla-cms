<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JFactory::getDocument()->addScriptDeclaration(
		'
		function insertPagebreak() {
			var title = document.getElementById("title").value;

			if (title != \'\') {
				title = "title=\""+title+"\" ";
			}

			// Get the pagebreak toc alias -- not inserting for now
			// don\'t know which attribute to use...
			var alt = document.getElementById("alt").value;

			if (alt != \'\') {
				alt = "alt=\""+alt+"\" ";
			}
			var tag = "<hr class=\"system-pagebreak\" "+title+" "+alt+"/>";
			window.parent.jInsertEditorText(tag, ' . json_encode($this->eName) . ');
			window.parent.jModalClose();
			return false;
		}
		'
);
?>
<br />
<form class="form-horizontal">

	<div class="control-group">
		<label for="title" class="control-label"><?php echo JText::_('COM_CONTENT_PAGEBREAK_TITLE'); ?></label>
		<div class="controls"><input type="text" id="title" name="title" /></div>
	</div>

	<div class="control-group">
		<label for="alias" class="control-label"><?php echo JText::_('COM_CONTENT_PAGEBREAK_TOC'); ?></label>
		<div class="controls"><input type="text" id="alt" name="alt" /></div>
	</div>

	<button class="btn btn-success pull-right" onclick="insertPagebreak();"><?php echo JText::_('COM_CONTENT_PAGEBREAK_INSERT_BUTTON'); ?></button>

</form>
