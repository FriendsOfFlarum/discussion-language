import Component from 'flarum/common/Component';
import flag from '../../common/utils/flag';
import type Language from '../../common/models/Language';
import type Mithril from 'mithril';

export interface LanguageDisplayAttrs {
  language: Language;
  uppercase?: boolean;
}

export default class LanguageDisplay extends Component<LanguageDisplayAttrs> {
  view(): Mithril.Child {
    const { language, uppercase } = this.attrs;
    const name = language.name() || '';

    return (
      <span>
        {flag(language)} {uppercase ? name.toUpperCase() : name}
      </span>
    );
  }
}
