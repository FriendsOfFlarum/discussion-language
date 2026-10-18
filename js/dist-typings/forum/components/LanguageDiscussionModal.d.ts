import FormModal, { type IFormModalAttrs } from 'flarum/common/components/FormModal';
import type Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';
import type Language from '../../common/models/Language';
export interface LanguageDiscussionModalAttrs extends IFormModalAttrs {
    /** The discussion whose language is changed, or none to choose one for the composer. */
    discussion?: Discussion;
    selected?: Language;
    /** Choosing a language submits it, rather than a button. */
    hideSubmitButton?: boolean;
    onsubmit?: (language: Language) => void;
}
export default class LanguageDiscussionModal extends FormModal<LanguageDiscussionModalAttrs> {
    languages: Language[];
    current: Language | undefined;
    selected: Language | undefined;
    oninit(vnode: Mithril.Vnode<LanguageDiscussionModalAttrs, this>): void;
    className(): string;
    title(): Mithril.Children;
    content(): Mithril.Children;
    select(language: Language): void;
    onsubmit(e?: SubmitEvent): void;
}
