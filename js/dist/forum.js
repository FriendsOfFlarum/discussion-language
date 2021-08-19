module.exports=function(t){var e={};function n(a){if(e[a])return e[a].exports;var o=e[a]={i:a,l:!1,exports:{}};return t[a].call(o.exports,o,o.exports,n),o.l=!0,o.exports}return n.m=t,n.c=e,n.d=function(t,e,a){n.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:a})},n.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},n.t=function(t,e){if(1&e&&(t=n(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var a=Object.create(null);if(n.r(a),Object.defineProperty(a,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var o in t)n.d(a,o,function(e){return t[e]}.bind(null,o));return a},n.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return n.d(e,"a",e),e},n.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},n.p="",n(n.s=35)}([,function(t,e){t.exports=flarum.core.compat["common/Model"]},function(t,e){t.exports=flarum.core.compat["common/extend"]},function(t,e,n){"use strict";function a(t,e){return(a=Object.setPrototypeOf||function(t,e){return t.__proto__=e,t})(t,e)}function o(t,e){t.prototype=Object.create(e.prototype),t.prototype.constructor=t,a(t,e)}n.d(e,"a",(function(){return o}))},function(t,e,n){"use strict";var a=n(13),o=n.n(a),s=n(8),r=n.n(s);e.a=function(t){if(t){var e=t.emoji?t.emoji():t;return e?m("img",{alt:t.country&&t.country()||"",className:"emoji",draggable:"false",loading:"lazy",src:"//cdn.jsdelivr.net/gh/twitter/twemoji@13/assets/72x72/"+o()(e)+".png"}):r()("fas fa-globe")}}},function(t,e){t.exports=flarum.core.compat["common/components/Button"]},function(t,e){t.exports=flarum.core.compat["common/app"]},function(t,e,n){"use strict";n.d(e,"a",(function(){return r}));var a=n(3),o=n(1),s=n.n(o),r=function(t){function e(){for(var e,n=arguments.length,a=new Array(n),o=0;o<n;o++)a[o]=arguments[o];return(e=t.call.apply(t,[this].concat(a))||this).code=s.a.attribute("code"),e.country=s.a.attribute("country"),e.name=s.a.attribute("name"),e.emoji=s.a.attribute("emoji"),e}return Object(a.a)(e,t),e.prototype.apiEndpoint=function(){return"/fof/discussion-language"+(this.exists?"/"+this.data.id:"")},e}(s.a)},function(t,e){t.exports=flarum.core.compat["common/helpers/icon"]},function(t,e){t.exports=flarum.core.compat["common/models/Forum"]},,function(t,e,n){"use strict";n.d(e,"a",(function(){return a}));var a={Language:n(7).a}},function(t,e,n){"use strict";n.d(e,"a",(function(){return a}));var a={flag:n(4).a}},function(t,e){
/*! Copyright Twitter Inc. and other contributors. Licensed under MIT */
t.exports=function(t){for(var e=[],n=0,a=0,o=0,s=t.length;o<s;)n=t.charCodeAt(o++),a?(e.push((65536+(a-55296<<10)+(n-56320)).toString(16)),a=0):55296<=n&&n<=56319?a=n:e.push(n.toString(16));return e.join("-")}},function(t,e){t.exports=flarum.core.compat["forum/components/DiscussionComposer"]},,function(t,e){t.exports=flarum.core.compat["common/Component"]},function(t,e){t.exports=flarum.core.compat["forum/components/IndexPage"]},function(t,e){t.exports=flarum.core.compat["common/models/Discussion"]},,function(t,e){t.exports=flarum.core.compat["forum/utils/DiscussionControls"]},function(t,e){t.exports=flarum.core.compat["common/components/Modal"]},function(t,e){t.exports=flarum.core.compat["forum/components/DiscussionPage"]},function(t,e){t.exports=flarum.core.compat["forum/components/DiscussionHero"]},function(t,e){t.exports=flarum.core.compat["forum/states/DiscussionListState"]},function(t,e){t.exports=flarum.core.compat["forum/components/DiscussionListItem"]},function(t,e){t.exports=flarum.core.compat["forum/states/GlobalSearchState"]},function(t,e){t.exports=flarum.core.compat["common/utils/setRouteWithForcedRefresh"]},function(t,e){t.exports=flarum.core.compat["common/components/Dropdown"]},,,,,,,function(t,e,n){"use strict";n.r(e),n.d(e,"components",(function(){return J})),n.d(e,"utils",(function(){return K.a})),n.d(e,"models",(function(){return Q.a}));var a=n(1),o=n.n(a),s=n(9),r=n.n(s),i=n(18),u=n.n(i),c=n(7),l=n(2),p=n(20),f=n.n(p),g=n(5),d=n.n(g),h=n(3),b=n(21),y=n.n(b),v=n(22),x=n.n(v),j=n(16),O=n.n(j),_=n(4),L=function(t){function e(){return t.apply(this,arguments)||this}Object(h.a)(e,t);var n=e.prototype;return n.oninit=function(e){t.prototype.oninit.call(this,e),this.languages=app.store.all("discussion-languages"),this.options=this.languages.reduce((function(t,e){return t[e.code()]=m("span",null,Object(_.a)(e)," ",e.name()),t}),this.attrs.extra||{})},n.view=function(){var t=this.attrs,e=t.language,n=t.uppercase,a=e.name()||"";return m("span",null,Object(_.a)(e)," ",n?a.toUpperCase():a)},e}(O.a),w=function(t){function e(){return t.apply(this,arguments)||this}Object(h.a)(e,t);var n=e.prototype;return n.oninit=function(e){t.prototype.oninit.call(this,e),this.languages=app.store.all("discussion-languages"),this.current=this.attrs.selected||this.attrs.discussion&&this.attrs.discussion.language(),this.selected=this.current},n.className=function(){return"FoFLanguageDiscussionModal"},n.title=function(){return this.attrs.discussion?app.translator.trans("fof-discussion-language.forum.change_language.edit_title",{title:m("em",null,this.attrs.discussion.title())}):app.translator.trans("fof-discussion-language.forum.change_language.title")},n.content=function(){var t=this;return[m("div",{className:"Modal-body"},m("div",{className:"Form-group"},this.languages.map((function(e){return m(d.a,{onclick:t.select.bind(t,e),className:"Button Button--block "+(t.selected===e?"active":"")},m(L,{language:e,uppercase:!0}))}))),!this.attrs.hideSubmitButton&&m("div",{className:"App-primaryControl"},d.a.component({type:"submit",className:"Button Button--primary",disabled:!this.selected||this.selected===this.current,loading:this.loading,icon:"fas fa-check"},app.translator.trans("fof-discussion-language.forum.change_language.submit_button"))))]},n.select=function(t){if(this.selected=t,this.attrs.hideSubmitButton)return this.onsubmit();m.redraw()},n.onsubmit=function(t){var e=this;t&&t.preventDefault();var n=this.attrs,a=n.discussion,o=n.onsubmit;if(this.loading=!0,!a)return this.hide(),void(o&&o(this.selected));var s=this.selected;a.save({relationships:{language:s}}).then((function(){return app.current instanceof x.a&&app.current.stream.update(),e.hide()})).catch(this.loaded.bind(this))},e}(y.a),C=n(6),D=n.n(C),B=n(17),N=n.n(B),P=n(14),S=n.n(P),k=function(t,e){return t.code().toLowerCase()>e.code().toLowerCase()},M=n(23),q=n.n(M),A=n(24),F=n.n(A),I=n(25),T=n.n(I),z=n(26),R=n.n(z),E=n(27),G=n.n(E),H=n(28),U=n.n(H),W=function(t){function e(){return t.apply(this,arguments)||this}Object(h.a)(e,t);var n=e.prototype;return n.oninit=function(e){t.prototype.oninit.call(this,e),this.languages=app.store.all("discussion-languages"),this.options=this.languages.reduce((function(t,e){return t[e.code()]=m(L,{language:e}),t}),this.attrs.extra||{})},n.view=function(){var t=this,e=this.attrs.selected;return U.a.component({buttonClassName:"Button",label:this.options[e]||this.options[this.attrs.default]},Object.keys(this.options).map((function(n){var a;a=app.forum.attribute("fof-discussion-language.composerLocaleDefault")?"any":app.translator.formatter.locale;var o=n===(e||a);return d.a.component({active:o,icon:!o||"fas fa-check",onclick:function(){return t.attrs.onclick(n)}},t.options[n])})))},e}(O.a),$=function(t){var e,n;if(null==(e=(n=this.attrs.discussion).isPrivateDiscussion)||!e.call(n)){var a=this.attrs.discussion.language();a&&t.add("discussion-language",m("span",null,Object(_.a)(a)||m("i",{className:"fas fa-globe"}),m("code",null,a.name())),5)}},J={Language:L,LanguageDiscussionModal:w,LanguageDropdown:W},K=n(12),Q=n(11);app.initializers.add("fof/discussion-language",(function(){app.store.models["discussion-languages"]=c.a,r.a.prototype.discussionLanguages=o.a.hasMany("discussionLanguages"),u.a.prototype.language=o.a.hasOne("language"),u.a.prototype.canChangeLanguage=o.a.attribute("canChangeLanguage"),Object(l.extend)(f.a,"moderationControls",(function(t,e){e.canChangeLanguage()&&t.add("language",d.a.component({icon:"fas fa-globe",onclick:function(){return app.modal.show(w,{discussion:e})}},app.translator.trans("fof-discussion-language.forum.discussion_controls.change_language_button")))})),Object(l.extend)(N.a.prototype,"newDiscussionAction",(function(t){var e=D.a.search.params().language;if(e)t.then((function(t){return t.fields.language=D.a.store.getBy("discussion-languages","code",e)}));else{var n=D.a.forum.attribute("fof-discussion-language.composerLocaleDefault");D.a.composer.fields.language=n?D.a.store.getBy("discussion-languages","code",D.a.translator.formatter.locale):""}})),S.a.prototype.chooseLanguage=function(t,e){var n=this;D.a.modal.show(w,{selected:this.composer.fields.language,hideSubmitButton:t,onsubmit:function(t){n.composer.fields.language=t,n.$("textarea").focus(),e&&e()}})},Object(l.extend)(S.a.prototype,"headerItems",(function(t){this._isByobuComposer||t.add("language",m("a",{className:"DiscussionComposer-changeTags",onclick:this.chooseLanguage.bind(this,!0,null)},m("span",{className:"LanguageLabel "+(this.composer.fields.language?"":"none")},this.composer.fields.language?L.component({language:this.composer.fields.language,uppercase:!0}):D.a.translator.trans("fof-discussion-language.forum.composer_discussion.choose_language_link"))),20)})),Object(l.override)(S.a.prototype,"onsubmit",(function(t){return this._isByobuComposer?t():this.composer.fields.language?void t():this.chooseLanguage(!0,t)})),Object(l.extend)(S.a.prototype,"data",(function(t){this._isByobuComposer||(t.relationships=t.relationships||{},t.relationships.language=this.composer.fields.language||D.a.store.all("discussion-languages").sort(k)[0])})),Object(l.extend)(T.a.prototype,"infoItems",$),Object(l.extend)(q.a.prototype,"items",$),Object(l.extend)(F.a.prototype,"requestParams",(function(t){var e;"byobuPrivate"!==app.current.data.routeName&&(t.include.push("language"),null!=(e=t.filter)&&e.tag&&(t.filter.q=(t.filter.q||"")+" tag:"+t.filter.tag),app.forum.attribute("fof-discussion-language.showAnyLangOpt")?app.search.params().language&&(t.filter.q=(t.filter.q||"")+" language:"+app.search.params().language):t.filter.q=(t.filter.q||"")+" language:"+(app.search.params().language?app.search.params().language:app.translator.formatter.locale))})),Object(l.extend)(R.a.prototype,"stickyParams",(function(t){return t.language=m.route.param("language")})),Object(l.extend)(N.a.prototype,"viewItems",(function(t){var e,n;"byobuPrivate"!==app.current.data.routeName&&(app.forum.attribute("fof-discussion-language.showAnyLangOpt")?(e={any:app.translator.trans("fof-discussion-language.forum.index_language.any")},n="any"):n=app.translator.formatter.locale,t.add("language",W.component({extra:e,default:n,onclick:function(t){var e=app.search.params();t===n?delete e.language:e.language=t,G()(app.route(app.current.get("routeName"),e))},selected:app.search.params().language})))}))}))}]);
//# sourceMappingURL=forum.js.map