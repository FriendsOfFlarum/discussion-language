import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import { components } from '@fof-follow-tags';
import LanguageDropdown from './components/LanguageDropdown';
import Stream from 'flarum/common/utils/Stream';

const SUBSCRIPTION_LANGUAGE_PRIORITY = 80;

export default function extendSubscriptionModal() {
  if (!('fof-follow-tags' in flarum.extensions)) return;

  extend(components.SubscriptionModal.prototype, 'oninit', function () {
    const tag = this.attrs.model;
    const subscriptionLanguage = tag.subscriptionLanguage();
    this.language = Stream(subscriptionLanguage);
  });

  extend(components.SubscriptionModal.prototype, 'formOptionItems', function (items) {
    items.add(
      'subscription_language',
      <div className="Form-group">
        <label>{app.translator.trans('fof-discussion-language.forum.sub_controls.subscription_language_label')}</label>
        <p className="helpText">{app.translator.trans('fof-discussion-language.forum.sub_controls.subscription_language_help')}</p>
        <LanguageDropdown
          selected={this.language()}
          onclick={(key) => {
            if (typeof key !== 'string') return;

            this.language(key);
          }}
        />
      </div>,
      SUBSCRIPTION_LANGUAGE_PRIORITY
    );
  });

  extend(components.SubscriptionModal.prototype, 'requestData', function (data) {
    data.language = this.language() || app.translator.getLocale();

    return data;
  });
}
