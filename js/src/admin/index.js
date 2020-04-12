import Forum from 'flarum/models/Forum';

import LanguagesSettingsModal from './components/LanguagesSettingsModal';
import Language from '../common/models/Language';

app.initializers.add('fof/discussion-language', () => {
    app.store.models['discussion-languages'] = Language;

    Forum.prototype.discussionLanguages = Forum.hasMany('discussionLanguages');

    app.extensionSettings['fof-discussion-language'] = () => app.modal.show(new LanguagesSettingsModal());
});
