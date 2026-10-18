import app from 'flarum/forum/app';
import addEditLanguageModal from './addEditLanguageModal';
import addLanguageComposer from './addLanguageComposer';
import addLanguageToDiscussionList from './addLanguageToDiscussionList';
import useLocalisedLastDiscussionOnTagsPage from './useLocalisedLastDiscussionOnTagsPage';
import extendSubscriptionModal from './extendSubscriptionModal';

export { default as extend } from './extend';

app.initializers.add('fof/discussion-language', () => {
  addEditLanguageModal();
  addLanguageComposer();
  addLanguageToDiscussionList();
  useLocalisedLastDiscussionOnTagsPage();
  extendSubscriptionModal();
});
