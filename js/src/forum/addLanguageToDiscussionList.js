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
  // Ignore PDs from Byobu
  // Must be conditional call in case byobu not installed
  if (this.attrs.discussion.isPrivateDiscussion?.()) return;

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
    const routeName = app.current.data.routeName;

    if (routeName === 'byobuPrivate') return;

    params.include.push('language');

    // Do not filter user discussions page as there is no dropdown
    if (routeName === 'user.discussions') return;

    // Required until https://github.com/flarum/framework/pull/3376 is released.
    if (routeName === 'following' && params.filter.q) {
      params.filter.q += ` is:${params.filter.subscription || 'following'}`;
      delete params.filter.subscription;
    }

    const paramLang = app.search.params().language;
    const locale = app.search.params().language ?? app.translator.formatter.locale;

    if (params.filter.q) {
      if (app.forum.attribute('fof-discussion-language.showAnyLangOpt')) {
        if (app.serach.params().language) {
          params.filter.q += ' language:' + paramLang;
        }
      } else {
        params.filter.q += ' language:' + locale;
      }
    } else {
      params.filter.language = locale;
    }
  });

  extend(GlobalSearchState.prototype, 'stickyParams', (params) => (params.language = m.route.param('language')));

  extend(IndexPage.prototype, 'viewItems', function (items) {
    // Don't add language controls to /private (fof/byobu)
    if (app.current.data.routeName === 'byobuPrivate') return;

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
