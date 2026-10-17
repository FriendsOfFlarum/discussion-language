import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import DiscussionControls from 'flarum/forum/utils/DiscussionControls';
import Button from 'flarum/common/components/Button';
import type Discussion from 'flarum/common/models/Discussion';
import type ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';

export default () => {
  extend(DiscussionControls, 'moderationControls', function (items: ItemList<Mithril.Children>, discussion: Discussion) {
    if (discussion.canChangeLanguage()) {
      items.add(
        'language',
        <Button icon="fas fa-globe" onclick={() => app.modal.show(() => import('./components/LanguageDiscussionModal'), { discussion })}>
          {app.translator.trans('fof-discussion-language.forum.discussion_controls.change_language_button')}
        </Button>
      );
    }
  });
};
