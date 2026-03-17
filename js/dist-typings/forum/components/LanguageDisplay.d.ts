import Component from 'flarum/common/Component';
import Language from '../../common/models/Language';
import type Mithril from 'mithril';
interface LanguageDisplayAttrs {
    language: Language;
    uppercase?: boolean;
    extra?: Record<string, Mithril.Children>;
}
export default class LanguageDisplay extends Component<LanguageDisplayAttrs> {
    options: Record<string, Mithril.Children>;
    oninit(vnode: Mithril.Vnode<LanguageDisplayAttrs, this>): void;
    view(): Mithril.Child;
}
export {};
