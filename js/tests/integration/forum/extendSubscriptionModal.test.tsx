import app from 'flarum/forum/app';
import mq from 'mithril-query';
import ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';
import { addLanguageField, addLanguageToRequest, seedLanguage, type LanguageSubscriptionModal } from '../../../src/forum/extendSubscriptionModal';
import { bootForum } from '../../helpers/forum';

beforeAll(() => bootForum());

/** fof/follow-tags' modal for a tag the reader follows in the given language, with or without the hook. */
function subscriptionModal(subscriptionLanguage: string | null, canSaveLanguage = true): LanguageSubscriptionModal {
  return {
    attrs: { model: { subscriptionLanguage: () => subscriptionLanguage } },
    ...(canSaveLanguage && { requestData: () => ({ subscription: 'follow' }) }),
  } as unknown as LanguageSubscriptionModal;
}

function opened(modal: LanguageSubscriptionModal): LanguageSubscriptionModal {
  seedLanguage.call(modal);

  return modal;
}

describe('choosing the language a tag is followed in', () => {
  it('starts with the language chosen before', () => {
    expect(opened(subscriptionModal('de')).language!()).toBe('de');
  });

  it('starts with every language, when the forum offers it and none was chosen', () => {
    expect(opened(subscriptionModal(null)).language!()).toBeNull();
  });

  it("starts with the reader's own, when the forum doesn't offer every language", () => {
    app.forum.pushAttributes({ 'fof-discussion-language.showAnyLangOpt': false });

    expect(opened(subscriptionModal(null)).language!()).toBe('en');

    app.forum.pushAttributes({ 'fof-discussion-language.showAnyLangOpt': true });
  });

  it('is offered alongside the subscription', () => {
    const items = new ItemList<Mithril.Children>();

    addLanguageField.call(opened(subscriptionModal('de')), items);

    expect(items.has('subscription_language')).toBe(true);

    const out = mq(<div>{items.toArray()}</div>);

    expect(out).toContainRaw('Discussion language');
    expect(out).toContainRaw('German');
  });

  it('is saved with the subscription', () => {
    const modal = opened(subscriptionModal('de'));
    const data = modal.requestData();

    modal.language!('fr');
    addLanguageToRequest.call(modal, data);

    expect(data).toEqual({ subscription: 'follow', subscriptionLanguage: 'fr' });
  });

  it("isn't offered by a fof/follow-tags that can't save it", () => {
    const modal = opened(subscriptionModal('de', false));
    const items = new ItemList<Mithril.Children>();
    const data: Record<string, unknown> = { subscription: 'follow' };

    addLanguageField.call(modal, items);
    addLanguageToRequest.call(modal, data);

    expect(modal.language).toBeUndefined();
    expect(items.has('subscription_language')).toBe(false);
    expect(data).toEqual({ subscription: 'follow' });
  });
});
