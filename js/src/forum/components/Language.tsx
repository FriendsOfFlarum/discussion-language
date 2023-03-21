import app from 'flarum/forum/app';
import Component from 'flarum/common/Component';
import LanguageModel from '../../common/models/Language';

import flag from '../../common/utils/flag';

interface IAttrs {
  language: LanguageModel;
  uppercase?: boolean;
}

export default class Language extends Component<IAttrs> {
  // oninit(vnode) {
  //   super.oninit(vnode);
  //   this.languages = app.store.all('discussion-languages');
  //   this.options = this.languages.reduce((o, lang) => {
  //     // o[lang.code()] = (
  //     //   <span>
  //     //     {flag(lang)}&nbsp;{lang.name()}
  //     //   </span>
  //     // );

  //     o[lang.code()] = (
  //       lang.name()
  //     );

  //     return o;
  //   }, this.attrs.extra || {});
  // }

  view() {
    const { language, uppercase } = this.attrs;
    const name = language.name() || 'x';

    return (
      <span>
        {flag(language)}
        &nbsp;
        {uppercase ? name.toUpperCase() : name}
      </span>
    );
  }
}
