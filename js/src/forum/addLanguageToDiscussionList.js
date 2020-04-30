import { extend } from 'flarum/extend';
import IndexPage from 'flarum/components/IndexPage';
import DiscussionHero from 'flarum/components/DiscussionHero';
import DiscussionList from 'flarum/components/DiscussionList';
import DiscussionListItem from 'flarum/components/DiscussionListItem';

import flag from '../common/utils/flag';
import LanguageDropdown from './components/LanguageDropdown';

const addLanguage = function (items) {
    const language = this.props.discussion.language();

    if (!language) return;

    items.add(
        'discussion-language',
        <span>
            {flag(language) || <i className="fas fa-globe" />}
            <code>{language.name()}</code>
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
        items.add(
            'language',
            LanguageDropdown.component({
                extra: { any: app.translator.trans('fof-discussion-language.forum.index_language.any') },
                default: 'any',
                onclick: (key) => {
                    const params = this.params();

                    if (key === 'any') delete params.language;
                    else params.language = key;

                    m.route(app.route(this.props.routeName, params));
                },
                selected: this.params().language,
            })
        );
    });
};
