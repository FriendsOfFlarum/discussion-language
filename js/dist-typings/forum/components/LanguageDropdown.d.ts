import Dropdown, { IDropdownAttrs } from 'flarum/common/components/Dropdown';
import type Mithril from 'mithril';
import Stream from 'flarum/common/utils/Stream';
export interface LanguageDropdownAttrs extends IDropdownAttrs {
    className?: string;
    selected?: string;
    onclick?: (langId: string) => void;
    extra?: Record<string, Mithril.Children>;
}
export default class LanguageDropdown extends Dropdown<LanguageDropdownAttrs> {
    selected: Stream<string>;
    options: Record<string, Mithril.Children>;
    loaded: boolean;
    static initAttrs(attrs: LanguageDropdownAttrs): void;
    oninit(vnode: Mithril.Vnode<LanguageDropdownAttrs, this>): void;
    getDefaultLanguage(): string;
    initSelectedLanguage(): string;
    loadLanguages(): void;
    buildDropdownContent(): Mithril.ChildArray;
    view(vnode: Mithril.Vnode<LanguageDropdownAttrs, this>): JSX.Element;
    getButton(children: Mithril.ChildArray): Mithril.Vnode<any, any>;
    getButtonContent(children: Mithril.ChildArray): Mithril.ChildArray;
    getMenu(items: Mithril.Vnode<any, any>[]): Mithril.Vnode<any, any>;
}
