import SelectDropdown, { type ISelectDropdownAttrs } from 'flarum/common/components/SelectDropdown';
import type Mithril from 'mithril';
export interface LanguageDropdownAttrs extends Partial<ISelectDropdownAttrs> {
    className?: string;
    /** The code of the language chosen, or `any`. */
    selected?: string;
    onselect?: (code: string) => void;
}
export default class LanguageDropdown extends SelectDropdown<LanguageDropdownAttrs & ISelectDropdownAttrs> {
    selected: string;
    static initAttrs(attrs: LanguageDropdownAttrs & ISelectDropdownAttrs): void;
    oninit(vnode: Mithril.Vnode<LanguageDropdownAttrs & ISelectDropdownAttrs, this>): void;
    view(vnode: Mithril.Vnode<LanguageDropdownAttrs & ISelectDropdownAttrs, this>): JSX.Element;
    select(code: string): void;
}
