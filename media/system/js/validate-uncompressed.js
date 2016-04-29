/**
 * @copyright	Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Unobtrusive Form Validation library
 *
 * Inspired by: Chris Campbell <www.particletree.com>
 *
 * @since  1.5
 */
var JFormValidator = function() {
	"use strict";
	var handlers, inputEmail, custom,

 	setHandler = function(name, fn, en) {
 	 	en = (en === '') ? true : en;
 	 	handlers[name] = {
 	 	 	enabled : en,
 	 	 	exec : fn
 	 	};
 	},

	refreshFormLabels = function(form, f)
	{
		// Iteration through DOM labels
		var $lbl, $el, for_id, el_id;
		var $lbls_hash = {};
		jQuery('label').each(function()
		{
			$lbl = jQuery(this);
			for_id = $lbl.attr('for');
			if (for_id)
			{
				$lbls_hash[for_id] = $lbl;
				//return;  // return will give minor performance improvement, but we don't use it to also index id-lbl
			}

			// Compatibility check ID-lbl as ID of label, it is better not to rely on this!, be compliant and specify "for="
			var lbl_id = $lbl.attr('id');
			if (lbl_id && lbl_id.indexOf('-lbl', lbl_id.length - 4) !== -1) {
				$lbls_hash[lbl_id.slice(0, -4)] = $lbl;
			}
		});

		// Set to zero length the .data('label') of elements without one
		if (typeof f === 'undefined') f = form.elements;
		var $empty = jQuery();
		for(var i=0; i<f.length; i++)
		{
			$el = jQuery(f[i]);
			if ( $el.data('label') === undefined )
			{
				el_id = $el.attr('id');
				(el_id && $lbls_hash.hasOwnProperty(el_id)) ?
					$el.data('label', $lbls_hash[el_id]) :
					$el.data('label', $empty) ;
			}
		}
	},

 	findLabel = function(id, form)
 	{
		if (!id) return false;
		
 	 	var $elem = jQuery('#'+id);
  	var $label = $elem.data('label');
  	
		if ( !!$label ) ;  // $label && $label !== undefined
		
		else if (!id)
			$elem.data('label', jQuery());
  	
   	// New element encountered (first run or / newly injected into the dom), redo iteration of DOM labels ... updating this and any other injected elements
  	else
  		refreshFormLabels(form, form.elements);
    
    $label = $elem.data('label');
    
		if ($label.length <= 0)
		{
			var $parentElem = $elem.parent();
		  parentTagName = $parentElem.get(0).tagName.toLowerCase();
		  
			if(parentTagName == "label")
			{
				$label = $parentElem;
				$elem.data('label', $label);
			}
		}
		return $label.length ? $label : false;
 	},

 	handleResponse = function(state, $el) {
 		// Get a label
 	 	var $label = $el.data('label');
 	 	if ($label === undefined) {
 	 		$label = findLabel($el.attr('id'), $el.get(0).form);
 	 	}

 	 	// Set the element and its label (if exists) invalid state
 	 	if (state === false) {
 	 	 	$el.addClass('invalid').attr('aria-invalid', 'true');
 	 	 	if ($label) {
 	 	 	 	$label.addClass('invalid');
 	 	 	}
 	 	} else {
 	 	 	$el.removeClass('invalid').attr('aria-invalid', 'false');
 	 	 	if ($label) {
 	 	 	 	$label.removeClass('invalid');
 	 	 	}
 	 	}
 	},

 	validate = function(el) {
 	 	var $el = jQuery(el), tagName, handler;
 	 	// Ignore the element if its currently disabled, because are not submitted for the http-request. For those case return always true.
 	 	if ($el.attr('disabled')) {
 	 	 	handleResponse(true, $el);
 	 	 	return true;
 	 	}
 	 	// If the field is required make sure it has a value
 	 	if ($el.attr('required') || $el.hasClass('required')) {
 	 	 	tagName = $el.prop("tagName").toLowerCase();
 	 	 	if (tagName === 'fieldset' && ($el.hasClass('radio') || $el.hasClass('checkboxes'))) {
 	 	 	 	if (!$el.find('input:checked').length){
 	 	 	 	 	handleResponse(false, $el);
 	 	 	 	 	return false;
 	 	 	 	}
 	 	 	//If element has class placeholder that means it is empty.
 	 	 	} else if (!$el.val() || $el.hasClass('placeholder') || ($el.attr('type') === 'checkbox' && !$el.is(':checked'))) {
 	 	 	 	handleResponse(false, $el);
 	 	 	 	return false;
 	 	 	}
 	 	}
 	 	// Only validate the field if the validate class is set
 	 	handler = ($el.attr('class') && $el.attr('class').match(/validate-([a-zA-Z0-9\_\-]+)/)) ? $el.attr('class').match(/validate-([a-zA-Z0-9\_\-]+)/)[1] : "";
 	 	if (handler === '') {
 	 	 	handleResponse(true, $el);
 	 	 	return true;
 	 	}
 	 	// Check the additional validation types
 	 	if ((handler) && (handler !== 'none') && (handlers[handler]) && $el.val()) {
 	 	 	// Execute the validation handler and return result
 	 	 	if (handlers[handler].exec($el.val(), $el) !== true) {
 	 	 	 	handleResponse(false, $el);
 	 	 	 	return false;
 	 	 	}
 	 	}
 	 	// Return validation state
 	 	handleResponse(true, $el);
 	 	return true;
 	},

 	isValid = function(form) {
 		var fields, valid = true, message, error, label, invalid = [], i, l;

 		// Validate form fields
 		fields = jQuery(form).find('input, textarea, select, fieldset');
 	 	for (i = 0, l = fields.length; i < l; i++) {
 	 		// Speed up validation by ignoring fields with 'novalidate' CSS class, such as Rule/Filters/Assigned fields
 	 		if(jQuery(fields[i]).hasClass('novalidate')) {
 	 			continue;
 	 		}
 	 	 	if (validate(fields[i]) === false) {
 	 	 	 	valid = false;
 	 	 	 	invalid.push(fields[i]);
 	 	 	}
 	 	}

 	 	// Run custom form validators if present
 	 	jQuery.each(custom, function(key, validator) {
 	 	 	if (validator.exec() !== true) {
 	 	 	 	valid = false;
 	 	 	}
 	 	});

 	 	if (!valid && invalid.length > 0) {
 	 	 	message = Joomla.JText._('JLIB_FORM_FIELD_INVALID');
 	 	 	error = {"error": []};
 	 	 	for (i = invalid.length - 1; i >= 0; i--) {
 	 	 		label = jQuery(invalid[i]).data("label");
 	 			if (label) {
 	 	 			error.error.push(message + label.text().replace("*", ""));
				}
 	 	 	}
 	 	 	Joomla.renderMessages(error);
 	 	}
 	 	
		// Since validation was successful, remove any old messages, because on-submit handlers and / or HTML5 validation may follow
 	 	else if (valid) Joomla.removeMessages();
 	 	
 	 	return valid;
 	},

 	attachToForm = function(form) {
 	 	var inputFields = [], elements,
 	 		$form = jQuery(form);
 	 	// Iterate through the form object and attach the validate method to all input fields.
 	 	elements = $form.find('input, textarea, select, fieldset, button');
 	 	for (var i = 0, l = elements.length; i < l; i++) {
 	 	 	var $el = jQuery(elements[i]), tagName = $el.prop("tagName").toLowerCase();
 	 	 	// Attach isValid method to submit button
 	 	 	if ((tagName === 'input' || tagName === 'button') && ($el.attr('type') === 'submit' || $el.attr('type') === 'image')) {
 	 	 	 	if ($el.hasClass('validate')) {
 	 	 	 	 	$el.on('click', function() {
 	 	 	 	 	 	return isValid(form);
 	 	 	 	 	});
 	 	 	 	}
 	 	 	}
 	 	 	// Attach validate method only to fields
 	 	 	else if (tagName !== 'button' && !(tagName === 'input' && $el.attr('type') === 'button')) {
 	 	 	 	if ($el.hasClass('required')) {
 	 	 	 	 	$el.attr('aria-required', 'true').attr('required', 'required');
 	 	 	 	}
 	 	 	 	if (tagName !== 'fieldset') {
 	 	 	 	 	$el.on('blur', function() {
 	 	 	 	 	 	return validate(this);
 	 	 	 	 	});
 	 	 	 	 	if ($el.hasClass('validate-email') && inputEmail) {
 	 	 	 	 		elements[i].setAttribute('type', 'email');
 	 	 	 	 	}
 	 	 	 	}
 	 	 	 	inputFields.push($el);
 	 	 	}
 	 	}
 	 	$form.data('inputfields', inputFields);
 	},

 	initialize = function() {
 	 	handlers = {};
 	 	custom = custom || {};

 	 	inputEmail = (function() {
 	 	 	var input = document.createElement("input");
 	 	 	input.setAttribute("type", "email");
 	 	 	return input.type !== "text";
 	 	})();
 	 	// Default handlers
 	 	setHandler('username', function(value, element) {
 	 	 	var regex = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&]", "i");
 	 	 	return !regex.test(value);
 	 	});
 	 	setHandler('password', function(value, element) {
 	 	 	var regex = /^\S[\S ]{2,98}\S$/;
 	 	 	return regex.test(value);
 	 	});
 	 	setHandler('numeric', function(value, element) {
 	 		var regex = /^(\d|-)?(\d|,)*\.?\d*$/;
 	 	 	return regex.test(value);
 	 	});
 	 	setHandler('email', function(value, element) {
		    value = punycode.toASCII(value);
 	 	 	var regex = /^[a-zA-Z0-9.!#$%&’*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
 	 	 	return regex.test(value);
 	 	});
 	 	// Attach to forms with class 'form-validate'
 	 	var forms = jQuery('form.form-validate');
 	 	for (var i = 0, l = forms.length; i < l; i++) {
 	 	 	attachToForm(forms[i]);
 	 	}
 	};

 	// Initialize handlers and attach validation to form
 	initialize();

 	return {
 	 	isValid : isValid,
 	 	validate : validate,
 	 	setHandler : setHandler,
 	 	attachToForm : attachToForm,
 	 	custom: custom
 	};
};

document.formvalidator = null;
jQuery(function() {
	document.formvalidator = new JFormValidator();
});
