tinyMCE.importPluginLanguagePack('template','en,tr,he,nb,ru,ru_KOI8-R,ru_UTF-8,nn,fi,cy,es,is,pl');var TinyMCE_TemplatePlugin={getInfo:function(){return{longname:'Template plugin',author:'Your name',authorurl:'http://www.yoursite.com',infourl:'http://www.yoursite.com/docs/template.html',version:"1.0"};},initInstance:function(inst){alert("Initialization parameter:"+tinyMCE.getParam("template_someparam",false));inst.addShortcut('ctrl','t','lang_template_desc','mceTemplate');},getControlHTML:function(cn){switch(cn){case"template":return tinyMCE.getButtonHTML(cn,'lang_template_desc','{$pluginurl}/images/template.gif','mceTemplate',true);}return"";},execCommand:function(editor_id,element,command,user_interface,value){switch(command){case"mceTemplate":if(user_interface){var template=new Array();template['file']='../../plugins/template/popup.htm';template['width']=300;template['height']=200;tinyMCE.openWindow(template,{editor_id:editor_id,some_custom_arg:"somecustomdata"});tinyMCE.triggerNodeChange(false);}else{alert("execCommand: mceTemplate gets called from popup.");}return true;}return false;},handleNodeChange:function(editor_id,node,undo_index,undo_levels,visual_aid,any_selection){if(node.parentNode.nodeName=="STRONG"||node.parentNode.nodeName=="B"){tinyMCE.switchClass(editor_id+'_template','mceButtonSelected');return true;}tinyMCE.switchClass(editor_id+'_template','mceButtonNormal');},setupContent:function(editor_id,body,doc){},onChange:function(inst){},handleEvent:function(e){top.status="template plugin event: "+e,type;return true;},cleanup:function(type,content,inst){switch(type){case"get_from_editor":alert("[FROM] Value HTML string: "+content);break;case"insert_to_editor":alert("[TO] Value HTML string: "+content);break;case"get_from_editor_dom":alert("[FROM] Value DOM Element "+content.innerHTML);break;case"insert_to_editor_dom":alert("[TO] Value DOM Element: "+content.innerHTML);break;}return content;},_someInternalFunction:function(a,b){return 1;}};tinyMCE.addPlugin("template",TinyMCE_TemplatePlugin);