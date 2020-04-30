import Component from 'flarum/Component';

import flag from '../../common/utils/flag';

export default class Language extends Component {
    init() {
        this.languages = app.store.all('discussion-languages');
        this.options = this.languages.reduce((o, lang) => {
            o[lang.code()] = (
                <span>
                    {flag(lang)} {lang.name()}
                </span>
            );

            return o;
        }, this.props.extra || {});
    }

    view() {
        const { language, uppercase } = this.props;
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
