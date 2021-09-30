import app from 'flarum/forum/app';

import Component from 'flarum/common/Component';
import Button from 'flarum/common/components/Button';

import Language from './Language';
import SelectDropdown from 'flarum/common/components/SelectDropdown';

export default class LanguageDropdown extends Component {
  oninit(vnode) {
    super.oninit(vnode);
    this.languages = app.store.all('discussion-languages');
    /**
     * @type {Record<string, import('mithril').Component>}
     */
    this.options = this.languages.reduce((o, lang) => {
      o[lang.code()] = <Language language={lang} />;

      return o;
    }, this.attrs.extra || {});
  }

  view() {
    const defaultValue = app.forum.attribute('fof-discussion-language.showAnyLangOpt') ? 'any' : app.translator.formatter.locale;

    return (
      <SelectDropdown buttonClassName="Button">
        {Object.keys(this.options).map((langId) => {
          const isItemSelected = this.attrs.selected ? langId === this.attrs.selected : langId === defaultValue;

          return (
            <Button active={isItemSelected} icon={isItemSelected ? 'fas fa-check' : true} onclick={this.attrs.onclick?.bind?.(this, langId)}>
              {this.options[langId]}
            </Button>
          );
        })}
      </SelectDropdown>
    );
  }
}
