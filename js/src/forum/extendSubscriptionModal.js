import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import { components } from '@fof-follow-tags';
import LanguageDropdown from './components/LanguageDropdown';
import Stream from 'flarum/common/utils/Stream';

const SUBSCRIPTION_LANGUAGE_PRIORITY = 80;

export default function extendSubscriptionModal() {
  if (!('fof-follow-tags' in flarum.extensions)) return;

  extend(components.SubscriptionModal.prototype, 'oninit', function () {
    this.language = Stream();

    const showAnyLangOption = app.forum.attribute('fof-discussion-language.showAnyLangOpt');

    this.additionalLanguages = showAnyLangOption ? { any: app.translator.trans('fof-discussion-language.forum.index_language.any') } : {};
    this.defaultLanguage = showAnyLangOption ? 'any' : app.translator.formatter.locale;
  });

  extend(components.SubscriptionModal.prototype, 'oncreate', function () {
    const tag = this.attrs.model;
    const subscriptionLanguage = tag.subscriptionLanguage();

    // For some reason, having this here in `oncreate` works, but we get unexpected bahviour if we put it in `oninit`.
    // It's only an issue when `subscriptionLanguage` is not empty/null.
    // TODO: investigate further at some point.
    this.language = Stream(subscriptionLanguage || this.defaultLanguage);
  });

  extend(components.SubscriptionModal.prototype, 'formOptionItems', function (items) {
    items.add(
      'subscription_language',
      <div className="Form-group">
        <label>{app.translator.trans('fof-discussion-language.forum.sub_controls.subscription_language_label')}</label>
        <p className="helpText">{app.translator.trans('fof-discussion-language.forum.sub_controls.subscription_language_help')}</p>
        <LanguageDropdown
          extra={this.additionalLanguages}
          default={this.defaultLanguage}
          selected={this.language()}
          onclick={(code) => {
            this.language(code);
            m.redraw();
          }}
        />
      </div>,
      SUBSCRIPTION_LANGUAGE_PRIORITY
    );
  });

  extend(components.SubscriptionModal.prototype, 'requestData', function (data) {
    data.language = this.language();

    return data;
  });
}
