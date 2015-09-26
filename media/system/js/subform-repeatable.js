(function(e){"use strict";e.subformRepeatable=function(t,n){this.$container=e(t);if(this.$container.data("subformRepeatable"))return r;this.$container.data("subformRepeatable",r),this.options=e.extend({},e.subformRepeatable.defaults,n),this.template="",this.prepareTemplate(),this.$containerRows=this.options.rowsContainer?this.$container.find(this.options.rowsContainer):this.$container;var r=this;this.$container.on("click",this.options.btAdd,function(t){t.preventDefault();var n=e(this).parents(r.options.repeatableElement);n.length||(n=null),r.addRow(n)}),this.$container.on("click",this.options.btRemove,function(t){t.preventDefault();var n=e(this).parents(r.options.repeatableElement);r.removeRow(n)}),this.options.btMove&&this.$containerRows.sortable({items:this.options.repeatableElement,handle:this.options.btMove,tolerance:"pointer",update:this.resortNames.bind(this)}),this.$container.trigger("subform-ready")},e.subformRepeatable.prototype.prepareTemplate=function(){if(this.options.rowTemplateSelector)this.template=e.trim(this.$container.find(this.options.rowTemplateSelector).text());else{var t=this.$container.find(this.options.repeatableElement).get(0),n=e(t).clone();try{this.clearScripts(n)}catch(r){window.console&&console.log(r)}this.template=n.prop("outerHTML")}},e.subformRepeatable.prototype.resortNames=function(){var t=this.$container.find(this.options.repeatableElement),n=[];for(var r=0,i=t.length;r<i;r++){var s=e(t[r]),o=s.attr("data-group"),u=s.attr("data-base-name"),a=u+r,f=s.find("*[name]");s.attr("data-group",a);for(var l=0,c=f.length;l<c;l++){var h=e(f[l]),p=h.attr("name"),d=p.replace("]["+o+"][","]["+a+"][");h.prop("type")==="radio"&&(h.data("nameNew",d),d+="[toFix]",n.push(h)),h.attr("name",d)}}for(var r=0,i=n.length;r<i;r++){var h=n[r];h.attr("name",h.data("nameNew"))}},e.subformRepeatable.prototype.addRow=function(t){var n=this.$containerRows.find(this.options.repeatableElement).length;if(n>=this.options.maximum)return null;var r=e.parseHTML(this.template);t?e(t).after(r):this.$containerRows.append(r);var i=e(r);i.attr("data-new","true"),this.fixUniqueAttributes(i,n),this.resortNames();try{this.fixScripts(i)}catch(s){window.console&&console.log(s)}return this.$container.trigger("subform-row-add",i),i},e.subformRepeatable.prototype.removeRow=function(e){var t=this.$containerRows.find(this.options.repeatableElement).length;if(t<=this.options.minimum)return;this.$container.trigger("subform-row-remove",e),e.remove(),this.resortNames()},e.subformRepeatable.prototype.fixUniqueAttributes=function(t,n){var r=t.attr("data-group"),i=t.attr("data-base-name"),n=n?n-1:0,s=i+(n+1);t.attr("data-group",s);var o=t.find("*[name]"),u={};for(var a=0,f=o.length;a<f;a++){var l=e(o[a]),c=l.attr("name"),h=c.replace(/(\]|\[\]$)/g,"").replace(/\[/g,"_"),p=c.replace("["+r+"][","["+s+"]["),d=h.replace(r,s),v=h;if(l.prop("type")==="checkbox"){if(c.match(/\[\]$/)){var m=t.find('label[for="'+h+'"]');m.length&&(m.attr("for",d),l.parents("fieldset.checkboxes").attr("id",d));var n=u[h]?u[h].length:0;v+=n,d+=n}}else if(l.prop("type")==="radio"){var n=u[h]?u[h].length:0;v+=n,d+=n}u[h]?u[h].push(!0):u[h]=[!0],l.attr("name",p),l.attr("id",d),t.find('label[for="'+v+'"]').attr("for",d)}},e.subformRepeatable.prototype.clearScripts=function(t){e.fn.chosen&&t.find("select.chzn-done").each(function(){var t=e(this);t.next(".chzn-container").remove(),t.show().addClass("fix-chosen")}),e.fn.minicolors&&(t.find(".minicolors input").each(function(){e(this).removeData("minicolors-initialized").removeData("minicolors-settings").removeProp("size").removeProp("maxlength").removeClass("minicolors-input").parents("span.minicolors").parent().append(this)}),t.find("span.minicolors").remove())},e.subformRepeatable.prototype.fixScripts=function(t){e.fn.chosen&&t.find("select.advancedSelect").chosen(),t.find(".minicolors").each(function(){var t=e(this);t.minicolors({control:t.attr("data-control")||"hue",position:t.attr("data-position")||"right",theme:"bootstrap"})}),t.find('a[onclick*="jInsertFieldValue"]').each(function(){var t=e(this),n=t.siblings('input[type="text"]').attr("id"),r=t.prev(),i=r.attr("href");t.attr("onclick","jInsertFieldValue('', '"+n+"');return false;"),r.attr("href",i.replace(/&fieldid=(.+)&/,"&fieldid="+n+"&"))}),e.fn.tooltip&&e(".hasTooltip").tooltip({html:!0,container:"body"}),window.SqueezeBox&&SqueezeBox.assign(t.find("a.modal").get(),{parse:"rel"})},e.subformRepeatable.defaults={btAdd:".group-add",btRemove:".group-remove",btMove:".group-move",minimum:0,maximum:10,repeatableElement:".subform-repeatable-group",rowTemplateSelector:"script.subform-repeatable-template-section",rowsContainer:null},e.fn.subformRepeatable=function(t){return this.each(function(){var t=t||{},n=e(this).data();if(n.subformRepeatable)return;for(var r in n)n.hasOwnProperty(r)&&(t[r]=n[r]);var i=new e.subformRepeatable(this,t);e(this).data("subformRepeatable",i)})},e(window).on("load",function(){e("div.subform-repeatable").subformRepeatable()})})(jQuery);