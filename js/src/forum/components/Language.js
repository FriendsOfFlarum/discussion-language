import Component from 'flarum/common/Component';

import flag from '../../common/utils/flag';

export default class Language extends Component {
  oninit(vnode) {
    super.oninit(vnode);
    this.languages = app.store.all('discussion-languages');
    this.options = this.languages.reduce((o, lang) => {
      o[lang.code()] = (
        <span>
          {flag(lang)} {lang.name()}
        </span>
      );

      return o;
    }, this.attrs.extra || {});
  }

  view() {
    const { language, uppercase } = this.attrs;
    const name = language.name() || '';

    return (
      <span>
        {flag(language)}
        &nbsp;
        {uppercase ? name.toUpperCase() : name}
      </span>
    );
  }
}
