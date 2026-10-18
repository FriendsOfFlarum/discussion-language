import Stream from 'flarum/common/utils/Stream';
import type ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';
import type SubscriptionModal from 'ext:fof/follow-tags/forum/components/SubscriptionModal';
/** fof/follow-tags' subscription modal, as it is once it can save the language. */
export type LanguageSubscriptionModal = SubscriptionModal & {
    language?: Stream<string | null>;
};
/**
 * The language the subscriber is notified of new discussions in: the one they
 * chose, or their own when the forum doesn't offer every language at once.
 */
export declare function seedLanguage(this: LanguageSubscriptionModal): void;
export declare function addLanguageField(this: LanguageSubscriptionModal, items: ItemList<Mithril.Children>): void;
export declare function addLanguageToRequest(this: LanguageSubscriptionModal, data: Record<string, unknown>): void;
export default function extendSubscriptionModal(): void;
