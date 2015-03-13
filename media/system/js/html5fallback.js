!function(e,t,a){"use strict";"function"!=typeof Object.create&&(Object.create=function(e){function t(){}return t.prototype=e,new t});var i=function(e,t){for(var a=["required","pattern","placeholder","autofocus","formnovalidate"],i=["email","url","number","range"],r={attributes:{},types:{}};t=a.pop();)r.attributes[t]=!!(t in e);for(;t=i.pop();)e.setAttribute("type",t),r.types[t]=e.type==t;return r}(t.createElement("input")),r={init:function(t,a){var i=this;i.elem=a,i.$elem=e(a),a.H5Form=i,i.options=e.extend({},e.fn.h5f.options,t),"form"===a.nodeName.toLowerCase()&&i.bindWithForm(i.elem,i.$elem)},bindWithForm:function(e,t){var r,s=this,n=!!t.attr("novalidate"),o=e.elements,l=o.length;for("onSubmit"===s.options.formValidationEvent&&t.on("submit",function(e){r=this.H5Form.donotValidate!==a?this.H5Form.donotValidate:!1,r||n||s.validateForm(s)?t.find(":input").each(function(){s.placeholder(s,this,"submit")}):(e.preventDefault(),this.donotValidate=!1)}),t.on("focusout focusin",function(e){s.placeholder(s,e.target,e.type)}),t.on("focusout change",s.validateField),t.find("fieldset").on("change",function(){s.validateField(this)}),i.attributes.formnovalidate||t.find(":submit[formnovalidate]").on("click",function(){s.donotValidate=!0});l--;)s.polyfill(o[l]),s.autofocus(s,o[l])},polyfill:function(e){if("form"===e.nodeName.toLowerCase())return!0;var t=e.form.H5Form;t.placeholder(t,e),t.numberType(t,e)},validateForm:function(){var e,t,a=this,i=a.elem,r=i.elements,s=r.length,n=!0;for(i.isValid=!0,e=0;s>e;e++)t=r[e],t.isRequired=!!t.required,t.isDisabled=!!t.disabled,t.isDisabled||(n=a.validateField(t),i.isValid&&!n&&a.setFocusOn(t),i.isValid=n&&i.isValid);return a.options.doRenderMessage&&a.renderErrorMessages(a,i),i.isValid},validateField:function(t){var r,s,n,o=t.target||t,l=!1,u=!1,d=!1,p=!1;return o.form===a?null:(r=o.form.H5Form,s=e(o),u=!!s.attr("required"),d=!!s.attr("disabled"),o.isDisabled||(l=!i.attributes.required&&u&&r.isValueMissing(r,o),p=!i.attributes.pattern&&r.matchPattern(r,o)),o.validityState={valueMissing:l,patternMismatch:p,valid:o.isDisabled||!(l||p)},i.attributes.required||(o.validityState.valueMissing?s.addClass(r.options.requiredClass):s.removeClass(r.options.requiredClass)),i.attributespattern||(o.validityState.patternMismatch?s.addClass(r.options.patternClass):s.removeClass(r.options.patternClass)),o.validityState.valid?(s.removeClass(r.options.invalidClass),n=r.findLabel(s),n.removeClass(r.options.invalidClass),n.attr("aria-invalid","false")):(s.addClass(r.options.invalidClass),n=r.findLabel(s),n.addClass(r.options.invalidClass)),o.validityState.valid)},isValueMissing:function(r,s){var n,o,l,u=e(s),d=s.type!==a?s.type:s.tagName.toLowerCase(),p=/^(checkbox|radio|fieldset)$/i.test(d),f=/^submit$/i.test(d);if(f)return!1;if(p){if("checkbox"===d)return!u.is(":checked");for(n="fieldset"===d?u.find("input"):t.getElementsByName(s.name),o=0,l=n.length;l>o;o++)if(e(n[o]).is(":checked"))return!1;return!0}return!(""!==u.val()&&(i.attributes.placeholder||!u.hasClass(r.options.placeholderClass)))},matchPattern:function(t,r){var s,n,o=e(r),l=o.attr("value"),u=o.attr("pattern"),d=o.attr("type");if(!i.attributes.placeholder&&o.attr("placeholder")&&o.hasClass(t.options.placeholderClass)||(l=o.attr("value")),""===l)return!1;if("email"===d){if(o.attr("multiple")===a)return!t.options.emailPatt.test(l);for(l=l.split(t.options.mutipleDelimiter),s=0,n=l.length;n>s;s++)if(!t.options.emailPatt.test(l[s].replace(/[ ]*/g,"")))return!0}else{if("url"===d)return!t.options.urlPatt.test(l);if("text"===d&&u!==a)return usrPatt=new RegExp("^(?:"+u+")$"),!usrPatt.test(l)}return!1},placeholder:function(t,r,s){var n=e(r),o=n.attr("placeholder"),l=/^(focusin|submit)$/i.test(s),u=/^(input|textarea)$/i.test(r.nodeName),d=/^password$/i.test(r.type),p=i.attributes.placeholder;p||!u||d||o===a||(""!==r.value||l?r.value===o&&l&&(r.value="",n.removeClass(t.options.placeholderClass)):(r.value=o,n.addClass(t.options.placeholderClass)))},numberType:function(t,a){var r,s,n,o,l,u,d,p,f=e(a),c=f.attr("type"),m=/^input$/i.test(a.nodeName),h=/^(number|range)$/i.test(c);if(!(!m||!h||"number"==c&&i.fields.number||"range"==c&&i.fields.range)){for(r=parseInt(f.attr("min")),s=parseInt(f.attr("max")),n=parseInt(f.attr("step")),o=parseInt(f.attr("value")),l=f.prop("attributes"),u=e("<select>"),r=isNaN(r)?-100:r,p=r;s>=p;p+=n)d=e('<option value="'+p+'">'+p+"</option>"),(o==p||o>p&&p+n>o)&&d.attr("selected",""),u.append(d);e.each(l,function(){u.attr(this.name,this.value)}),f.replaceWith(u)}},autofocus:function(t,a){var r=e(a),s=!!r.attr("autofocus"),n=/^(input|textarea|select|fieldset)$/i.test(a.nodeName),o=/^submit$/i.test(a.type),l=i.attributes.autofocus;!l&&n&&!o&&s&&e(function(){t.setFocusOn(a)})},findLabel:function(t){var a,i=e('label[for="'+t.attr("id")+'"]');return i.length<=0&&(a=t.parent(),"label"==a.get(0).tagName.toLowerCase()&&(i=a)),i},setFocusOn:function(t){"fieldset"===t.tagName.toLowerCase()?e(t).find(":first").focus():e(t).focus()},renderErrorMessages:function(t,a){for(var i,r,s=a.elements,n=s.length,o={errors:[]};n--;)i=e(s[n]),r=t.findLabel(i),i.hasClass(t.options.requiredClass)&&(o.errors[n]=r.text().replace("*","")+t.options.requiredMessage),i.hasClass(t.options.patternClass)&&(o.errors[n]=r.text().replace("*","")+t.options.patternMessage);o.errors.length>0&&Joomla.renderMessages(o)}};e.fn.h5f=function(e){return this.each(function(){Object.create(r).init(e,this)})},e.fn.h5f.options={invalidClass:"invalid",requiredClass:"required",requiredMessage:" is required.",placeholderClass:"placeholder",patternClass:"pattern",patternMessage:" doesn't match pattern.",doRenderMessage:!1,formValidationEvent:"onSubmit",emailPatt:/^[a-zA-Z0-9.!#$%&‚Äô*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/,urlPatt:/[a-z][\-\.+a-z]*:\/\//i},e(function(){e("form").h5f({doRenderMessage:!0,requiredClass:"musthavevalue"})})}(jQuery,document);
