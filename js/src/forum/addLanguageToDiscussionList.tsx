import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import IndexPage from 'flarum/forum/components/IndexPage';
import DiscussionHero from 'flarum/forum/components/DiscussionHero';
import DiscussionListState from 'flarum/forum/states/DiscussionListState';
import DiscussionListItem from 'flarum/forum/components/DiscussionListItem';
import Icon from 'flarum/common/components/Icon';
import setRouteWithForcedRefresh from 'flarum/common/utils/setRouteWithForcedRefresh';
import type Discussion from 'flarum/common/models/Discussion';
import type ItemList from 'flarum/common/utils/ItemList';
import type { PaginatedListRequestParams } from 'flarum/common/states/PaginatedListState';
import type Mithril from 'mithril';

import flag from '../common/utils/flag';
import LanguageDropdown from './components/LanguageDropdown';
import { defaultLanguage, languageToFilterBy, selectedLanguage } from './utils/languageFilter';
import { ANY } from '../common/utils/any';

function addLanguage(discussion: Discussion, items: ItemList<Mithril.Children>) {
  // fof/byobu's private discussions have no language.
  if ((discussion as Discussion & { isPrivateDiscussion?: () => boolean }).isPrivateDiscussion?.()) return;

  const language = discussion.language?.();

  if (!language) return;

  items.add(
    'discussion-language',
    <span>
      {flag(language) || <Icon name="fas fa-globe" />}
      <code>{language.name()}</code>
    </span>,
    5
  );
}

export default () => {
  extend(DiscussionListItem.prototype, 'infoItems', function (items) {
    addLanguage(this.attrs.discussion, items);
  });
  extend(DiscussionHero.prototype, 'items', function (items) {
    addLanguage(this.attrs.discussion, items);
  });

  extend(DiscussionListState.prototype, 'requestParams', function (this: DiscussionListState, params: PaginatedListRequestParams) {
    const routeName = app.current.get('routeName');

    // fof/byobu's private discussions have no language.
    if (routeName === 'byobuPrivate' || routeName === 'byobuUserPrivate') return;

    // A request naming its includes gets none of the API's defaults.
    params.include = [...[params.include ?? []].flat(), 'language'];

    // Only the list the language dropdown is for. Others, such as a user's
    // discussions or a flarum/deck column, keep the filters they were given.
    if (this !== app.discussions) return;

    const filter = params.filter as Record<string, string>;
    const language = languageToFilterBy(
      selectedLanguage(),
      !!app.forum.attribute<boolean>('fof-discussion-language.showAnyLangOpt'),
      app.translator.getLocale()
    );

    if (language) {
      filter.language = language;
    } else {
      delete filter.language;
    }
  });

  extend(IndexPage.prototype, 'viewItems', function (items: ItemList<Mithril.Children>) {
    if (app.current.get('routeName') === 'byobuPrivate') return;

    const defaultSelected = defaultLanguage();

    items.add(
      'language',
      <LanguageDropdown
        selected={selectedLanguage() ?? defaultSelected}
        onselect={(key: string) => {
          const params = app.search.state.params() as Record<string, unknown>;
          const filter = { ...(params.filter as Record<string, string> | undefined) };

          // "Any language" stays in the URL too: the forum may be set to send a
          // list without a language to the reader's.
          if (key === defaultSelected && key !== ANY) {
            delete filter.language;
          } else {
            filter.language = key;
          }

          setRouteWithForcedRefresh(app.route(app.current.get('routeName'), { ...params, filter }));
        }}
      />
    );
  });
};
