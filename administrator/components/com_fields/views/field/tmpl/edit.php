<?php
/**
 * @package    Fields
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2015 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
$input = $app->input;

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "field.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			' . $this->form->getField("description")->save() . '
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
	jQuery(document).ready(function() {
		jQuery("#jform_title").data("dp-old-value", jQuery("#jform_title").val());
		jQuery("#jform_title").change(function(data, handler) {
			if(jQuery("#jform_title").data("dp-old-value") == jQuery("#jform_label").val()) {
				jQuery("#jform_label").val(jQuery("#jform_title").val());
			}

			jQuery("#jform_title").data("dp-old-value", jQuery("#jform_title").val());
		});
	});
');

?>

<form action="<?php echo JRoute::_('index.php?option=com_fields&context=' . $input->getCmd('context', 'com_content') . '&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_FIELDS_VIEW_FIELD_FIELDSET_GENERAL', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<?php
				echo $this->form->renderField('type');
				echo $this->form->renderField('label');
				echo $this->form->renderField('required');
				echo $this->form->renderField('default_value');
				echo $this->form->renderField('render_class');
				echo $this->form->renderField('class');

				// rendering additional fields
				foreach ($this->form->getFieldsets('fieldparams') as $name => $fieldSet)
				{
					foreach ($this->form->getFieldset($name) as $field)
					{
						echo $field->renderField();
					}
				}

				echo $this->form->getLabel('description');
				echo $this->form->getInput('description');
				?>
			</div>
			<div class="span3">
				<?php

				$this->set('fields', array(
						array('published', 'state', 'enabled'),
						'catid',
						'assigned_cat_ids',
						'access',
						'language',
						'tags',
						'note'
				));
				echo JLayoutHelper::render('joomla.edit.global', $this);
				$this->set('fields', null);
				?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_FIELDS_VIEW_FIELD_FIELDSET_PUBLISHING', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
			</div>
			<div class="span6">
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php if ($this->canDo->get('core.admin')) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'rules', JText::_('COM_FIELDS_VIEW_FIELD_FIELDSET_RULES', true)); ?>
			<?php echo $this->form->getInput('rules'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php
		$this->set('ignore_fieldsets', array('fieldparams'));
		echo JLayoutHelper::render('joomla.edit.params', $this); ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>

		<?php echo $this->form->getInput('context'); ?>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
