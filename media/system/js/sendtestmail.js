jQuery(document).ready(function(e){e("#sendtestmail").click(function(){var s={smtpauth:e('input[name="jform[smtpauth]"]:checked').val(),smtpuser:e('input[name="jform[smtpuser]"]').val(),smtppass:e('input[name="jform[smtppass]"]').val(),smtphost:e('input[name="jform[smtphost]"]').val(),smtpsecure:e('select[name="jform[smtpsecure]"]').val(),smtpport:e('input[name="jform[smtpport]"]').val(),mailfrom:e('input[name="jform[mailfrom]"]').val(),fromname:e('input[name="jform[fromname]"]').val(),mailer:e('select[name="jform[mailer]"]').val(),mailonline:e('input[name="jform[mailonline]"]:checked').val()};e.ajax({method:"POST",url:sendtestmail_url,data:s,dataType:"json"}).fail(function(e,s){var a={};if("parsererror"==s){for(var o=e.responseText.trim(),m=[],r=o.length-1;r>=0;r--)m.unshift(["&#",o[r].charCodeAt(),";"].join(""));o=m.join(""),a.error=[Joomla.JText._("COM_CONFIG_SENDMAIL_JS_ERROR_PARSE").replace("%s",o)]}else"nocontent"==s?a.error=[Joomla.JText._("COM_CONFIG_SENDMAIL_JS_ERROR_NO_CONTENT")]:"timeout"==s?a.error=[Joomla.JText._("COM_CONFIG_SENDMAIL_JS_ERROR_TIMEOUT")]:"abort"==s?a.error=[Joomla.JText._("COM_CONFIG_SENDMAIL_JS_ERROR_CONNECTION_ABORT")]:a.error=[Joomla.JText._("COM_CONFIG_SENDMAIL_JS_ERROR_OTHER").replace("%s",e.status)];Joomla.renderMessages(a)}).done(function(e){var s={};e.data?"object"==typeof e.messages&&"undefined"!=typeof e.messages.success&&e.messages.success.length>0&&(s.success=[e.messages.success]):"object"==typeof e.messages&&("undefined"!=typeof e.messages.error&&e.messages.error.length>0&&(s.error=[e.messages.error]),"undefined"!=typeof e.messages.notice&&e.messages.notice.length>0&&(s.notice=[e.messages.notice]),"undefined"!=typeof e.messages.message&&e.messages.message.length>0&&(s.message=[e.messages.message])),Joomla.renderMessages(s)}),window.scrollTo(0,0)})});