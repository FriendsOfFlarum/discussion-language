import { extend } from 'flarum/extend';
import IndexPage from 'flarum/components/IndexPage';
import DiscussionHero from 'flarum/components/DiscussionHero';
import DiscussionList from 'flarum/components/DiscussionList';
import DiscussionListItem from 'flarum/components/DiscussionListItem';

import Dropdown from 'flarum/components/Dropdown';
import Button from 'flarum/components/Button';

const addLanguage = function (items) {
    const language = this.props.discussion.language();

    if (!language) return;

    items.add(
        'discussion-language',
        <span>
            <i className="fas fa-globe" />
            <code>{language.code()}</code>
        </span>,
        5
    );
};

export default () => {
    extend(DiscussionListItem.prototype, 'infoItems', addLanguage);
    extend(DiscussionHero.prototype, 'items', addLanguage);

    extend(DiscussionList.prototype, 'requestParams', function (params) {
        params.include.push('language');

        if (this.props.params.language) {
            params.filter.q = (params.filter.q || '') + ' language:' + this.props.params.language;
        }
    });

    extend(IndexPage.prototype, 'stickyParams', (params) => (params.language = m.route.param('language')));

    extend(IndexPage.prototype, 'viewItems', function (items) {
        const languages = app.store.all('discussion-languages');
        const options = languages.reduce(
            (o, lang) => {
                o[lang.code()] = lang.code();

                return o;
            },
            { any: app.translator.trans('fof-discussion-language.forum.index_language.any') }
        );

        items.add(
            'language',
            Dropdown.component({
                buttonClassName: 'Button',
                label: options[this.params().language] || options.any,
                children: Object.keys(options).map((key) => {
                    const selected = this.params().language || 'any';
                    const active = key === selected;

                    return Button.component({
                        children: options[key],
                        icon: active ? 'fas fa-check' : true,
                        onclick: () => {
                            const params = this.params();

                            if (key === 'any') delete params.language;
                            else params.language = key;

                            m.route(app.route(this.props.routeName, params));
                        },
                        active: active,
                    });
                }),
            })
        );
    });
};
