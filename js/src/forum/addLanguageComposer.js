import { extend } from 'flarum/extend';
import DiscussionComposer from 'flarum/components/DiscussionComposer';
import Select from 'flarum/components/Select';
// import iso from 'iso-639-1';

const sort = (a, b) => a.code().toLowerCase() > b.code().toLowerCase();

export default () => {
    extend(DiscussionComposer.prototype, 'headerItems', function (items) {
        const languages = app.store.all('discussion-languages').sort(sort);

        items.add(
            'language',
            Select.component({
                value: (this.language && this.language.code()) || languages[0].code(),
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

        data.relationships.language = this.language || app.store.all('discussion-languages').sort(sort)[0];
    });
};
