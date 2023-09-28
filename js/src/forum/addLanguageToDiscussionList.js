import app from 'flarum/forum/app';
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
    if (routeName === 'byobuUserPrivate') return;

    params.include.push('language');

    // Do not filter user discussions page as there is no dropdown
    if (routeName === 'user.discussions') return;

    // Required until https://github.com/flarum/framework/pull/3376 is released.
    if (routeName === 'following' && params.filter.q) {
      params.filter.q += ` is:${params.filter.subscription || 'following'}`;
      delete params.filter.subscription;
    }

    const paramLang = app.search.params().language;
    const locale = app.search.params().language ?? app.translator.getLocale();
    const showAnyOpt = app.forum.attribute('fof-discussion-language.showAnyLangOpt');

    if (params.filter.q) {
      if (showAnyOpt) {
        if (paramLang) {
          params.filter.q += ' language:' + paramLang;
        }
      } else {
        params.filter.q += ' language:' + locale;
      }
    } else {
      if (!showAnyOpt || paramLang) {
        params.filter.language = locale;
      }
    }
  });

  extend(GlobalSearchState.prototype, 'stickyParams', (params) => (params.language = m.route.param('language')));

  extend(IndexPage.prototype, 'viewItems', function (items) {
    // Don't add language controls to /private (fof/byobu)
    if (app.current.data.routeName === 'byobuPrivate') return;

    const defaultSelected = app.forum.attribute('fof-discussion-language.showAnyLangOpt') ? 'any' : app.translator.getLocale();

    items.add(
      'language',
      <LanguageDropdown
        selected={app.search.params().language}
        onclick={(key) => {
          // if `key` is not a string, return early
          if (typeof key !== 'string') return;

          const params = app.search.params();

          if (key === defaultSelected) delete params.language;
          else params.language = key;

          setRouteWithForcedRefresh(app.route(app.current.get('routeName'), params));
        }}
      />
    );
  });
};
