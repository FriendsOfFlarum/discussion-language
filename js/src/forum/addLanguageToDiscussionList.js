import { extend } from 'flarum/common/extend';
import IndexPage from 'flarum/forum/components/IndexPage';
import DiscussionHero from 'flarum/forum/components/DiscussionHero';
import DiscussionListState from 'flarum/forum/states/DiscussionListState';
import DiscussionListItem from 'flarum/forum/components/DiscussionListItem';
import GlobalSearchState from 'flarum/forum/states/GlobalSearchState';
import setRouteWithForcedRefresh from 'flarum/common/utils/setRouteWithForcedRefresh';

import flag from '../common/utils/flag';
import LanguageDropdown from './components/LanguageDropdown';

const addLanguage = function (items) {
    const language = this.attrs.discussion.language();

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

    extend(DiscussionListState.prototype, 'requestParams', function (params) {
        params.include.push('language');

        if (params.filter?.tag) params.filter.q = (params.filter.q || '') + ' tag:' + params.filter.tag;

        if (app.forum.attribute('fof-discussion-language.showAnyLangOpt')) {
            if (app.search.params().language) {
                params.filter.q = (params.filter.q || '') + ' language:' + app.search.params().language;
            }
        } else {
            params.filter.q =
                (params.filter.q || '') +
                ' language:' +
                (app.search.params().language ? app.search.params().language : app.translator.formatter.locale);// +  ' tag:' + params.filter.tag;
        }
    });

    extend(GlobalSearchState.prototype, 'stickyParams', (params) => (params.language = m.route.param('language')));

    extend(IndexPage.prototype, 'viewItems', function (items) {
        let extra, defaultSelected;
        if (app.forum.attribute('fof-discussion-language.showAnyLangOpt')) {
            extra = { any: app.translator.trans('fof-discussion-language.forum.index_language.any') };
            defaultSelected = 'any';
        } else {
            defaultSelected = app.translator.formatter.locale;
        }

        items.add(
            'language',
            LanguageDropdown.component({
                extra,
                default: defaultSelected,
                onclick: (key) => {
                    const params = app.search.params();

                    if (key === defaultSelected) delete params.language;
                    else params.language = key;

                    setRouteWithForcedRefresh(app.route(app.current.get('routeName'), params));
                },
                selected: app.search.params().language,
            })
        );
    });
};
