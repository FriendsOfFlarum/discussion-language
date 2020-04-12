import { extend } from 'flarum/extend';
import DiscussionComposer from 'flarum/components/DiscussionComposer';
import Select from 'flarum/components/Select';
// import iso from 'iso-639-1';

export default () => {
    extend(DiscussionComposer.prototype, 'headerItems', function (items) {
        const languages = app.store.all('discussion-languages');

        items.add(
            'language',
            Select.component({
                value: this.language && this.language.code(),
                options: languages.reduce((o, lang) => {
                    const code = lang.code();

                    o[code] = code;

                    return o;
                }, {}),
                onchange: (value) => (this.language = app.store.getBy('discussion-languages', 'code', value)),
            }),
            20
        );
    });

    extend(DiscussionComposer.prototype, 'data', function (data) {
        data.relationships = data.relationships || {};

        if (this.language) data.relationships.language = this.language;
    });
};
