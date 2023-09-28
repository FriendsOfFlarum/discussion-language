import app from 'flarum/forum/app';
import Button from 'flarum/common/components/Button';
import Dropdown, { IDropdownAttrs } from 'flarum/common/components/Dropdown';
import type Mithril from 'mithril';
import icon from 'flarum/common/helpers/icon';
import classList from 'flarum/common/utils/classList';
import generateLanguageOptions from '../utils/generateLanguageOptions';
import Stream from 'flarum/common/utils/Stream';

export interface LanguageDropdownAttrs extends IDropdownAttrs {
  className?: string;
  selected?: string;
  onclick?: (langId: string) => void;
  extra?: Record<string, Mithril.Children>;
}

export default class LanguageDropdown extends Dropdown<LanguageDropdownAttrs> {
  selected!: Stream<string>;
  options!: Record<string, Mithril.Children>;
  loaded: boolean = false;

  static initAttrs(attrs: LanguageDropdownAttrs): void {
    attrs.buttonClassName = 'Button';
    attrs.className = classList(attrs.className, 'Dropdown--select', 'Dropdown--language');
    super.initAttrs(attrs);
  }

  oninit(vnode: Mithril.Vnode<LanguageDropdownAttrs, this>): void {
    super.oninit(vnode);

    this.loadLanguages();
    this.selected = Stream(this.initSelectedLanguage());
  }

  getDefaultLanguage(): string {
    return (app.forum.attribute('fof-discussion-language.showAnyLangOpt') ? 'any' : app.translator.getLocale()) || '';
  }

  initSelectedLanguage(): string {
    return this.attrs.selected || this.getDefaultLanguage();
  }

  loadLanguages(): void {
    if (this.loaded === false) {
      this.options = generateLanguageOptions(this.attrs.extra);
      this.loaded = true;
    }
  }

  buildDropdownContent(): Mithril.ChildArray {
    return Object.keys(this.options).map((langId) => {
      const isItemSelected = this.selected() === langId;

      return (
        <Button
          key={langId + isItemSelected}
          active={isItemSelected}
          icon={isItemSelected ? 'fas fa-check' : true}
          onclick={() => {
            this.selected(langId);

            if (this.attrs.onclick) {
              this.attrs.onclick.call(this, langId);
            }
          }}
          aria-label={langId}
        >
          {this.options[langId]}
        </Button>
      );
    });
  }

  view(vnode: Mithril.Vnode<LanguageDropdownAttrs, this>) {
    const children = this.buildDropdownContent();

    return super.view({ ...vnode.attrs, children });
  }

  getButton(children: Mithril.ChildArray): Mithril.Vnode<any, any> {
    return (
      <button
        key={this.selected() + 'Button'}
        className={'Dropdown-toggle ' + this.attrs.buttonClassName}
        aria-haspopup="menu"
        aria-label={this.attrs.accessibleToggleLabel}
        data-toggle="dropdown"
        onclick={this.attrs.onclick}
      >
        {this.getButtonContent(children)}
      </button>
    );
  }

  getButtonContent(children: Mithril.ChildArray): Mithril.ChildArray {
    return [
      <span className="Button-label">{this.options[this.selected()]}</span>,
      this.attrs.caretIcon ? icon(this.attrs.caretIcon, { className: 'Button-caret' }) : null,
    ];
  }

  getMenu(items: Mithril.Vnode<any, any>[]): Mithril.Vnode<any, any> {
    return (
      <ul key={this.selected() + 'Menu'} className={'Dropdown-menu dropdown-menu ' + this.attrs.menuClassName}>
        {items}
      </ul>
    );
  }
}
