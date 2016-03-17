<?php
/**
 * @package    Fields
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2015 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_fields.models.types.list', JPATH_ADMINISTRATOR);

class FieldsTypeYesno extends FieldsTypeList
{

	protected function postProcessDomNode ($field, DOMElement $fieldNode, JForm $form)
	{
		$return = parent::postProcessDomNode($field, $fieldNode, $form);
		$fieldNode->setAttribute('type', 'radio');

		// JFormField replaces invalid characters
		$id = 'params_' . preg_replace('#\W#', '_', $fieldNode->getAttribute('name'));

		JFactory::getDocument()->addScriptDeclaration(
				"jQuery( document ).ready(function() {
	jQuery('#jform_" . $id . " input').bind('click', function(e)
	{
		jQuery('#jform_" . $id . " label').attr('class', 'btn');
		if (jQuery('#jform_" . $id . "0')[0].checked || (!jQuery('#jform_" . $id . "0')[0].checked && !jQuery('#jform_" . $id . "1')[0].checked))
		{
			jQuery('#jform_" . $id . " label[for=\"jform_" . $id . "0\"]').attr('class', 'btn btn-success');
		}
		else
		{
			jQuery('#jform_" . $id . " label[for=\"jform_" . $id . "1\"]').attr('class', 'btn btn-danger');
		}
	});
	jQuery('#jform_" . $id . " label').attr('class', 'btn');
	if (jQuery('#jform_" . $id . "0')[0].checked || (!jQuery('#jform_" . $id . "0')[0].checked && !jQuery('#jform_" . $id . "1')[0].checked))
	{
		jQuery('#jform_" . $id . " label[for=\"jform_" . $id . "0\"]').attr('class', 'btn btn-success');
	}
	else
	{
		jQuery('#jform_" . $id . " label[for=\"jform_" . $id . "1\"]').attr('class', 'btn btn-danger');
	}
});");

		return $return;
	}

	public function getOptions ($field)
	{
		return array(
				1 => JText::_('JYES'),
				0 => JText::_('JNO')
		);
	}
}
