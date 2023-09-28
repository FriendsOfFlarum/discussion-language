/*! For license information please see admin.js.LICENSE.txt */
(()=>{var t={195:(t,e,n)=>{t.exports=n(236)},236:t=>{var e=function(t){"use strict";var e,n=Object.prototype,r=n.hasOwnProperty,o="function"==typeof Symbol?Symbol:{},a=o.iterator||"@@iterator",i=o.asyncIterator||"@@asyncIterator",s=o.toStringTag||"@@toStringTag";function c(t,e,n){return Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}),t[e]}try{c({},"")}catch(t){c=function(t,e,n){return t[e]=n}}function u(t,e,n,r){var o=e&&e.prototype instanceof h?e:h,a=Object.create(o.prototype),i=new O(r||[]);return a._invoke=function(t,e,n){var r=f;return function(o,a){if(r===g)throw new Error("Generator is already running");if(r===m){if("throw"===o)throw a;return k()}for(n.method=o,n.arg=a;;){var i=n.delegate;if(i){var s=E(i,n);if(s){if(s===p)continue;return s}}if("next"===n.method)n.sent=n._sent=n.arg;else if("throw"===n.method){if(r===f)throw r=m,n.arg;n.dispatchException(n.arg)}else"return"===n.method&&n.abrupt("return",n.arg);r=g;var c=l(t,e,n);if("normal"===c.type){if(r=n.done?m:d,c.arg===p)continue;return{value:c.arg,done:n.done}}"throw"===c.type&&(r=m,n.method="throw",n.arg=c.arg)}}}(t,n,i),a}function l(t,e,n){try{return{type:"normal",arg:t.call(e,n)}}catch(t){return{type:"throw",arg:t}}}t.wrap=u;var f="suspendedStart",d="suspendedYield",g="executing",m="completed",p={};function h(){}function v(){}function y(){}var b={};c(b,a,(function(){return this}));var w=Object.getPrototypeOf,_=w&&w(w(D([])));_&&_!==n&&r.call(_,a)&&(b=_);var x=y.prototype=h.prototype=Object.create(b);function L(t){["next","throw","return"].forEach((function(e){c(t,e,(function(t){return this._invoke(e,t)}))}))}function j(t,e){function n(o,a,i,s){var c=l(t[o],t,a);if("throw"!==c.type){var u=c.arg,f=u.value;return f&&"object"==typeof f&&r.call(f,"__await")?e.resolve(f.__await).then((function(t){n("next",t,i,s)}),(function(t){n("throw",t,i,s)})):e.resolve(f).then((function(t){u.value=t,i(u)}),(function(t){return n("throw",t,i,s)}))}s(c.arg)}var o;this._invoke=function(t,r){function a(){return new e((function(e,o){n(t,r,e,o)}))}return o=o?o.then(a,a):a()}}function E(t,n){var r=t.iterator[n.method];if(r===e){if(n.delegate=null,"throw"===n.method){if(t.iterator.return&&(n.method="return",n.arg=e,E(t,n),"throw"===n.method))return p;n.method="throw",n.arg=new TypeError("The iterator does not provide a 'throw' method")}return p}var o=l(r,t.iterator,n.arg);if("throw"===o.type)return n.method="throw",n.arg=o.arg,n.delegate=null,p;var a=o.arg;return a?a.done?(n[t.resultName]=a.value,n.next=t.nextLoc,"return"!==n.method&&(n.method="next",n.arg=e),n.delegate=null,p):a:(n.method="throw",n.arg=new TypeError("iterator result is not an object"),n.delegate=null,p)}function N(t){var e={tryLoc:t[0]};1 in t&&(e.catchLoc=t[1]),2 in t&&(e.finallyLoc=t[2],e.afterLoc=t[3]),this.tryEntries.push(e)}function S(t){var e=t.completion||{};e.type="normal",delete e.arg,t.completion=e}function O(t){this.tryEntries=[{tryLoc:"root"}],t.forEach(N,this),this.reset(!0)}function D(t){if(t){var n=t[a];if(n)return n.call(t);if("function"==typeof t.next)return t;if(!isNaN(t.length)){var o=-1,i=function n(){for(;++o<t.length;)if(r.call(t,o))return n.value=t[o],n.done=!1,n;return n.value=e,n.done=!0,n};return i.next=i}}return{next:k}}function k(){return{value:e,done:!0}}return v.prototype=y,c(x,"constructor",y),c(y,"constructor",v),v.displayName=c(y,s,"GeneratorFunction"),t.isGeneratorFunction=function(t){var e="function"==typeof t&&t.constructor;return!!e&&(e===v||"GeneratorFunction"===(e.displayName||e.name))},t.mark=function(t){return Object.setPrototypeOf?Object.setPrototypeOf(t,y):(t.__proto__=y,c(t,s,"GeneratorFunction")),t.prototype=Object.create(x),t},t.awrap=function(t){return{__await:t}},L(j.prototype),c(j.prototype,i,(function(){return this})),t.AsyncIterator=j,t.async=function(e,n,r,o,a){void 0===a&&(a=Promise);var i=new j(u(e,n,r,o),a);return t.isGeneratorFunction(n)?i:i.next().then((function(t){return t.done?t.value:i.next()}))},L(x),c(x,s,"Generator"),c(x,a,(function(){return this})),c(x,"toString",(function(){return"[object Generator]"})),t.keys=function(t){var e=[];for(var n in t)e.push(n);return e.reverse(),function n(){for(;e.length;){var r=e.pop();if(r in t)return n.value=r,n.done=!1,n}return n.done=!0,n}},t.values=D,O.prototype={constructor:O,reset:function(t){if(this.prev=0,this.next=0,this.sent=this._sent=e,this.done=!1,this.delegate=null,this.method="next",this.arg=e,this.tryEntries.forEach(S),!t)for(var n in this)"t"===n.charAt(0)&&r.call(this,n)&&!isNaN(+n.slice(1))&&(this[n]=e)},stop:function(){this.done=!0;var t=this.tryEntries[0].completion;if("throw"===t.type)throw t.arg;return this.rval},dispatchException:function(t){if(this.done)throw t;var n=this;function o(r,o){return s.type="throw",s.arg=t,n.next=r,o&&(n.method="next",n.arg=e),!!o}for(var a=this.tryEntries.length-1;a>=0;--a){var i=this.tryEntries[a],s=i.completion;if("root"===i.tryLoc)return o("end");if(i.tryLoc<=this.prev){var c=r.call(i,"catchLoc"),u=r.call(i,"finallyLoc");if(c&&u){if(this.prev<i.catchLoc)return o(i.catchLoc,!0);if(this.prev<i.finallyLoc)return o(i.finallyLoc)}else if(c){if(this.prev<i.catchLoc)return o(i.catchLoc,!0)}else{if(!u)throw new Error("try statement without catch or finally");if(this.prev<i.finallyLoc)return o(i.finallyLoc)}}}},abrupt:function(t,e){for(var n=this.tryEntries.length-1;n>=0;--n){var o=this.tryEntries[n];if(o.tryLoc<=this.prev&&r.call(o,"finallyLoc")&&this.prev<o.finallyLoc){var a=o;break}}a&&("break"===t||"continue"===t)&&a.tryLoc<=e&&e<=a.finallyLoc&&(a=null);var i=a?a.completion:{};return i.type=t,i.arg=e,a?(this.method="next",this.next=a.finallyLoc,p):this.complete(i)},complete:function(t,e){if("throw"===t.type)throw t.arg;return"break"===t.type||"continue"===t.type?this.next=t.arg:"return"===t.type?(this.rval=this.arg=t.arg,this.method="return",this.next="end"):"normal"===t.type&&e&&(this.next=e),p},finish:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var n=this.tryEntries[e];if(n.finallyLoc===t)return this.complete(n.completion,n.afterLoc),S(n),p}},catch:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var n=this.tryEntries[e];if(n.tryLoc===t){var r=n.completion;if("throw"===r.type){var o=r.arg;S(n)}return o}}throw new Error("illegal catch attempt")},delegateYield:function(t,n,r){return this.delegate={iterator:D(t),resultName:n,nextLoc:r},"next"===this.method&&(this.arg=e),p}},t}(t.exports);try{regeneratorRuntime=e}catch(t){"object"==typeof globalThis?globalThis.regeneratorRuntime=e:Function("r","regeneratorRuntime = r")(e)}},879:t=>{t.exports=function(t){for(var e=[],n=0,r=0,o=0,a=t.length;o<a;)n=t.charCodeAt(o++),r?(e.push((65536+(r-55296<<10)+(n-56320)).toString(16)),r=0):55296<=n&&n<=56319?r=n:e.push(n.toString(16));return e.join("-")}}},e={};function n(r){var o=e[r];if(void 0!==o)return o.exports;var a=e[r]={exports:{}};return t[r](a,a.exports,n),a.exports}n.n=t=>{var e=t&&t.__esModule?()=>t.default:()=>t;return n.d(e,{a:e}),e},n.d=(t,e)=>{for(var r in e)n.o(e,r)&&!n.o(t,r)&&Object.defineProperty(t,r,{enumerable:!0,get:e[r]})},n.o=(t,e)=>Object.prototype.hasOwnProperty.call(t,e),n.r=t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})};var r={};(()=>{"use strict";n.r(r),n.d(r,{components:()=>G,extend:()=>rt,models:()=>H,utils:()=>z});var t={};n.r(t),n.d(t,{default:()=>V,getEmoji:()=>M,load:()=>T});const e=flarum.core.compat["admin/app"];var o=n.n(e);const a=flarum.core.compat["admin/components/SettingDropdown"];var i=n.n(a);function s(t,e,n,r,o,a,i){try{var s=t[a](i),c=s.value}catch(t){return void n(t)}s.done?e(c):Promise.resolve(c).then(r,o)}function c(t){return function(){var e=this,n=arguments;return new Promise((function(r,o){var a=t.apply(e,n);function i(t){s(a,r,o,i,c,"next",t)}function c(t){s(a,r,o,i,c,"throw",t)}i(void 0)}))}}function u(t,e){return u=Object.setPrototypeOf||function(t,e){return t.__proto__=e,t},u(t,e)}function l(t,e){t.prototype=Object.create(e.prototype),t.prototype.constructor=t,u(t,e)}var f=n(195),d=n.n(f);const g=flarum.core.compat["admin/components/ExtensionPage"];var p=n.n(g);const h=flarum.core.compat["common/components/Button"];var v=n.n(h);const y=flarum.core.compat["common/components/Select"];var b=n.n(y);const w=flarum.core.compat["common/components/LoadingIndicator"];var _=n.n(w);const x=flarum.core.compat["common/helpers/icon"];var L=n.n(x);const j=flarum.core.compat["common/utils/Stream"];var E=n.n(j);const N=flarum.core.compat["common/components/Alert"];var S,O=n.n(N);function D(t){return k.apply(this,arguments)}function k(){return(k=c(d().mark((function t(e){var n,r;return d().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return n=o().forum.attribute("baseUrl")+"/assets/extensions/fof-discussion-language/"+e,t.next=3,fetch(n);case 3:if((r=t.sent).ok){t.next=7;break}if(404!==r.status){t.next=7;break}throw new Error("Missing asset (`"+e+"`): "+n);case 7:return t.next=9,r.json();case 9:return t.abrupt("return",t.sent);case 10:case"end":return t.stop()}}),t)})))).apply(this,arguments)}var P=function(){var t=c(d().mark((function t(){return d().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,D("iso-639-2.json");case 2:return S=t.sent,t.abrupt("return",S);case 4:case"end":return t.stop()}}),t)})));return function(){return t.apply(this,arguments)}}(),C=function(t,e){return e&&(null==t?void 0:t["Native name(s)"])||(null==t?void 0:t["Language name(s)"])};const F=function(t){var e;return null==(e=Array.from(Object.values(S||{})))?void 0:e.sort((function(e,n){var r,o;return(null==(r=C(e,t))?void 0:r.toLowerCase())>(null==(o=C(n,t))?void 0:o.toLowerCase())})).reduce((function(e,n){return e[n["639-1"]||n["639-2"]]=C(n,t),e}),{})};var A,T=function(){var t=c(d().mark((function t(){return d().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,D("countries.json");case 2:return A=t.sent,t.abrupt("return",A);case 4:case"end":return t.stop()}}),t)})));return function(){return t.apply(this,arguments)}}();function V(t){if(!A)return{};var e=t?"native":"name";return Object.entries(A).reduce((function(t,n){var r=n[0],o=n[1];return t[r]=o[e],t}),{})}var M=function(t){var e,n;return null==(e=A)||null==(n=e[t])?void 0:n.emoji},R=function(t){function e(){for(var e,n=arguments.length,r=new Array(n),o=0;o<n;o++)r[o]=arguments[o];return(e=t.call.apply(t,[this].concat(r))||this).recordsUpdating={},e.recordsDeleting={},e.newLocaleValue=E()(""),e.newCountryValue=E()(""),e.loadingData=!0,e.loadingDataError=!1,e.errorDetails=null,e.isAddingNewRecord=!1,e}l(e,t);var n=e.prototype;return n.oncreate=function(e){t.prototype.oncreate.call(this,e),this.loadData()},n.loadData=function(){var t=c(d().mark((function t(){return d().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return this.loadingData=!0,this.loadingDataError=!1,m.redraw(),t.prev=3,t.next=6,Promise.all([P(),T()]);case 6:this.loadingData=!1,this.loadingDataError=!1,m.redraw(),t.next=18;break;case 11:t.prev=11,t.t0=t.catch(3),console.error(t.t0),this.loadingData=!1,this.loadingDataError=!0,this.errorDetails=t.t0,m.redraw();case 18:case"end":return t.stop()}}),t,this,[[3,11]])})));return function(){return t.apply(this,arguments)}}(),n.content=function(t){return m("div",{className:"container"},m("div",{className:"FofDiscussionLanguagesSettingsPage"},m("div",{className:"Form-group"},this.buildSettingComponent({type:"bool",label:o().translator.trans("fof-discussion-language.admin.settings.native_label"),setting:"fof-discussion-language.native"})),m("div",{className:"Form-group"},this.buildSettingComponent({type:"bool",label:o().translator.trans("fof-discussion-language.admin.settings.show_flag_label"),setting:"fof-discussion-language.showFlags"})),m("div",{className:"Form-group"},this.buildSettingComponent({type:"bool",label:o().translator.trans("fof-discussion-language.admin.settings.composer_default_label"),setting:"fof-discussion-language.composerLocaleDefault"})),m("div",{className:"Form-group"},this.buildSettingComponent({type:"bool",label:o().translator.trans("fof-discussion-language.admin.settings.locale_sort_label"),setting:"fof-discussion-language.filter_language_on_http_request"})),m("div",{className:"Form-group"},this.buildSettingComponent({type:"bool",label:o().translator.trans("fof-discussion-language.admin.settings.show_any_lang_opt_label"),setting:"fof-discussion-language.showAnyLangOpt"})),m("div",{className:"Form-group"},this.buildSettingComponent({type:"bool",label:o().translator.trans("fof-discussion-language.admin.settings.tags_page_discussion_locale_label"),setting:"fof-discussion-language.useLocaleForTagsPageLastDiscussion"})),this.submitButton(t),m("hr",null),this.localeSettings(),m("p",{className:"helpText"},"This extension uses material from the Wikipedia article"," ",m("a",{href:"https://en.wikipedia.org/wiki/List_of_ISO_639-2_codes",target:"_blank",rel:"noreferrer nofollow noopener"},"List of ISO 639-2 Codes"),", which is released under the"," ",m("a",{href:"https://creativecommons.org/licenses/by-sa/3.0/"},"Creative Commons Attribution-Share-Alike License 3.0"),".")))},n.localeSettings=function(){var t,e=this;if(this.loadingData)return m(_(),null);if(this.loadingDataError)return m(O(),{dismissible:!1,type:"error"},o().translator.trans("fof-discussion-language.admin.settings.errors.missing_assets"),m("br",null),m("b",null,"Error:")," ",m("code",null,null==(t=this.errorDetails)?void 0:t.message));var n=!!Number(this.setting("fof-discussion-language.native")()),r=F(n),a=V(n);return m("[",null,m("div",{className:"Form-group flex",style:{"margin-bottom":0,"font-weight":"bold"}},m("span",null,o().translator.trans("fof-discussion-language.admin.settings.config.language_label")),m("span",null,o().translator.trans("fof-discussion-language.admin.settings.config.country_label")),m("span",null),m("span",null)),m("div",{className:"Form-group flex"},b().component({onchange:this.newLocaleValue,value:this.newLocaleValue(),options:r}),b().component({onchange:this.newCountryValue,value:this.newCountryValue(),options:a}),v().component({className:"Button Button--primary",onclick:this.add.bind(this),disabled:!this.newLocaleValue()||!this.newCountryValue()||this.isAddingNewRecord},L()(this.isAddingNewRecord?"fas fa-spinner fa-spin":"fas fa-plus"))),m("div",{className:"Form-group"},o().store.all("discussion-languages").map((function(t){var n=t.id(),o=e.recordsUpdating[n],i=e.recordsDeleting[n],s=t.country();return m("div",{className:"flex"},b().component({onchange:function(r){e.recordsUpdating[n]=!0,t.save({code:r}).then((function(){e.recordsUpdating[n]=!1,m.redraw()}))},value:t.code(),options:r,disabled:o||i}),b().component({onchange:function(r){e.recordsUpdating[n]=!0,t.save({country:r}).then((function(){e.recordsUpdating[n]=!1,m.redraw()}))},value:s,options:a,disabled:o||i}),v().component({className:"Button Button--danger",disabled:i,onclick:e.remove.bind(e,t)},L()(i?"fas fa-spinner fa-spin":"fas fa-times")))}))))},n.onkeydown=function(t){"Enter"===t.key&&(this.add(),t.preventDefault())},n.add=function(){var t=this;if(!this.isAddingNewRecord&&this.newLocaleValue()){this.isAddingNewRecord=!0;var e=this.newLocaleValue(),n=this.newCountryValue();o().store.createRecord("discussion-languages").save({code:e,country:n}).then((function(){t.newLocaleValue(""),t.newCountryValue("")})).catch((function(){})).finally((function(){t.isAddingNewRecord=!1,m.redraw()}))}},n.remove=function(t){var e=this;this.recordsDeleting[t.id()]=!0,t.delete().then((function(){}),(function(){})).then((function(){delete e.recordsDeleting[t.id()],m.redraw()}))},e}(p()),G={LanguagesSettingsPage:R};function I(){return I=Object.assign||function(t){for(var e=1;e<arguments.length;e++){var n=arguments[e];for(var r in n)Object.prototype.hasOwnProperty.call(n,r)&&(t[r]=n[r])}return t},I.apply(this,arguments)}var U=n(879),B=n.n(U),z=I({getCountry:V,countries:t,locales:F,getName:function(t,e){var n;return C(null==(n=S)?void 0:n.find((function(e){return[e["639-1"]||e["639-2"]].includes(t)})),e)}},{flag:function(t){if(t){var e=t.emoji?t.emoji():t;return e?m("img",{alt:t.country&&t.country()||"",className:"emoji",draggable:"false",loading:"lazy",src:"//cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/72x72/"+B()(e)+".png"}):L()("fas fa-globe")}}});const Y=flarum.core.compat["common/Model"];var q=n.n(Y),W=function(t){function e(){return t.apply(this,arguments)||this}l(e,t);var n=e.prototype;return n.id=function(){return q().attribute("id").call(this)},n.code=function(){return q().attribute("code").call(this)},n.country=function(){return q().attribute("country").call(this)},n.name=function(){return q().attribute("name").call(this)},n.emoji=function(){return q().attribute("emoji").call(this)},n.apiEndpoint=function(){return"/fof/discussion-language"+(this.exists?"/"+this.data.id:"")},e}(q()),H={Language:W};const J=flarum.core.compat["common/extenders"];var K=n.n(J);const Q=flarum.core.compat["common/models/Discussion"];var X=n.n(Q);const Z=flarum.core.compat["common/models/Forum"];var $=n.n(Z);const tt=flarum.core.compat["tags/common/models/Tag"];var et=n.n(tt);const nt=[(new(K().Store)).add("discussion-languages",W),new(K().Model)(X()).hasOne("language").attribute("canChangeLanguage"),new(K().Model)($()).hasMany("discussionLanguages"),new(K().Model)(et()).attribute("subscriptionLanguage").attribute("localisedLastDiscussion")],rt=[].concat(nt);o().initializers.add("fof/discussion-language",(function(){o().extensionData.for("fof-discussion-language").registerPage(R).registerPermission({permission:"discussion.changeLanguageModerate",icon:"fas fa-globe",label:o().translator.trans("fof-discussion-language.admin.permissions.allow_change_language_label")},"moderate",65).registerPermission({icon:"fas fa-globe",label:o().translator.trans("fof-discussion-language.admin.permissions.allow_change_language_label"),setting:function(){var t=parseInt(o().data.settings["fof-discussion-language.allow_language_change"]);return i().component({defaultLabel:t?o().translator.trans("core.admin.permissions_controls.allow_some_minutes_button",{count:t}):o().translator.trans("core.admin.permissions_controls.allow_indefinitely_button"),key:"fof-discussion-language.allow_language_change",options:[{value:"-1",label:o().translator.trans("core.admin.permissions_controls.allow_indefinitely_button")},{value:"10",label:o().translator.trans("core.admin.permissions_controls.allow_ten_minutes_button")},{value:"reply",label:o().translator.trans("core.admin.permissions_controls.allow_until_reply_button")}]})}},"start",65)}))})(),module.exports=r})();
//# sourceMappingURL=admin.js.map