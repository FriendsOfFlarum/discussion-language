import { extend } from 'flarum/extend';
import DiscussionComposer from 'flarum/components/DiscussionComposer';
import LanguageDropdown from './components/LanguageDropdown';

const sort = (a, b) => a.code().toLowerCase() > b.code().toLowerCase();

export default () => {
    extend(DiscussionComposer.prototype, 'headerItems', function (items) {
        const languages = app.store.all('discussion-languages').sort(sort);

        items.add(
            'language',
            LanguageDropdown.component({
                selected: (this.language && this.language.code()) || languages[0].code(),
                onclick: (key) => (this.language = app.store.getBy('discussion-languages', 'code', key)),
            }),
            20
        );
    });

    extend(DiscussionComposer.prototype, 'data', function (data) {
        data.relationships = data.relationships || {};

        data.relationships.language = this.language || app.store.all('discussion-languages').sort(sort)[0];
    });
};
