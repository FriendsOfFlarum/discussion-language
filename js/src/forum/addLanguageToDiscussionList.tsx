import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import IndexPage from 'flarum/forum/components/IndexPage';
import DiscussionHero from 'flarum/forum/components/DiscussionHero';
import DiscussionListState from 'flarum/forum/states/DiscussionListState';
import DiscussionListItem from 'flarum/forum/components/DiscussionListItem';
import GlobalSearchState from 'flarum/forum/states/GlobalSearchState';
import setRouteWithForcedRefresh from 'flarum/common/utils/setRouteWithForcedRefresh';
import type ItemList from 'flarum/common/utils/ItemList';
import type { PaginatedListRequestParams } from 'flarum/common/states/PaginatedListState';
import type Mithril from 'mithril';

import flag from '../common/utils/flag';
import LanguageDropdown from './components/LanguageDropdown';
import type Language from '../common/models/Language';

const addLanguage = function (this: any, items: ItemList<Mithril.Children>) {
  // Ignore PDs from Byobu
  // Must be conditional call in case byobu not installed
  if (this.attrs.discussion.isPrivateDiscussion?.()) return;

  const language: Language | false | null = this.attrs.discussion.language?.();

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

  extend(DiscussionListState.prototype, 'requestParams', function (params: PaginatedListRequestParams) {
    const filter = (params.filter ?? {}) as Record<string, string>;
    const routeName = (app.current as any).data.routeName as string;

    if (routeName === 'byobuPrivate') return;
    if (routeName === 'byobuUserPrivate') return;

    (params.include as string[]).push('language');

    // Do not filter user discussions page as there is no dropdown
    if (routeName === 'user.discussions') return;

    // Required until https://github.com/flarum/framework/pull/3376 is released.
    if (routeName === 'following' && filter.q) {
      filter.q += ` is:${filter.subscription || 'following'}`;
      delete filter.subscription;
    }

    const paramLang = app.search.params().language;
    const locale = app.search.params().language ?? app.translator.getLocale();
    const showAnyOpt = app.forum.attribute('fof-discussion-language.showAnyLangOpt');

    if (filter.q) {
      if (showAnyOpt) {
        if (paramLang) {
          filter.q += ' language:' + paramLang;
        }
      } else {
        filter.q += ' language:' + locale;
      }
    } else {
      if (!showAnyOpt || paramLang) {
        filter.language = locale;
      }
    }

    params.filter = filter;
  });

  extend(GlobalSearchState.prototype, 'stickyParams', (params: Record<string, string>) => (params.language = m.route.param('language')));

  extend(IndexPage.prototype, 'viewItems', function (items: ItemList<Mithril.Children>) {
    // Don't add language controls to /private (fof/byobu)
    if ((app.current as any).data.routeName === 'byobuPrivate') return;

    const defaultSelected = app.forum.attribute('fof-discussion-language.showAnyLangOpt') ? 'any' : app.translator.getLocale();

    items.add(
      'language',
      <LanguageDropdown
        selected={app.search.params().language}
        onclick={(key: string) => {
          const params = app.search.params();

          if (key === defaultSelected) delete params.language;
          else params.language = key;

          setRouteWithForcedRefresh(app.route(app.current.get('routeName'), params));
        }}
      />
    );
  });
};
