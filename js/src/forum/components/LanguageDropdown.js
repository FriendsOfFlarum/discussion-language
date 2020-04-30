import Component from 'flarum/Component';
import Dropdown from 'flarum/components/Dropdown';
import Button from 'flarum/components/Button';

import flag from '../../common/utils/flag';
import Language from './Language';

export default class LanguageDropdown extends Component {
    init() {
        this.languages = app.store.all('discussion-languages');
        this.options = this.languages.reduce((o, lang) => {
            o[lang.code()] = <Language language={lang} />;

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
