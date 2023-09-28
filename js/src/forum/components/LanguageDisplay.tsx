import app from 'flarum/forum/app';
import Component from 'flarum/common/Component';
import flag from '../../common/utils/flag';
import Language from '../../common/models/Language';
import type Mithril from 'mithril';
import generateLanguageOptions from '../utils/generateLanguageOptions';

interface LanguageDisplayAttrs {
  language: Language;
  uppercase?: boolean;
  extra?: Record<string, Mithril.Children>;
}

export default class LanguageDisplay extends Component<LanguageDisplayAttrs> {
  options!: Record<string, Mithril.Children>;

  oninit(vnode: Mithril.Vnode<LanguageDisplayAttrs, this>): void {
    super.oninit(vnode);

    this.options = generateLanguageOptions(this.attrs.extra);
  }

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
