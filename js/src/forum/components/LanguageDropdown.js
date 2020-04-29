import Component from 'flarum/Component';
import Dropdown from 'flarum/components/Dropdown';
import Button from 'flarum/components/Button';

import flag from '../../common/utils/flag';
import { getName as getLanguageName } from '../../common/utils/locales';
import isNative from '../helpers/isNative';

export default class LanguageDropdown extends Component {
    init() {
        const native = isNative();

        this.languages = app.store.all('discussion-languages');
        this.options = this.languages.reduce((o, lang) => {
            o[lang.code()] = (
                <span>
                    {flag(lang.country())} {getLanguageName(lang.code(), native)}
                </span>
            );

            return o;
        }, this.props.extra || {});
    }

    view() {
        const selected = this.props.selected;

        return Dropdown.component({
            buttonClassName: 'Button',
            label: this.options[selected] || this.options[this.props.default],
            children: Object.keys(this.options).map((key) => {
                const isSelected = selected || 'any';
                const active = key === isSelected;

                return Button.component({
                    active,
                    children: this.options[key],
                    icon: active ? 'fas fa-check' : true,
                    onclick: () => this.props.onclick(key),
                });
            }),
        });
    }
}
