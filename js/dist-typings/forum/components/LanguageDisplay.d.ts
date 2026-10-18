import Component from 'flarum/common/Component';
import type Language from '../../common/models/Language';
import type Mithril from 'mithril';
export interface LanguageDisplayAttrs {
    language: Language;
    uppercase?: boolean;
}
export default class LanguageDisplay extends Component<LanguageDisplayAttrs> {
    view(): Mithril.Child;
}
