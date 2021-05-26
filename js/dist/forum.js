module.exports=function(t){var n={};function e(o){if(n[o])return n[o].exports;var a=n[o]={i:o,l:!1,exports:{}};return t[o].call(a.exports,a,a.exports,e),a.l=!0,a.exports}return e.m=t,e.c=n,e.d=function(t,n,o){e.o(t,n)||Object.defineProperty(t,n,{enumerable:!0,get:o})},e.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},e.t=function(t,n){if(1&n&&(t=e(t)),8&n)return t;if(4&n&&"object"==typeof t&&t&&t.__esModule)return t;var o=Object.create(null);if(e.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:t}),2&n&&"string"!=typeof t)for(var a in t)e.d(o,a,function(n){return t[n]}.bind(null,a));return o},e.n=function(t){var n=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(n,"a",n),n},e.o=function(t,n){return Object.prototype.hasOwnProperty.call(t,n)},e.p="",e(e.s=34)}([function(t,n){t.exports=flarum.core.compat["common/Model"]},function(t,n){t.exports=flarum.core.compat["common/extend"]},function(t,n,e){"use strict";function o(t,n){t.prototype=Object.create(n.prototype),t.prototype.constructor=t,t.__proto__=n}e.d(n,"a",(function(){return o}))},function(t,n,e){"use strict";var o=e(11),a=e.n(o),s=e(7),r=e.n(s);n.a=function(t){if(t){var n=t.emoji?t.emoji():t;return n?m("img",{alt:t.country&&t.country()||"",className:"emoji",draggable:"false",loading:"lazy",src:"//cdn.jsdelivr.net/gh/twitter/twemoji@13/assets/72x72/"+a()(n)+".png"}):r()("fas fa-globe")}}},function(t,n){t.exports=flarum.core.compat["common/components/Button"]},function(t,n){t.exports=flarum.core.compat["common/app"]},function(t,n,e){"use strict";function o(t){if(void 0===t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return t}e.d(n,"a",(function(){return u}));var a=e(2);function s(t,n,e){return n in t?Object.defineProperty(t,n,{value:e,enumerable:!0,configurable:!0,writable:!0}):t[n]=e,t}var r=e(0),i=e.n(r),u=function(t){function n(){for(var n,e=arguments.length,a=new Array(e),r=0;r<e;r++)a[r]=arguments[r];return s(o(n=t.call.apply(t,[this].concat(a))||this),"code",i.a.attribute("code")),s(o(n),"country",i.a.attribute("country")),s(o(n),"name",i.a.attribute("name")),s(o(n),"emoji",i.a.attribute("emoji")),n}return Object(a.a)(n,t),n.prototype.apiEndpoint=function(){return"/fof/discussion-language"+(this.exists?"/"+this.data.id:"")},n}(i.a)},function(t,n){t.exports=flarum.core.compat["common/helpers/icon"]},function(t,n){t.exports=flarum.core.compat["common/models/Forum"]},function(t,n,e){"use strict";e.d(n,"a",(function(){return o}));var o={Language:e(6).a}},function(t,n,e){"use strict";e.d(n,"a",(function(){return o}));var o={flag:e(3).a}},function(t,n){
/*! Copyright Twitter Inc. and other contributors. Licensed under MIT */
t.exports=function(t){for(var n=[],e=0,o=0,a=0,s=t.length;a<s;)e=t.charCodeAt(a++),o?(n.push((65536+(o-55296<<10)+(e-56320)).toString(16)),o=0):55296<=e&&e<=56319?o=e:n.push(e.toString(16));return n.join("-")}},function(t,n){t.exports=flarum.core.compat["forum/components/DiscussionComposer"]},,,function(t,n){t.exports=flarum.core.compat["common/Component"]},function(t,n){t.exports=flarum.core.compat["forum/components/IndexPage"]},,function(t,n){t.exports=flarum.core.compat["common/models/Discussion"]},,function(t,n){t.exports=flarum.core.compat["forum/utils/DiscussionControls"]},function(t,n){t.exports=flarum.core.compat["common/components/Modal"]},function(t,n){t.exports=flarum.core.compat["forum/components/DiscussionPage"]},function(t,n){t.exports=flarum.core.compat["forum/components/DiscussionHero"]},function(t,n){t.exports=flarum.core.compat["forum/states/DiscussionListState"]},function(t,n){t.exports=flarum.core.compat["forum/components/DiscussionListItem"]},function(t,n){t.exports=flarum.core.compat["forum/states/GlobalSearchState"]},function(t,n){t.exports=flarum.core.compat["common/utils/setRouteWithForcedRefresh"]},function(t,n){t.exports=flarum.core.compat["common/components/Dropdown"]},,,,,,function(t,n,e){"use strict";e.r(n),e.d(n,"components",(function(){return J})),e.d(n,"utils",(function(){return K.a})),e.d(n,"models",(function(){return Q.a}));var o=e(0),a=e.n(o),s=e(8),r=e.n(s),i=e(18),u=e.n(i),c=e(6),l=e(1),p=e(20),f=e.n(p),g=e(4),d=e.n(g),h=e(2),b=e(21),y=e.n(b),v=e(22),x=e.n(v),j=e(15),O=e.n(j),_=e(3),L=function(t){function n(){return t.apply(this,arguments)||this}Object(h.a)(n,t);var e=n.prototype;return e.oninit=function(n){t.prototype.oninit.call(this,n),this.languages=app.store.all("discussion-languages"),this.options=this.languages.reduce((function(t,n){return t[n.code()]=m("span",null,Object(_.a)(n)," ",n.name()),t}),this.attrs.extra||{})},e.view=function(){var t=this.attrs,n=t.language,e=t.uppercase,o=n.name()||"";return m("span",null,Object(_.a)(n)," ",e?o.toUpperCase():o)},n}(O.a),w=function(t){function n(){return t.apply(this,arguments)||this}Object(h.a)(n,t);var e=n.prototype;return e.oninit=function(n){t.prototype.oninit.call(this,n),this.languages=app.store.all("discussion-languages"),this.current=this.attrs.selected||this.attrs.discussion&&this.attrs.discussion.language(),this.selected=this.current},e.className=function(){return"FoFLanguageDiscussionModal"},e.title=function(){return this.attrs.discussion?app.translator.trans("fof-discussion-language.forum.change_language.edit_title",{title:m("em",null,this.attrs.discussion.title())}):app.translator.trans("fof-discussion-language.forum.change_language.title")},e.content=function(){var t=this;return[m("div",{className:"Modal-body"},m("div",{className:"Form-group"},this.languages.map((function(n){return m(d.a,{onclick:t.select.bind(t,n),className:"Button Button--block "+(t.selected===n?"active":"")},m(L,{language:n,uppercase:!0}))}))),!this.attrs.hideSubmitButton&&m("div",{className:"App-primaryControl"},d.a.component({type:"submit",className:"Button Button--primary",disabled:!this.selected||this.selected===this.current,loading:this.loading,icon:"fas fa-check"},app.translator.trans("fof-discussion-language.forum.change_language.submit_button"))))]},e.select=function(t){if(this.selected=t,this.attrs.hideSubmitButton)return this.onsubmit();m.redraw()},e.onsubmit=function(t){var n=this;t&&t.preventDefault();var e=this.attrs,o=e.discussion,a=e.onsubmit;if(this.loading=!0,!o)return this.hide(),void(a&&a(this.selected));var s=this.selected;o.save({relationships:{language:s}}).then((function(){return app.current instanceof x.a&&app.current.stream.update(),n.hide()})).catch(this.loaded.bind(this))},n}(y.a),D=e(5),C=e.n(D),S=e(16),k=e.n(S),N=e(12),B=e.n(N),M=function(t,n){return t.code().toLowerCase()>n.code().toLowerCase()},P=e(23),F=e.n(P),I=e(24),A=e.n(I),q=e(25),R=e.n(q),T=e(26),z=e.n(T),E=e(27),G=e.n(E),H=e(28),U=e.n(H),W=function(t){function n(){return t.apply(this,arguments)||this}Object(h.a)(n,t);var e=n.prototype;return e.oninit=function(n){t.prototype.oninit.call(this,n),this.languages=app.store.all("discussion-languages"),this.options=this.languages.reduce((function(t,n){return t[n.code()]=m(L,{language:n}),t}),this.attrs.extra||{})},e.view=function(){var t=this,n=this.attrs.selected;return U.a.component({buttonClassName:"Button",label:this.options[n]||this.options[this.attrs.default]},Object.keys(this.options).map((function(e){var o=e===(n||"any");return d.a.component({active:o,icon:!o||"fas fa-check",onclick:function(){return t.attrs.onclick(e)}},t.options[e])})))},n}(O.a),$=function(t){var n=this.attrs.discussion.language();n&&t.add("discussion-language",m("span",null,Object(_.a)(n)||m("i",{className:"fas fa-globe"}),m("code",null,n.name())),5)},J={Language:L,LanguageDiscussionModal:w,LanguageDropdown:W},K=e(10),Q=e(9);app.initializers.add("fof/discussion-language",(function(){app.store.models["discussion-languages"]=c.a,r.a.prototype.discussionLanguages=a.a.hasMany("discussionLanguages"),u.a.prototype.language=a.a.hasOne("language"),u.a.prototype.canChangeLanguage=a.a.attribute("canChangeLanguage"),Object(l.extend)(f.a,"moderationControls",(function(t,n){n.canChangeLanguage()&&t.add("language",d.a.component({icon:"fas fa-globe",onclick:function(){return app.modal.show(w,{discussion:n})}},app.translator.trans("fof-discussion-language.forum.discussion_controls.change_language_button")))})),Object(l.extend)(k.a.prototype,"newDiscussionAction",(function(t){var n=C.a.search.params().language;if(n)t.then((function(t){return t.fields.language=C.a.store.getBy("discussion-languages","code",n)}));else{var e=C.a.forum.attribute("fof-discussion-language.composerLocaleDefault");C.a.composer.fields.language=e?C.a.store.getBy("discussion-languages","code",C.a.translator.formatter.locale):""}})),B.a.prototype.chooseLanguage=function(t,n){var e=this;C.a.modal.show(w,{selected:this.composer.fields.language,hideSubmitButton:t,onsubmit:function(t){e.composer.fields.language=t,e.$("textarea").focus(),n&&n()}})},Object(l.extend)(B.a.prototype,"headerItems",(function(t){t.add("language",m("a",{className:"DiscussionComposer-changeTags",onclick:this.chooseLanguage.bind(this,!0,null)},m("span",{className:"LanguageLabel "+(this.composer.fields.language?"":"none")},this.composer.fields.language?L.component({language:this.composer.fields.language,uppercase:!0}):C.a.translator.trans("fof-discussion-language.forum.composer_discussion.choose_language_link"))),20)})),Object(l.override)(B.a.prototype,"onsubmit",(function(t){if(!this.composer.fields.language)return this.chooseLanguage(!0,t);t()})),Object(l.extend)(B.a.prototype,"data",(function(t){t.relationships=t.relationships||{},t.relationships.language=this.composer.fields.language||C.a.store.all("discussion-languages").sort(M)[0]})),Object(l.extend)(R.a.prototype,"infoItems",$),Object(l.extend)(F.a.prototype,"items",$),Object(l.extend)(A.a.prototype,"requestParams",(function(t){t.include.push("language"),app.search.params().language&&(t.filter.q=(t.filter.q||"")+" language:"+app.search.params().language)})),Object(l.extend)(z.a.prototype,"stickyParams",(function(t){return t.language=m.route.param("language")})),Object(l.extend)(k.a.prototype,"viewItems",(function(t){t.add("language",W.component({extra:{any:app.translator.trans("fof-discussion-language.forum.index_language.any")},default:"any",onclick:function(t){var n=app.search.params();"any"===t?delete n.language:n.language=t,G()(app.route(app.current.get("routeName"),n))},selected:app.search.params().language}))}))}))}]);
//# sourceMappingURL=forum.js.map