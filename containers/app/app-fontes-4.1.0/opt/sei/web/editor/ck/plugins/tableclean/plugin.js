﻿CKEDITOR.env.ie&&9>CKEDITOR.env.version&&(Array.prototype.indexOf=function(a,c){for(var d=c||0,b=this.length;d<b;d++)if(this[d]===a)return d;return-1});function cleanAttribute(a){var c=["width","style","moz_resizing","colspan","rowspan"];"TABLE"==a.nodeName?(c.push("class"),c.push("border")):c.push("align");var d=[];a=a.attributes;var b=a.length;if(0<b){for(var e=0;e<b;e++)-1==c.indexOf(a[e].name.toLowerCase())&&d.push(a[e].name);for(c=0;c<d.length;c++)a.removeNamedItem(d[c])}}
function cleanStyle(a){var c="width font-family font-size font-weight text-align border-spacing".split(" "),d=[];a=a.style;var b=a.length;if(CKEDITOR.env.ie&&9>CKEDITOR.env.version)if(0==a.cssText.length)b=0;else var e=a.cssText.split(";"),b=e.length;var f;if(0<b){for(var g=0;g<b;g++)e?(f=e[g].split(":")[0].toLowerCase(),-1!=c.indexOf(f)&&d.push(e[g])):(f=a[g],-1==c.indexOf(f)&&d.push(f));if(e)a.cssText=d.join(";");else for(c=0;c<d.length;c++)a.removeProperty(d[c])}}
function cleanTable(a){"TABLE"==a.nodeName&&(CKEDITOR.env.ie?cleanElement(a.children[0]):cleanElement(a.firstElementChild),cleanAttribute(a),cleanStyle(a))}
function cleanElement(a){var c="P DIV SPAN EM U S SUP SUB STRONG".split(" ");-1!==["THEAD","TBODY","TR","TD","TH"].indexOf(a.nodeName)&&(cleanAttribute(a),cleanStyle(a));CKEDITOR.env.ie?(null!=a.childNodes[0]&&cleanElement(a.childNodes[0]),null!=a.nextSibling&&cleanElement(a.nextSibling)):(null!=a.firstElementChild&&cleanElement(a.firstElementChild),null!=a.nextElementSibling&&cleanElement(a.nextElementSibling));if(-1!==["COLGROUP","COL"].indexOf(a.nodeName))CKEDITOR.env.ie?a.removeNode():a.remove();
else if(-1!==c.indexOf(a.nodeName))if(CKEDITOR.env.ie){for(;null!=a.childNodes[0];)a.parentElement.insertBefore(a.childNodes[0],a);a.removeNode()}else{for(;null!=a.firstChild;)a.parentElement.insertBefore(a.firstChild,a);a.remove()}}
CKEDITOR.plugins.add("tableclean",{requires:["table"],init:function(a){a.addCommand("tableClearFormat",{exec:function(a){a=a.getSelection();var d=a.getRanges(),b=null;(b=a.getSelectedElement())?b=b.getAscendant("table",!0):0<d.length&&(CKEDITOR.env.webkit&&d[0].shrink(CKEDITOR.NODE_ELEMENT),b=d[0].getCommonAncestor(!0).getAscendant("table",!0));this._.selectedElement=b;cleanTable(b.$);b.setAttribute("border","1");b.setAttribute("cellpadding","4");b.setStyle("border-collapse","collapse");b.setStyle("margin-left",
"auto");b.setStyle("margin-right","auto");b.removeClass("cke_show_border")}});a.addMenuItems&&a.addMenuItems({tableclean:{label:"Limpar Formatação",command:"tableClearFormat",group:"table",order:4}});a.contextMenu&&a.contextMenu.addListener(function(a,d){return!a||a.isReadOnly()?null:a.hasAscendant("table",1)?{tableclean:CKEDITOR.TRISTATE_OFF}:null})}});