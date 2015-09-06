/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

;(function($){
	"use strict";
	$.subformRepeatable = function(container, options){
		this.$container = $(container);

		// check if alredy exist
		if(this.$container.data("subformRepeatable")){
			return self;
		}

		// Add a reverse reference to the DOM object
		this.$container.data("subformRepeatable", self);

		// merge options
		this.options = $.extend({}, $.subformRepeatable.defaults, options);

		// template for the repeating group
		this.template = '';

		// prepare a row template, and find available field names
		this.prepareTemplate();

		// check rows container
		this.$containerRows = this.options.rowsContainer ? this.$container.find(this.options.rowsContainer) : this.$container;

		// To avoid scope issues,
		var self = this;

		// bind add button
		this.$container.on('click', this.options.btAdd, function (e) {
			e.preventDefault();
			var after = $(this).parents(self.options.repeatableElement);
			if(!after.length){
				after = null;
			}
			self.addRow(after);
		});

		// bind remove button
		this.$container.on('click', this.options.btRemove, function (e) {
			e.preventDefault();
			var $row = $(this).parents(self.options.repeatableElement);
			self.removeRow($row);
		});

		// bind move button
		if(this.options.btMove){
			this.$containerRows.sortable({
				items: this.options.repeatableElement,
				handle: this.options.btMove,
				tolerance: 'pointer',
				update: this.resortNames.bind(this)
			});
		}

		// tell all that we a ready
		this.$container.trigger('subform-ready');
	};

	// prepare a template that we will use repeating
	$.subformRepeatable.prototype.prepareTemplate = function(){
		// create from template
		if(this.options.rowTemplateSelector){
			this.template = $.trim(this.$container.find(this.options.rowTemplateSelector).text());
		}
		// create from existing rows
		else {
			//find first available
			var row = this.$container.find(this.options.repeatableElement).get(0),
				$row = $(row).clone();

			// clear scripts that can be attached to the fields
			try {
				this.clearScripts($row);
			} catch (e) {
				if(window.console){
					console.log(e);
				}
			}

			this.template = $row.prop('outerHTML');
		}
	};

	// update names, after sorting, or deleting,
	$.subformRepeatable.prototype.resortNames = function(){
		var $rows = this.$container.find(this.options.repeatableElement),
			specialFields = [];

		for(var i=0, l = $rows.length; i<l; i++){
			var $row = $($rows[i]),
				group = $row.attr('data-group'),// group that was used for old
				basename = $row.attr('data-base-name'), // base name, without count
				groupnew = basename + i,
				haveName = $row.find('*[name]');

			$row.attr('data-group', groupnew);
			for(var n=0, nl = haveName.length; n<nl; n++){
				var $el = $(haveName[n]),
    				name = $el.attr('name'),
    				nameNew = name.replace('][' + group + '][', ']['+ groupnew +'][');// count new name

				// special case due specific behavior of the radio input
				if($el.prop('type') === 'radio'){
					$el.data('nameNew', nameNew);
					nameNew = nameNew + '[toFix]';
					specialFields.push($el);
				}

				// replace name to new
    			$el.attr('name', nameNew);
			}
		}

		// hande special cases
		for(var i=0, l = specialFields.length; i<l; i++){
			var $el = specialFields[i]; // It alredy wrapped to jQuery
			$el.attr('name', $el.data('nameNew'));
		}

	};

	// add new row
	$.subformRepeatable.prototype.addRow = function(after){
		// count how much we already have
		var count = this.$containerRows.find(this.options.repeatableElement).length;
		if(count >= this.options.maximum){
			return null;
		}

		// make new from template
		var row = $.parseHTML(this.template);

		//add to container
		if(after){
			$(after).after(row);
		} else {
			this.$containerRows.append(row);
		}

		var $row = $(row);
		//add marker that it is new
		$row.attr('data-new', 'true');
		// fix names and id`s, and reset values
		this.fixUniqueAttributes($row, count);
		//make sure that ordering is right
		this.resortNames();

		// try find out with related scripts,
		// tricky thing, so be careful
		try {
			this.fixScripts($row);
		} catch (e) {
			if(window.console){
				console.log(e);
			}
		}

		// tell everyone about the new row
		this.$container.trigger('subform-row-add', $row);
		return $row;
	};

	// remove row
	$.subformRepeatable.prototype.removeRow = function($row){
		// count how much we have
		var count = this.$containerRows.find(this.options.repeatableElement).length;
		if(count <= this.options.minimum){
			return;
		}

		// tell everyoune about the row will be removed
		this.$container.trigger('subform-row-remove', $row);
		$row.remove();
		//make sure that ordering is right
		this.resortNames();
	};

	//fix names ind id`s for field that in $row
	$.subformRepeatable.prototype.fixUniqueAttributes = function($row, count){
		var group = $row.attr('data-group'),// group for that was used for old
			basename = $row.attr('data-base-name'), // base name, without count
			count = count ? count - 1 : 0,
			groupnew = basename + (count + 1); // new group name

		$row.attr('data-group', groupnew);

		// fix inputs that have a "name" attribute
		var haveName = $row.find('*[name]'),
			ids = {}; // collect existing id`s for fix checkboxes and radio
		for(var i=0, l = haveName.length; i<l; i++){
				var $el = $(haveName[i]),
				name = $el.attr('name'),
				id = name.replace(/(\]|\[\]$)/g, '').replace(/\[/g, '_'), // count id from name, cause we lost it after cloning
				nameNew = name.replace('[' + group + '][', '['+ groupnew +']['),// count new name
				idNew = id.replace(group, groupnew),// count new id
				forOldAttr = id; // for fix "for" in the labels

			if($el.prop('type') === 'checkbox'){// <input type="checkbox"> fix
				//check if multiple
				if(name.match(/\[\]$/)){
					// replace a group label "for"
					var groupLbl = $row.find('label[for="' + id + '"]');
					if(groupLbl.length){
						groupLbl.attr('for', idNew);
						$el.parents('fieldset.checkboxes').attr('id', idNew);
					}
					// recount id
					var count = ids[id] ? ids[id].length : 0;
					forOldAttr = forOldAttr + count;
					idNew = idNew + count;
				}
			}
			else if($el.prop('type') === 'radio'){// <input type="radio"> fix
				// recount id
				var count = ids[id] ? ids[id].length : 0;
				forOldAttr = forOldAttr + count;
				idNew = idNew + count;
			}

			//cache ids
			if(ids[id]){
				ids[id].push(true);
			} else {
				ids[id] = [true];
			}

			// replace name to new
			$el.attr('name', nameNew);
			// set new id
			$el.attr('id', idNew);
			// guess there a lable for this input
			$row.find('label[for="' + forOldAttr + '"]').attr('for', idNew);
		}
	};

	// remove scripts attached to fields
	// @TODO: make thing better when something like that will be accepted https://github.com/joomla/joomla-cms/pull/6357
	$.subformRepeatable.prototype.clearScripts = function($row){
		// destroy chosen if any
		if($.fn.chosen){
			$row.find('select.chzn-done').each(function(){
				var $el = $(this);
				$el.next('.chzn-container').remove();
				$el.show().addClass('fix-chosen');
			});
		}

		// colorpicker
		if($.fn.minicolors){
			$row.find('.minicolors input').each(function(){
				$(this).removeData('minicolors-initialized')
				.removeData('minicolors-settings')
				.removeProp('size')
				.removeProp('maxlength')
				.removeClass('minicolors-input')
				// move out from <span>
				.parents('span.minicolors').parent().append(this);
			});
			$row.find('span.minicolors').remove();
		}
	};

	// method for hack the scripts that can be related
	// to the one of field that in given $row
	$.subformRepeatable.prototype.fixScripts = function($row){
		//color picker
		$row.find('.minicolors').each(function() {
			var $el = $(this);
			$el.minicolors({
				control: $el.attr('data-control') || 'hue',
				position: $el.attr('data-position') || 'right',
				theme: 'bootstrap'
			});
		});

		// fix media field
		$row.find('a[onclick*="jInsertFieldValue"]').each(function(){
				var $el = $(this),
				inputId = $el.siblings('input[type="text"]').attr('id'),
				$select = $el.prev(),
				oldHref = $select.attr('href');
			// update the clear button
			$el.attr('onclick', "jInsertFieldValue('', '" + inputId + "');return false;")
			// update select button
			$select.attr('href', oldHref.replace(/&fieldid=(.+)&/, '&fieldid=' + inputId + '&'));
		});

		//tooltips
		if($.fn.tooltip){
			$('.hasTooltip').tooltip({html: true, container: "body"});
		}

		// another modals
		if(window.SqueezeBox){
			SqueezeBox.assign($row.find('a.modal').get(), {parse: 'rel'});
		}
	};

	// defaults
	$.subformRepeatable.defaults = {
		btAdd: ".group-add", //  button selector for "add" action
		btRemove: ".group-remove",//  button selector for "remove" action
		btMove: ".group-move",//  button selector for "move" action
		minimum: 0, // minimum repeating
		maximum: 10, // maximum repeating
		repeatableElement: ".subform-repeatable-group",
		rowTemplateSelector: 'script.subform-repeatable-template-section', // selector for the row template <script>
		rowsContainer: null // container for rows, same as main container by default
	};

	$.fn.subformRepeatable = function(options){
		return this.each(function(){
			var options = options || {},
				data = $(this).data();

			if(data.subformRepeatable){
				// Alredy initialized, nothing to do here
				return;
			}

			for (var p in data) {
				// check options in the element
				if (data.hasOwnProperty(p)) {
					options[p] = data[p];
				}
			}

			var inst = new $.subformRepeatable(this, options);
			$(this).data('subformRepeatable', inst);
		});
	};

	// initialise all available
	// wait when all will be loaded, important for scripts fix
	$(window).on('load', function(){
		$('div.subform-repeatable').subformRepeatable();
	})

})(jQuery);
