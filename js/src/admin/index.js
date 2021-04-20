import { extend } from 'flarum/common/extend';
import Forum from 'flarum/common/models/Forum';
import PermissionGrid from 'flarum/admin/components/PermissionGrid';
import SettingDropdown from 'flarum/admin/components/SettingDropdown';

import LanguagesSettingsPage from './components/LanguagesSettingsPage';
import Language from '../common/models/Language';

export * from './components';
export * from './utils';
export * from '../common/models';

app.initializers.add('fof/discussion-language', () => {
    app.store.models['discussion-languages'] = Language;

    Forum.prototype.discussionLanguages = Forum.hasMany('discussionLanguages');

    app.extensionData.for('fof-discussion-language').registerPage(LanguagesSettingsPage);

    // This extend must remain for now, as the new admin UI in beta 15 does not currently support the custom dropdown
    extend(PermissionGrid.prototype, 'startItems', (items) => {
        items.add(
            'allowLanguageChange',
            {
                icon: 'fas fa-globe',
                label: app.translator.trans('fof-discussion-language.admin.permissions.allow_change_language_label'),
                setting: () => {
                    const minutes = parseInt(app.data.settings['fof-discussion-language.allow_tag_change'], 10);

                    return SettingDropdown.component({
                        defaultLabel: minutes
                            ? app.translator.transChoice('core.admin.permissions_controls.allow_some_minutes_button', minutes, { count: minutes })
                            : app.translator.trans('core.admin.permissions_controls.allow_indefinitely_button'),
                        key: 'fof-discussion-language.allow_language_change',
                        options: [
                            { value: '-1', label: app.translator.trans('core.admin.permissions_controls.allow_indefinitely_button') },
                            { value: '10', label: app.translator.trans('core.admin.permissions_controls.allow_ten_minutes_button') },
                            { value: 'reply', label: app.translator.trans('core.admin.permissions_controls.allow_until_reply_button') },
                        ],
                    });
                },
            },
            90
        );
    });
});
