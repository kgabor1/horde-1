var ImpContacts={_passAddresses:function(){var a="";$("selected_addresses").childElements().each(function(b){if(b.value){a+=b.value+"|"}});$("sa").setValue(a)},sameOption:function(e,d,a){var c=e+": "+d.value,b=a.value;return Try.these(function(){return(c==b)||(decodeURIComponent(c)==decodeURIComponent(b))},function(){return(c==b)})},addAddress:function(e){var g,a,c,b=$("search_results");if(!$F(b).size()){alert(IMP.text.contacts_select)}else{g=$("selected_addresses");a=$A(g).size();b.childElements().each(function(d){if(d.value&&d.selected){if(!$A(g).any(function(f){return this.sameOption(e,d,f)},this)){c=e+": "+d.value;g[a++]=new Option(c,c)}}},this)}},updateMessage:function(){if(parent.opener.closed){alert(IMP.text.contacts_closed);window.close();return}if(!parent.opener.document[this.formname]){alert(IMP.text.contacts_called);window.close();return}$("selected_addresses").childElements().each(function(c){var a=c.value,d,e=null,g,b;g=a.indexOf(":");d=a.substring(0,g);a=a.substring(g+2,a.length);if(d=="to"){e=parent.opener.document[this.formname].to}else{if(!this.to_only&&d=="cc"){e=parent.opener.document[this.formname].cc}else{if(!this.to_only&&d=="bcc"){e=parent.opener.document[this.formname].bcc}}}if(!e){return}if(e.value.length){b=e.value.replace(/, +/g,",").split(",").findAll(function(f){return f});e.value=b.join(", ");if(e.value.lastIndexOf(";")!=e.value.length-1){e.value+=","}e.value+=" "+a}else{e.value=a}if(a.lastIndexOf(";")!=a.length-1){e.value+=", "}},this);window.close()},removeAddress:function(){$("selected_addresses").childElements().each(function(a){if(a.selected){a.remove()}})},onDomLoad:function(){$("contacts").observe("submit",this._passAddresses.bind(this));document.observe("change",this._changeHandler.bindAsEventListener(this));document.observe("click",this._clickHandler.bindAsEventListener(this));document.observe("dblclick",this._dblclickHandler.bindAsEventListener(this))},_changeHandler:function(a){var b=a.element().readAttribute("id");switch(b){case"search_results":$(b)[0].selected=false;break}},_clickHandler:function(a){var b=a.element().readAttribute("id");switch(b){case"btn_add_to":case"btn_add_cc":case"btn_add_bcc":this.addAddress(b.substring(8));break;case"btn_update":this.updateMessage();break;case"btn_delete":this.removeAddress();break;case"btn_cancel":window.close();break}},_dblclickHandler:function(b){var a=b.element();if(!a.match("SELECT")){a=a.up("SELECT")}switch(a.readAttribute("id")){case"search_results":this.addAddress("to");break;case"selected_addresses":this.removeAddress();break}}};document.observe("dom:loaded",ImpContacts.onDomLoad.bind(ImpContacts));