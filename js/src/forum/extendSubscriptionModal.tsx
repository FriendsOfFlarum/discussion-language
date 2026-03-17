import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import { components } from '@fof-follow-tags';
import LanguageDropdown from './components/LanguageDropdown';
import Stream from 'flarum/common/utils/Stream';
import type Tag from 'flarum/tags/common/models/Tag';
import type ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';

const SUBSCRIPTION_LANGUAGE_PRIORITY = 80;

export default function extendSubscriptionModal() {
  if (!('fof-follow-tags' in flarum.extensions)) return;

  extend(components.SubscriptionModal.prototype, 'oninit', function (this: typeof components.SubscriptionModal.prototype) {
    const tag = this.attrs.model as Tag;
    let subscriptionLanguage = tag.subscriptionLanguage();

    if (!app.forum.attribute('fof-discussion-language.showAnyLangOpt') && subscriptionLanguage === null) {
      subscriptionLanguage = app.translator.getLocale();
    }

    (this as any).language = Stream(subscriptionLanguage);
  });

  extend(
    components.SubscriptionModal.prototype,
    'formOptionItems',
    function (this: typeof components.SubscriptionModal.prototype, items: ItemList<Mithril.Children>) {
      items.add(
        'subscription_language',
        <div className="Form-group">
          <label>{app.translator.trans('fof-discussion-language.forum.sub_controls.subscription_language_label')}</label>
          <p className="helpText">{app.translator.trans('fof-discussion-language.forum.sub_controls.subscription_language_help')}</p>
          <LanguageDropdown
            selected={(this as any).language()}
            onclick={(key: string) => {
              (this as any).language(key);
            }}
          />
        </div>,
        SUBSCRIPTION_LANGUAGE_PRIORITY
      );
    }
  );

  extend(
    components.SubscriptionModal.prototype,
    'requestData',
    function (this: typeof components.SubscriptionModal.prototype, data: Record<string, unknown>) {
      data.language = (this as any).language();

      return data;
    }
  );
}
