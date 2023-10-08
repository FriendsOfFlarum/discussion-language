/*! For license information please see forum.js.LICENSE.txt */
(()=>{var t={879:t=>{t.exports=function(t){for(var e=[],n=0,o=0,a=0,s=t.length;a<s;)n=t.charCodeAt(a++),o?(e.push((65536+(o-55296<<10)+(n-56320)).toString(16)),o=0):55296<=n&&n<=56319?o=n:e.push(n.toString(16));return e.join("-")}}},e={};function n(o){var a=e[o];if(void 0!==a)return a.exports;var s=e[o]={exports:{}};return t[o](s,s.exports,n),s.exports}n.n=t=>{var e=t&&t.__esModule?()=>t.default:()=>t;return n.d(e,{a:e}),e},n.d=(t,e)=>{for(var o in e)n.o(e,o)&&!n.o(t,o)&&Object.defineProperty(t,o,{enumerable:!0,get:e[o]})},n.o=(t,e)=>Object.prototype.hasOwnProperty.call(t,e),n.r=t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})};var o={};(()=>{"use strict";n.r(o),n.d(o,{components:()=>ot,extend:()=>mt,models:()=>rt,utils:()=>at});const t=flarum.core.compat["forum/app"];var e=n.n(t);const a=flarum.core.compat["common/extend"],s=flarum.core.compat["forum/utils/DiscussionControls"];var r=n.n(s);const i=flarum.core.compat["common/components/Button"];var u=n.n(i);function c(t,e){return c=Object.setPrototypeOf||function(t,e){return t.__proto__=e,t},c(t,e)}function l(t,e){t.prototype=Object.create(e.prototype),t.prototype.constructor=t,c(t,e)}const g=flarum.core.compat["common/components/Modal"];var f=n.n(g);const d=flarum.core.compat["forum/components/DiscussionPage"];var p=n.n(d);const h=flarum.core.compat["common/Component"];var v=n.n(h),b=n(879),y=n.n(b);const L=flarum.core.compat["common/helpers/icon"];var w=n.n(L);const D=function(t){if(t){var e=t.emoji?t.emoji():t;return e?m("img",{alt:t.country&&t.country()||"",className:"emoji",draggable:"false",loading:"lazy",src:"//cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/72x72/"+y()(e)+".png"}):w()("fas fa-globe")}};function x(){return x=Object.assign||function(t){for(var e=1;e<arguments.length;e++){var n=arguments[e];for(var o in n)Object.prototype.hasOwnProperty.call(n,o)&&(t[o]=n[o])}return t},x.apply(this,arguments)}function _(t){var n={};return e().store.all("discussion-languages").forEach((function(t){n[t.code()]=m(N,{language:t})})),x({},n,t)}var N=function(t){function e(){for(var e,n=arguments.length,o=new Array(n),a=0;a<n;a++)o[a]=arguments[a];return(e=t.call.apply(t,[this].concat(o))||this).options=void 0,e}l(e,t);var n=e.prototype;return n.oninit=function(e){t.prototype.oninit.call(this,e),this.options=_(this.attrs.extra)},n.view=function(){var t=this.attrs,e=t.language,n=t.uppercase,o=e.name()||"";return m("span",null,D(e)," ",n?o.toUpperCase():o)},e}(v()),C=function(t){function n(){return t.apply(this,arguments)||this}l(n,t);var o=n.prototype;return o.oninit=function(n){t.prototype.oninit.call(this,n),this.languages=e().store.all("discussion-languages").filter((function(t){return"any"!==t.code()})),this.current=this.attrs.selected||this.attrs.discussion&&this.attrs.discussion.language(),this.selected=this.current},o.className=function(){return"FoFLanguageDiscussionModal"},o.title=function(){return this.attrs.discussion?e().translator.trans("fof-discussion-language.forum.change_language.edit_title",{title:m("em",null,this.attrs.discussion.title())}):e().translator.trans("fof-discussion-language.forum.change_language.title")},o.content=function(){var t=this;return[m("div",{className:"Modal-body"},m("div",{className:"Form-group"},this.languages.map((function(e){return m(u(),{onclick:t.select.bind(t,e),className:"Button Button--block "+(t.selected===e?"active":"")},m(N,{language:e,uppercase:!0}))}))),!this.attrs.hideSubmitButton&&m("div",{className:"App-primaryControl"},u().component({type:"submit",className:"Button Button--primary",disabled:!this.selected||this.selected===this.current,loading:this.loading,icon:"fas fa-check"},e().translator.trans("fof-discussion-language.forum.change_language.submit_button"))))]},o.select=function(t){if(this.selected=t,this.attrs.hideSubmitButton)return this.onsubmit();m.redraw()},o.onsubmit=function(t){var n=this;t&&t.preventDefault();var o=this.attrs,a=o.discussion,s=o.onsubmit;if(this.loading=!0,!a)return this.hide(),void(s&&s(this.selected));var r=this.selected;a.save({relationships:{language:r}}).then((function(){return e().current instanceof p()&&e().current.stream.update(),n.hide()})).catch(this.loaded.bind(this))},n}(f());const S=flarum.core.compat["forum/components/IndexPage"];var B=n.n(S);const k=flarum.core.compat["forum/components/DiscussionComposer"];var O=n.n(k),P=function(t,e){return t.code().toLowerCase()>e.code().toLowerCase()};const j=flarum.core.compat["forum/components/DiscussionHero"];var M=n.n(j);const A=flarum.core.compat["forum/states/DiscussionListState"];var I=n.n(A);const q=flarum.core.compat["forum/components/DiscussionListItem"];var F=n.n(q);const T=flarum.core.compat["forum/states/GlobalSearchState"];var z=n.n(T);const E=flarum.core.compat["common/utils/setRouteWithForcedRefresh"];var R=n.n(E);const U=flarum.core.compat["common/components/Dropdown"];var G=n.n(U);const H=flarum.core.compat["common/utils/classList"];var W=n.n(H);const $=flarum.core.compat["common/utils/Stream"];var J=n.n($),K=function(t){function n(){for(var e,n=arguments.length,o=new Array(n),a=0;a<n;a++)o[a]=arguments[a];return(e=t.call.apply(t,[this].concat(o))||this).selected=void 0,e.options=void 0,e.loaded=!1,e}l(n,t),n.initAttrs=function(e){e.buttonClassName="Button",e.className=W()(e.className,"Dropdown--select","Dropdown--language"),t.initAttrs.call(this,e)};var o=n.prototype;return o.oninit=function(e){t.prototype.oninit.call(this,e),this.loadLanguages(),this.selected=J()(this.initSelectedLanguage())},o.getDefaultLanguage=function(){return(e().forum.attribute("fof-discussion-language.showAnyLangOpt")?"any":e().translator.getLocale())||""},o.initSelectedLanguage=function(){return this.attrs.selected||this.getDefaultLanguage()},o.loadLanguages=function(){!1===this.loaded&&(this.options=_(this.attrs.extra),this.loaded=!0)},o.buildDropdownContent=function(){var t=this;return Object.keys(this.options).map((function(e){var n=t.selected()===e;return m(u(),{key:e+n,active:n,icon:!n||"fas fa-check",onclick:function(){t.selected(e),t.attrs.onclick&&t.attrs.onclick.call(t,e)},"aria-label":e},t.options[e])}))},o.view=function(e){var n=this.buildDropdownContent();return t.prototype.view.call(this,x({},e.attrs,{children:n}))},o.getButton=function(t){return m("button",{key:this.selected()+"Button",className:"Dropdown-toggle "+this.attrs.buttonClassName,"aria-haspopup":"menu","aria-label":this.attrs.accessibleToggleLabel,"data-toggle":"dropdown",onclick:this.attrs.onclick},this.getButtonContent(t))},o.getButtonContent=function(t){return[m("span",{className:"Button-label"},this.options[this.selected()]),this.attrs.caretIcon?w()(this.attrs.caretIcon,{className:"Button-caret"}):null]},o.getMenu=function(t){return m("ul",{key:this.selected()+"Menu",className:"Dropdown-menu dropdown-menu "+this.attrs.menuClassName},t)},n}(G()),Q=function(t){var e,n;if(null==(e=(n=this.attrs.discussion).isPrivateDiscussion)||!e.call(n)){var o=this.attrs.discussion.language();o&&t.add("discussion-language",m("span",null,D(o)||m("i",{className:"fas fa-globe"}),m("code",null,o.name())),5)}};const V=flarum.core.compat["tags/common/models/Tag"];var X=n.n(V);const Y=flarum.core.compat["common/models/Discussion"];var Z=n.n(Y);const tt=flarum.core.compat["common/Model"];var et=n.n(tt);const nt=flarum.extensions["fof-follow-tags"];var ot={LanguageDisplay:N,LanguageDiscussionModal:C,LanguageDropdown:K},at={flag:D},st=function(t){function e(){return t.apply(this,arguments)||this}l(e,t);var n=e.prototype;return n.id=function(){return et().attribute("id").call(this)},n.code=function(){return et().attribute("code").call(this)},n.country=function(){return et().attribute("country").call(this)},n.name=function(){return et().attribute("name").call(this)},n.emoji=function(){return et().attribute("emoji").call(this)},n.apiEndpoint=function(){return"/fof/discussion-language"+(this.exists?"/"+this.data.id:"")},e}(et()),rt={Language:st};const it=flarum.core.compat["common/extenders"];var ut=n.n(it);const ct=flarum.core.compat["common/models/Forum"];var lt=n.n(ct);const gt=[(new(ut().Store)).add("discussion-languages",st),new(ut().Model)(Z()).hasOne("language").attribute("canChangeLanguage"),new(ut().Model)(lt()).hasMany("discussionLanguages"),new(ut().Model)(X()).attribute("subscriptionLanguage").attribute("localisedLastDiscussion")],mt=[].concat(gt);e().initializers.add("fof/discussion-language",(function(){(0,a.extend)(r(),"moderationControls",(function(t,n){n.canChangeLanguage()&&t.add("language",u().component({icon:"fas fa-globe",onclick:function(){return e().modal.show(C,{discussion:n})}},e().translator.trans("fof-discussion-language.forum.discussion_controls.change_language_button")))})),(0,a.extend)(B().prototype,"newDiscussionAction",(function(t){var n=e().search.params().language;if(n)t.then((function(t){return t.fields.language=e().store.getBy("discussion-languages","code",n)}));else{var o=e().forum.attribute("fof-discussion-language.composerLocaleDefault");e().composer.fields.language=o?e().store.getBy("discussion-languages","code",e().translator.getLocale()):""}})),O().prototype.chooseLanguage=function(t,n){var o=this;e().modal.show(C,{selected:this.composer.fields.language,hideSubmitButton:t,onsubmit:function(t){o.composer.fields.language=t,o.$("textarea").focus(),n&&n()}})},(0,a.extend)(O().prototype,"headerItems",(function(t){this._isByobuComposer||t.add("language",m("a",{className:"DiscussionComposer-changeTags",onclick:this.chooseLanguage.bind(this,!0,null)},m("span",{className:"LanguageLabel "+(this.composer.fields.language?"":"none")},this.composer.fields.language?N.component({language:this.composer.fields.language,uppercase:!0}):e().translator.trans("fof-discussion-language.forum.composer_discussion.choose_language_link"))),20)})),(0,a.override)(O().prototype,"onsubmit",(function(t){return this._isByobuComposer?t():this.composer.fields.language?void t():this.chooseLanguage(!0,t)})),(0,a.extend)(O().prototype,"data",(function(t){this._isByobuComposer||(t.relationships=t.relationships||{},t.relationships.language=this.composer.fields.language||e().store.all("discussion-languages").sort(P)[0])})),(0,a.extend)(F().prototype,"infoItems",Q),(0,a.extend)(M().prototype,"items",Q),(0,a.extend)(I().prototype,"requestParams",(function(t){var n,o=e().current.data.routeName;if("byobuPrivate"!==o&&"byobuUserPrivate"!==o&&(t.include.push("language"),"user.discussions"!==o)){"following"===o&&t.filter.q&&(t.filter.q+=" is:"+(t.filter.subscription||"following"),delete t.filter.subscription);var a=e().search.params().language,s=null!=(n=e().search.params().language)?n:e().translator.getLocale(),r=e().forum.attribute("fof-discussion-language.showAnyLangOpt");t.filter.q?r?a&&(t.filter.q+=" language:"+a):t.filter.q+=" language:"+s:r&&!a||(t.filter.language=s)}})),(0,a.extend)(z().prototype,"stickyParams",(function(t){return t.language=m.route.param("language")})),(0,a.extend)(B().prototype,"viewItems",(function(t){if("byobuPrivate"!==e().current.data.routeName){var n=e().forum.attribute("fof-discussion-language.showAnyLangOpt")?"any":e().translator.getLocale();t.add("language",m(K,{selected:e().search.params().language,onclick:function(t){if("string"==typeof t){var o=e().search.params();t===n?delete o.language:o.language=t,R()(e().route(e().current.get("routeName"),o))}}}))}})),"flarum-tags"in flarum.extensions&&(X().prototype.lastPostedDiscussion=function(){var t,n,o,a=et().hasOne("lastPostedDiscussion").bind(this);if(!e().forum.attribute("fof-discussion-language.useLocaleForTagsPageLastDiscussion"))return a();var s=this.localisedLastDiscussion(),r=(null==(t=e().session.user)||null==t.preferences||null==(n=t.preferences())?void 0:n.locale)||e().translator.formatter.locale||e().forum.discussionLanguages()[0].code(),i=null==(o=e().forum.discussionLanguages().find((function(t){return t.code()===r})))?void 0:o.id();if(!i)return a();var u=s[i];return u?new(Z())({attributes:{title:u.title,slug:u.id,lastPostedAt:new Date(1e3*u.at).toISOString()}}):a()}),"fof-follow-tags"in flarum.extensions&&((0,a.extend)(nt.components.SubscriptionModal.prototype,"oninit",(function(){var t=this.attrs.model.subscriptionLanguage();this.language=J()(t)})),(0,a.extend)(nt.components.SubscriptionModal.prototype,"formOptionItems",(function(t){var n=this;t.add("subscription_language",m("div",{className:"Form-group"},m("label",null,e().translator.trans("fof-discussion-language.forum.sub_controls.subscription_language_label")),m("p",{className:"helpText"},e().translator.trans("fof-discussion-language.forum.sub_controls.subscription_language_help")),m(K,{selected:this.language(),onclick:function(t){"string"==typeof t&&n.language(t)}})),80)})),(0,a.extend)(nt.components.SubscriptionModal.prototype,"requestData",(function(t){return t.language=this.language(),t})))}))})(),module.exports=o})();
//# sourceMappingURL=forum.js.map