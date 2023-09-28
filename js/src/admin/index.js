import app from 'flarum/admin/app';
import SettingDropdown from 'flarum/admin/components/SettingDropdown';

import LanguagesSettingsPage from './components/LanguagesSettingsPage';

export * from './components';
export * from './utils';
export * from '../common/models';
export { default as extend } from './extend';

app.initializers.add('fof/discussion-language', () => {
  app.extensionData
    .for('fof-discussion-language')
    .registerPage(LanguagesSettingsPage)
    .registerPermission(
      {
        permission: 'discussion.changeLanguageModerate',
        icon: 'fas fa-globe',
        label: app.translator.trans('fof-discussion-language.admin.permissions.allow_change_language_label'),
      },
      'moderate',
      65
    )
    .registerPermission(
      {
        icon: 'fas fa-globe',
        label: app.translator.trans('fof-discussion-language.admin.permissions.allow_change_language_label'),
        setting: () => {
          const minutes = parseInt(app.data.settings['fof-discussion-language.allow_language_change']);

          return SettingDropdown.component({
            defaultLabel: minutes
              ? app.translator.trans('core.admin.permissions_controls.allow_some_minutes_button', { count: minutes })
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
      'start',
      65
    );
});
