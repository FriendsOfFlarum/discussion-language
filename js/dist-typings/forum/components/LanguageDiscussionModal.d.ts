import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';
import type Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';
import Language from '../../common/models/Language';
export interface LanguageDiscussionModalAttrs extends IInternalModalAttrs {
    discussion?: Discussion;
    selected?: Language;
    hideSubmitButton?: boolean;
    onsubmit?: (language: Language) => void;
}
export default class LanguageDiscussionModal extends Modal<LanguageDiscussionModalAttrs> {
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
