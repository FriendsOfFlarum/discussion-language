import app from 'flarum/forum/app';
import Button from 'flarum/common/components/Button';
import SelectDropdown, { type ISelectDropdownAttrs } from 'flarum/common/components/SelectDropdown';
import classList from 'flarum/common/utils/classList';
import extractText from 'flarum/common/utils/extractText';
import type Mithril from 'mithril';

import type Language from '../../common/models/Language';
import generateLanguageOptions from '../utils/generateLanguageOptions';
import { defaultLanguage } from '../utils/languageFilter';

export interface LanguageDropdownAttrs extends Partial<ISelectDropdownAttrs> {
  className?: string;
  /** The code of the language chosen, or `any`. */
  selected?: string;
  onselect?: (code: string) => void;
}

export default class LanguageDropdown extends SelectDropdown<LanguageDropdownAttrs & ISelectDropdownAttrs> {
  selected!: string;

  static initAttrs(attrs: LanguageDropdownAttrs & ISelectDropdownAttrs) {
    attrs.className = classList(attrs.className, 'Dropdown--language');
    attrs.buttonClassName ||= 'Button';
    // As the sort dropdown beside it on the discussion list.
    attrs.caretIcon ??= 'fas fa-caret-down';
    attrs.accessibleToggleLabel ||= extractText(app.translator.trans('fof-discussion-language.forum.language_dropdown.toggle_label'));

    super.initAttrs(attrs);
  }

  oninit(vnode: Mithril.Vnode<LanguageDropdownAttrs & ISelectDropdownAttrs, this>) {
    super.oninit(vnode);

    this.selected = this.attrs.selected || defaultLanguage();
  }

  view(vnode: Mithril.Vnode<LanguageDropdownAttrs & ISelectDropdownAttrs, this>) {
    const options = generateLanguageOptions();

    return super.view({
      ...vnode,
      children: Object.keys(options).map((code) => {
        const active = this.selected === code;

        return (
          <Button
            active={active}
            icon={active ? 'fas fa-check' : true}
            // Its content is a component, which the button can't read a name from.
            aria-label={app.store.getBy<Language>('discussion-languages', 'code', code)?.name()}
            data-language={code}
            onclick={() => this.select(code)}
          >
            {options[code]}
          </Button>
        );
      }),
    });
  }

  select(code: string) {
    this.selected = code;

    this.attrs.onselect?.(code);
  }
}
