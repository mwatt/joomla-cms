Joomla=window.Joomla||{},Joomla.editors=Joomla.editors||{},Joomla.editors.instances=Joomla.editors.instances||{},function(e,t){"use strict";e.submitform=function(e,n,o){n||(n=t.getElementById("adminForm")),e&&(n.task.value=e),n.noValidate=!o,n.setAttribute("novalidate",!o);var i=t.createElement("input");i.style.display="none",i.type="submit",n.appendChild(i).click(),n.removeChild(i)},e.submitbutton=function(t){e.submitform(t)},e.JText={strings:{},_:function(e,t){return"undefined"!=typeof this.strings[e.toUpperCase()]?this.strings[e.toUpperCase()]:t},load:function(e){for(var t in e)e.hasOwnProperty(t)&&(this.strings[t.toUpperCase()]=e[t]);return this}},e.replaceTokens=function(e){if(/^[0-9A-F]{32}$/i.test(e)){var n,o,i,r=t.getElementsByTagName("input");for(n=0,i=r.length;i>n;n++)o=r[n],"hidden"==o.type&&"1"==o.value&&32==o.name.length&&(o.name=e)}},e.isEmail=function(e){var t=/^[\w.!#$%&‚Äô*+\/=?^`{|}~-]+@[a-z0-9-]+(?:\.[a-z0-9-]{2,})+$/i;return t.test(e)},e.checkAll=function(e,t){if(!e.form)return!1;t=t?t:"cb";var n,o,i,r=0;for(n=0,i=e.form.elements.length;i>n;n++)o=e.form.elements[n],o.type==e.type&&0===o.id.indexOf(t)&&(o.checked=e.checked,r+=o.checked?1:0);return e.form.boxchecked&&(e.form.boxchecked.value=r),!0},e.renderMessages=function(n){e.removeMessages();var o,i,r,a,l,s,d,c=t.getElementById("system-message-container");for(o in n)if(n.hasOwnProperty(o)){for(i=n[o],r=t.createElement("div"),r.className="alert alert-"+o,a=e.JText._(o),"undefined"!=typeof a&&(l=t.createElement("h4"),l.className="alert-heading",l.innerHTML=e.JText._(o),r.appendChild(l)),s=i.length-1;s>=0;s--)d=t.createElement("div"),d.innerHTML=i[s],r.appendChild(d);c.appendChild(r)}},e.removeMessages=function(){for(var e=t.getElementById("system-message-container");e.firstChild;)e.removeChild(e.firstChild);e.style.display="none",e.offsetHeight,e.style.display=""},e.isChecked=function(e,n){if("undefined"==typeof n&&(n=t.getElementById("adminForm")),n.boxchecked.value=e?parseInt(n.boxchecked.value)+1:parseInt(n.boxchecked.value)-1,n.elements["checkall-toggle"]){var o,i,r,a=!0;for(o=0,r=n.elements.length;r>o;o++)if(i=n.elements[o],"checkbox"==i.type&&"checkall-toggle"!=i.name&&!i.checked){a=!1;break}n.elements["checkall-toggle"].checked=a}},e.popupWindow=function(e,t,n,o,i){var r=(screen.width-n)/2,a=(screen.height-o)/2,l="height="+o+",width="+n+",top="+a+",left="+r+",scrollbars="+i+",resizable";window.open(e,t,l).window.focus()},e.tableOrdering=function(n,o,i,r){"undefined"==typeof r&&(r=t.getElementById("adminForm")),r.filter_order.value=n,r.filter_order_Dir.value=o,e.submitform(i,r)},window.writeDynaList=function(e,n,o,i,r){var a,l,s,d="<select "+e+">",c=o==i,u=0;for(l in n)n.hasOwnProperty(l)&&(s=n[l],s[0]==o&&(a="",(c&&r==s[1]||!c&&0===u)&&(a='selected="selected"'),d+='<option value="'+s[1]+'" '+a+">"+s[2]+"</option>",u++));d+="</select>",t.writeln(d)},window.changeDynaList=function(e,n,o,i,r){for(var a,l,s,d,c=t.adminForm[e],u=o==i;c.firstChild;)c.removeChild(c.firstChild);a=0;for(l in n)n.hasOwnProperty(l)&&(s=n[l],s[0]==o&&(d=new Option,d.value=s[1],d.text=s[2],(u&&r==d.value||!u&&0===a)&&(d.selected=!0),c.options[a++]=d));c.length=a},window.radioGetCheckedValue=function(e){if(!e)return"";var t,n=e.length;if(void 0===n)return e.checked?e.value:"";for(t=0;n>t;t++)if(e[t].checked)return e[t].value;return""},window.getSelectedValue=function(e,n){var o=t[e][n],i=o.selectedIndex;return null!==i&&i>-1?o.options[i].value:null},window.listItemTask=function(e,n){var o,i=t.adminForm,r=0,a=i[e];if(!a)return!1;for(;;){if(o=i["cb"+r],!o)break;o.checked=!1,r++}return a.checked=!0,i.boxchecked.value=1,window.submitform(n),!1},window.submitbutton=function(t){e.submitbutton(t)},window.submitform=function(t){e.submitform(t)},window.saveorder=function(e,t){window.checkAll_button(e,t)},window.checkAll_button=function(n,o){o=o?o:"saveorder";var i,r;for(i=0;n>=i;i++){if(r=t.adminForm["cb"+i],!r)return void alert("You cannot change the order of items, as an item in the list is `Checked Out`");r.checked=!0}e.submitform(o)},e.loadingLayer=function(n,o){if(n=n||"show",o=o||t.body,"load"==n){var i=t.getElementsByTagName("body")[0].getAttribute("data-basepath")||"",r=t.createElement("div");r.id="loading-logo",r.style.position="fixed",r.style.top="0",r.style.left="0",r.style.width="100%",r.style.height="100%",r.style.opacity="0.8",r.style.filter="alpha(opacity=80)",r.style.overflow="hidden",r.style["z-index"]="10000",r.style.display="none",r.style["background-color"]="#fff",r.style["background-image"]='url("'+i+'/media/jui/images/ajax-loader.gif")',r.style["background-position"]="center",r.style["background-repeat"]="no-repeat",r.style["background-attachment"]="fixed",o.appendChild(r)}else t.getElementById("loading-logo")||e.loadingLayer("load",o),t.getElementById("loading-logo").style.display="show"==n?"block":"none";return t.getElementById("loading-logo")}}(Joomla,document);
