import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import DiscussionControls from 'flarum/forum/utils/DiscussionControls';
import Button from 'flarum/common/components/Button';

import LanguageDiscussionModal from './components/LanguageDiscussionModal';

export default () => {
  extend(DiscussionControls, 'moderationControls', function (items, discussion) {
    if (discussion.canChangeLanguage()) {
      items.add(
        'language',
        Button.component(
          {
            icon: 'fas fa-globe',
            onclick: () => app.modal.show(LanguageDiscussionModal, { discussion }),
          },
          app.translator.trans('fof-discussion-language.forum.discussion_controls.change_language_button')
        )
      );
    }
  });
};
