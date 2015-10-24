/**
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Field user
 */
;(function($){
	'use strict';

	$.fieldUser = function(container, options){
		// Merge options with defaults
		this.options = $.extend({}, $.fieldUser.defaults, options);

		// Set up elements
		this.$container = $(container);
		this.$modal = this.$container.find(this.options.modal);
		this.$modalBody = this.$modal.children('.modal-body');
		this.$input = this.$container.find(this.options.input);
		this.$buttonSelect = this.$container.find(this.options.buttonSelect);
		this.$groups = this.$container.find(this.options.groups);

		//  + '&amp;groups=' + $groups + '&amp;excluded=' + $excluded
		// Bind events
		this.$buttonSelect.on('click', this.modalOpen.bind(this));
	};

	// display modal for select the file
	$.fieldUser.prototype.modalOpen = function() {
		var $iframe = $('<iframe>', {
			name: 'field-user-modal',
			src: this.options.url.replace('{field-user-id}', this.$input.attr('id')),
			width: this.options.modalWidth,
			height: this.options.modalHeight
		});
		this.$modalBody.append($iframe);
		this.$modal.modal('show');

		var self = this; // save context
		$iframe.load(function(){
			var content = $(this).contents();

			$.prototype.jSelectUser = function(id, title, field) {
				var old_id = document.getElementById(field + '_id').value;
				if (old_id != id) {
					document.getElementById(field + '_id').value = id;
					document.getElementById(field + '_name').value = title;
					var el = document.getElementById(field + '_id'),
						callbackStr =  el.getAttribute('data-onchange'),
						callback;
					if(callbackStr) {
						callback = new Function(callbackStr);
						callback.call(el);
					}
				}
				self.modalClose.call(self);
			};

			// bind cancel
			content.on('click', '.button-cancel', self.modalClose.bind(self));
		});
	};

	// close modal
	$.fieldUser.prototype.modalClose = function() {
		this.$modal.modal('hide');
		this.$modalBody.empty();
	};

	// default options
	$.fieldUser.defaults = {
		buttonSelect: '.button-select', // selector for button to change the value
		input: '.field-user-input', // selector for the input
		linkSaveSelected: '.link-save-selected', // selector for button to save the selected value
		modal: '.modal', // modal selector
		url : 'index.php?option=com_users&view=users&layout=modal&tmpl=component',
		modalWidth: '100%', // modal width
		modalHeight: '300px' // modal height
	};

	$.fn.fieldUser = function(options){
		return this.each(function(){
			var $el = $(this), instance = $el.data('fieldUser');
			if(!instance){
				var options = options || {},
					data = $el.data();

				// Check options in the element
				for (var p in data) {
					if (data.hasOwnProperty(p)) {
						options[p] = data[p];
					}
				}

				instance = new $.fieldUser(this, options);
				$el.data('fieldUser', instance);
			}
		});
	};

	// Initialise all defaults
	$(document).ready(function(){
		$('.field-user-wrapper').fieldUser();
	});

})(jQuery);
