import Model from 'flarum/Model';
import Forum from 'flarum/models/Forum';
import Discussion from 'flarum/models/Discussion';

import Language from '../common/models/Language';
import addLanguageComposer from './addLanguageComposer';
import addLanguageToDiscussionList from './addLanguageToDiscussionList';

app.initializers.add('fof/discussion-language', () => {
    app.store.models['discussion-languages'] = Language;

    Forum.prototype.discussionLanguages = Model.hasMany('discussionLanguages');
    Discussion.prototype.language = Model.hasOne('language');

    addLanguageComposer();
    addLanguageToDiscussionList();
});
