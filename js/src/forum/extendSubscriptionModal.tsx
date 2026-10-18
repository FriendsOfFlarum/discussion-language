import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import Stream from 'flarum/common/utils/Stream';
import type ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';
import type SubscriptionModal from 'ext:fof/follow-tags/forum/components/SubscriptionModal';

import LanguageDropdown from './components/LanguageDropdown';

/** fof/follow-tags' subscription modal, as it is once it can save the language. */
export type LanguageSubscriptionModal = SubscriptionModal & { language?: Stream<string | null> };

const SUBSCRIPTION_LANGUAGE_PRIORITY = 80;

/**
 * The language the subscriber is notified of new discussions in: the one they
 * chose, or their own when the forum doesn't offer every language at once.
 */
export function seedLanguage(this: LanguageSubscriptionModal): void {
  // fof/follow-tags lets the language be saved with a subscription from 2.0.0.
  if (typeof this.requestData !== 'function') return;

  let language = this.attrs.model.subscriptionLanguage();

  if (language === null && !app.forum.attribute<boolean>('fof-discussion-language.showAnyLangOpt')) {
    language = app.translator.getLocale();
  }

  this.language = Stream(language);
}

export function addLanguageField(this: LanguageSubscriptionModal, items: ItemList<Mithril.Children>): void {
  const language = this.language;

  if (!language) return;

  items.add(
    'subscription_language',
    <div className="Form-group">
      <label>{app.translator.trans('fof-discussion-language.forum.sub_controls.subscription_language_label')}</label>
      <p className="helpText">{app.translator.trans('fof-discussion-language.forum.sub_controls.subscription_language_help')}</p>
      <LanguageDropdown selected={language() ?? undefined} onselect={(code: string) => language(code)} />
    </div>,
    SUBSCRIPTION_LANGUAGE_PRIORITY
  );
}

export function addLanguageToRequest(this: LanguageSubscriptionModal, data: Record<string, unknown>): void {
  if (this.language) {
    data.subscriptionLanguage = this.language();
  }
}

export default function extendSubscriptionModal(): void {
  if (!('fof-follow-tags' in flarum.extensions)) return;

  // By its path: fof/follow-tags loads the modal when it is first opened.
  const modal = 'ext:fof/follow-tags/forum/components/SubscriptionModal';

  extend(modal, 'oninit', seedLanguage);
  extend(modal, 'formOptionItems', addLanguageField);
  extend(modal, 'requestData', addLanguageToRequest);
}
