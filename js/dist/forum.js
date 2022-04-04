/*! For license information please see forum.js.LICENSE.txt */
(()=>{var t={879:t=>{t.exports=function(t){for(var e=[],o=0,a=0,n=0,s=t.length;n<s;)o=t.charCodeAt(n++),a?(e.push((65536+(a-55296<<10)+(o-56320)).toString(16)),a=0):55296<=o&&o<=56319?a=o:e.push(o.toString(16));return e.join("-")}}},e={};function o(a){var n=e[a];if(void 0!==n)return n.exports;var s=e[a]={exports:{}};return t[a](s,s.exports,o),s.exports}o.n=t=>{var e=t&&t.__esModule?()=>t.default:()=>t;return o.d(e,{a:e}),e},o.d=(t,e)=>{for(var a in e)o.o(e,a)&&!o.o(t,a)&&Object.defineProperty(t,a,{enumerable:!0,get:e[a]})},o.o=(t,e)=>Object.prototype.hasOwnProperty.call(t,e),o.r=t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})};var a={};(()=>{"use strict";o.r(a),o.d(a,{components:()=>et,models:()=>at,utils:()=>ot});const t=flarum.core.compat["common/Model"];var e=o.n(t);const n=flarum.core.compat["common/models/Forum"];var s=o.n(n);const r=flarum.core.compat["tags/common/models/Tag"];var i=o.n(r);const u=flarum.core.compat["common/models/Discussion"];var c=o.n(u);function l(t,e){return l=Object.setPrototypeOf||function(t,e){return t.__proto__=e,t},l(t,e)}function p(t,e){t.prototype=Object.create(e.prototype),t.prototype.constructor=t,l(t,e)}var g=function(t){function o(){for(var o,a=arguments.length,n=new Array(a),s=0;s<a;s++)n[s]=arguments[s];return(o=t.call.apply(t,[this].concat(n))||this).code=e().attribute("code"),o.country=e().attribute("country"),o.name=e().attribute("name"),o.emoji=e().attribute("emoji"),o}return p(o,t),o.prototype.apiEndpoint=function(){return"/fof/discussion-language"+(this.exists?"/"+this.data.id:"")},o}(e());const f=flarum.core.compat["common/extend"],d=flarum.core.compat["forum/utils/DiscussionControls"];var h=o.n(d);const v=flarum.core.compat["common/components/Button"];var y=o.n(v);const b=flarum.core.compat["common/components/Modal"];var L=o.n(b);const x=flarum.core.compat["forum/components/DiscussionPage"];var D=o.n(x);const _=flarum.core.compat["common/Component"];var w=o.n(_),C=o(879),P=o.n(C);const S=flarum.core.compat["common/helpers/icon"];var j=o.n(S);const O=function(t){if(t){var e=t.emoji?t.emoji():t;return e?m("img",{alt:t.country&&t.country()||"",className:"emoji",draggable:"false",loading:"lazy",src:"//cdn.jsdelivr.net/gh/twitter/twemoji@13/assets/72x72/"+P()(e)+".png"}):j()("fas fa-globe")}};var B=function(t){function e(){return t.apply(this,arguments)||this}p(e,t);var o=e.prototype;return o.oninit=function(e){t.prototype.oninit.call(this,e),this.languages=app.store.all("discussion-languages"),this.options=this.languages.reduce((function(t,e){return t[e.code()]=m("span",null,O(e)," ",e.name()),t}),this.attrs.extra||{})},o.view=function(){var t=this.attrs,e=t.language,o=t.uppercase,a=e.name()||"";return m("span",null,O(e)," ",o?a.toUpperCase():a)},e}(w()),N=function(t){function e(){return t.apply(this,arguments)||this}p(e,t);var o=e.prototype;return o.oninit=function(e){t.prototype.oninit.call(this,e),this.languages=app.store.all("discussion-languages"),this.current=this.attrs.selected||this.attrs.discussion&&this.attrs.discussion.language(),this.selected=this.current},o.className=function(){return"FoFLanguageDiscussionModal"},o.title=function(){return this.attrs.discussion?app.translator.trans("fof-discussion-language.forum.change_language.edit_title",{title:m("em",null,this.attrs.discussion.title())}):app.translator.trans("fof-discussion-language.forum.change_language.title")},o.content=function(){var t=this;return[m("div",{className:"Modal-body"},m("div",{className:"Form-group"},this.languages.map((function(e){return m(y(),{onclick:t.select.bind(t,e),className:"Button Button--block "+(t.selected===e?"active":"")},m(B,{language:e,uppercase:!0}))}))),!this.attrs.hideSubmitButton&&m("div",{className:"App-primaryControl"},y().component({type:"submit",className:"Button Button--primary",disabled:!this.selected||this.selected===this.current,loading:this.loading,icon:"fas fa-check"},app.translator.trans("fof-discussion-language.forum.change_language.submit_button"))))]},o.select=function(t){if(this.selected=t,this.attrs.hideSubmitButton)return this.onsubmit();m.redraw()},o.onsubmit=function(t){var e=this;t&&t.preventDefault();var o=this.attrs,a=o.discussion,n=o.onsubmit;if(this.loading=!0,!a)return this.hide(),void(n&&n(this.selected));var s=this.selected;a.save({relationships:{language:s}}).then((function(){return app.current instanceof D()&&app.current.stream.update(),e.hide()})).catch(this.loaded.bind(this))},e}(L());const k=flarum.core.compat["common/app"];var M=o.n(k);const A=flarum.core.compat["forum/components/IndexPage"];var q=o.n(A);const F=flarum.core.compat["forum/components/DiscussionComposer"];var I=o.n(F),T=function(t,e){return t.code().toLowerCase()>e.code().toLowerCase()};const z=flarum.core.compat["forum/components/DiscussionHero"];var R=o.n(z);const E=flarum.core.compat["forum/states/DiscussionListState"];var G=o.n(E);const H=flarum.core.compat["forum/components/DiscussionListItem"];var U=o.n(H);const W=flarum.core.compat["forum/states/GlobalSearchState"];var $=o.n(W);const J=flarum.core.compat["common/utils/setRouteWithForcedRefresh"];var K=o.n(J);const Q=flarum.core.compat["forum/app"];var V=o.n(Q);const X=flarum.core.compat["common/components/SelectDropdown"];var Y=o.n(X),Z=function(t){function e(){return t.apply(this,arguments)||this}p(e,t);var o=e.prototype;return o.oninit=function(e){t.prototype.oninit.call(this,e),this.languages=V().store.all("discussion-languages"),this.options=this.languages.reduce((function(t,e){return t[e.code()]=m(B,{language:e}),t}),this.attrs.extra||{})},o.view=function(){var t=this,e=V().forum.attribute("fof-discussion-language.showAnyLangOpt")?"any":V().translator.formatter.locale;return m(Y(),{buttonClassName:"Button"},Object.keys(this.options).map((function(o){var a,n=t.attrs.selected?o===t.attrs.selected:o===e;return m(y(),{active:n,icon:!n||"fas fa-check",onclick:null==(a=t.attrs.onclick)||null==a.bind?void 0:a.bind(t,o)},t.options[o])})))},e}(w()),tt=function(t){var e,o;if(null==(e=(o=this.attrs.discussion).isPrivateDiscussion)||!e.call(o)){var a=this.attrs.discussion.language();a&&t.add("discussion-language",m("span",null,O(a)||m("i",{className:"fas fa-globe"}),m("code",null,a.name())),5)}},et={Language:B,LanguageDiscussionModal:N,LanguageDropdown:Z},ot={flag:O},at={Language:g};app.initializers.add("fof/discussion-language",(function(){app.store.models["discussion-languages"]=g,"flarum-tags"in flarum.extensions&&(i().prototype.localisedLastDiscussion=e().attribute("localisedLastDiscussion")),s().prototype.discussionLanguages=e().hasMany("discussionLanguages"),c().prototype.language=e().hasOne("language"),c().prototype.canChangeLanguage=e().attribute("canChangeLanguage"),(0,f.extend)(h(),"moderationControls",(function(t,e){e.canChangeLanguage()&&t.add("language",y().component({icon:"fas fa-globe",onclick:function(){return app.modal.show(N,{discussion:e})}},app.translator.trans("fof-discussion-language.forum.discussion_controls.change_language_button")))})),(0,f.extend)(q().prototype,"newDiscussionAction",(function(t){var e=M().search.params().language;if(e)t.then((function(t){return t.fields.language=M().store.getBy("discussion-languages","code",e)}));else{var o=M().forum.attribute("fof-discussion-language.composerLocaleDefault");M().composer.fields.language=o?M().store.getBy("discussion-languages","code",M().translator.formatter.locale):""}})),I().prototype.chooseLanguage=function(t,e){var o=this;M().modal.show(N,{selected:this.composer.fields.language,hideSubmitButton:t,onsubmit:function(t){o.composer.fields.language=t,o.$("textarea").focus(),e&&e()}})},(0,f.extend)(I().prototype,"headerItems",(function(t){this._isByobuComposer||t.add("language",m("a",{className:"DiscussionComposer-changeTags",onclick:this.chooseLanguage.bind(this,!0,null)},m("span",{className:"LanguageLabel "+(this.composer.fields.language?"":"none")},this.composer.fields.language?B.component({language:this.composer.fields.language,uppercase:!0}):M().translator.trans("fof-discussion-language.forum.composer_discussion.choose_language_link"))),20)})),(0,f.override)(I().prototype,"onsubmit",(function(t){return this._isByobuComposer?t():this.composer.fields.language?void t():this.chooseLanguage(!0,t)})),(0,f.extend)(I().prototype,"data",(function(t){this._isByobuComposer||(t.relationships=t.relationships||{},t.relationships.language=this.composer.fields.language||M().store.all("discussion-languages").sort(T)[0])})),(0,f.extend)(U().prototype,"infoItems",tt),(0,f.extend)(R().prototype,"items",tt),(0,f.extend)(G().prototype,"requestParams",(function(t){var e,o=app.current.data.routeName;if("byobuPrivate"!==o&&(t.include.push("language"),"user.discussions"!==o)){"following"===o&&t.filter.q&&(t.filter.q+=" is:"+(t.filter.subscription||"following"),delete t.filter.subscription);var a=app.search.params().language,n=null!=(e=app.search.params().language)?e:app.translator.formatter.locale;t.filter.q?app.forum.attribute("fof-discussion-language.showAnyLangOpt")?a&&(t.filter.q+=" language:"+a):t.filter.q+=" language:"+n:t.filter.language=n}})),(0,f.extend)($().prototype,"stickyParams",(function(t){return t.language=m.route.param("language")})),(0,f.extend)(q().prototype,"viewItems",(function(t){var e,o;"byobuPrivate"!==app.current.data.routeName&&(app.forum.attribute("fof-discussion-language.showAnyLangOpt")?(e={any:app.translator.trans("fof-discussion-language.forum.index_language.any")},o="any"):o=app.translator.formatter.locale,t.add("language",Z.component({extra:e,default:o,onclick:function(t){var e=app.search.params();t===o?delete e.language:e.language=t,K()(app.route(app.current.get("routeName"),e))},selected:app.search.params().language})))})),"flarum-tags"in flarum.extensions&&(i().prototype.lastPostedDiscussion=function(){var t,o,a,n=e().hasOne("lastPostedDiscussion").bind(this);if(!V().forum.attribute("fof-discussion-language.useLocaleForTagsPageLastDiscussion"))return n();var s=this.localisedLastDiscussion(),r=(null==(t=V().session.user)||null==t.preferences||null==(o=t.preferences())?void 0:o.locale)||V().translator.formatter.locale||V().forum.discussionLanguages()[0].code(),i=null==(a=V().forum.discussionLanguages().find((function(t){return t.code()===r})))?void 0:a.id();if(!i)return n();var u=s[i];return u?new(c())({attributes:{title:u.title,slug:u.id,lastPostedAt:new Date(1e3*u.at).toISOString()}}):n()})}))})(),module.exports=a})();
//# sourceMappingURL=forum.js.map