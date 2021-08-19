import Model from 'flarum/common/Model';
import Forum from 'flarum/common/models/Forum';
import Discussion from 'flarum/common/models/Discussion';

import Language from '../common/models/Language';

import addEditLanguageModal from './addEditLanguageModal';
import addLanguageComposer from './addLanguageComposer';
import addLanguageToDiscussionList from './addLanguageToDiscussionList';

export * from './components';
export * from '../common/utils';
export * from '../common/models';

app.initializers.add('fof/discussion-language', () => {
  app.store.models['discussion-languages'] = Language;

  Forum.prototype.discussionLanguages = Model.hasMany('discussionLanguages');
  Discussion.prototype.language = Model.hasOne('language');
  Discussion.prototype.canChangeLanguage = Model.attribute('canChangeLanguage');

  addEditLanguageModal();
  addLanguageComposer();
  addLanguageToDiscussionList();
});
