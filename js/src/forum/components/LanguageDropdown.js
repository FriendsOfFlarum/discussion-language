import Component from 'flarum/common/Component';
import Dropdown from 'flarum/common/components/Dropdown';
import Button from 'flarum/common/components/Button';

import Language from './Language';

export default class LanguageDropdown extends Component {
    oninit(vnode) {
        super.oninit(vnode);
        this.languages = app.store.all('discussion-languages');
        this.options = this.languages.reduce((o, lang) => {
            o[lang.code()] = <Language language={lang} />;

            return o;
        }, this.attrs.extra || {});
    }

    view() {
        const selected = this.attrs.selected;

        return Dropdown.component(
            {
                buttonClassName: 'Button',
                label: this.options[selected] || this.options[this.attrs.default],
            },
            Object.keys(this.options).map((key) => {
                const isSelected = selected || app.translator.formatter.locale;
                const active = key === isSelected;

                return Button.component(
                    {
                        active,
                        icon: active ? 'fas fa-check' : true,
                        onclick: () => this.attrs.onclick(key),
                    },
                    this.options[key]
                );
            })
        );
    }
}
