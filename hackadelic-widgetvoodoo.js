;String.prototype.format=function(){var s=this;for(i=0;i<arguments.length;i++)
s=s.replace(new RegExp('\\{'+i+'\\}','gi'),arguments[i]);return s;}
jQuery(document).ready(function(){var ws=WidgetVoodooSelectors.widget;var ts=WidgetVoodooSelectors.title;var cs=WidgetVoodooSelectors.autocollapse;var widgets=jQuery(ws);widgets.each(function(index,elem){var self=jQuery(this);var title=jQuery(ts,self);if(!title.length||!title.html().replace('&nbsp;',''))return true;var body=self.children(':not('+ts+')');var wid='widgetbody-'+index;if(body.length>1){var wrap=jQuery('<DIV class="widgetbody {0}">{1}</DIV>'.format(wid,self.html()));title=jQuery(ts,wrap);self.html(wrap);title.insertBefore(wrap);}
else
body.addClass('widgetbody '+wid);title.addClass('collapsible');self.addClass('collapsible');title.click(function(){jQuery(ws+' .'+wid).slideToggle('fast');jQuery(this).toggleClass('collapsed');});});if(cs){jQuery.each(cs.split(/\s*,\s*/),function(i,it){jQuery(it+' .widgetbody').hide();jQuery(it+' '+ts).addClass('collapsed');});}});