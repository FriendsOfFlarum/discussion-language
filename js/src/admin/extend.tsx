import app from 'flarum/admin/app';
import Extend from 'flarum/common/extenders';
import SettingDropdown from 'flarum/admin/components/SettingDropdown';
import commonExtend from '../common/extend';
import LanguagesSettingsPage from './components/LanguagesSettingsPage';

export default [
  ...commonExtend,

  new Extend.Admin()
    .page(LanguagesSettingsPage)
    .setting(
      () => ({
        setting: 'fof-discussion-language.native',
        type: 'bool',
        label: app.translator.trans('fof-discussion-language.admin.settings.native_label'),
      }),
      60
    )
    .setting(
      () => ({
        setting: 'fof-discussion-language.showFlags',
        type: 'bool',
        label: app.translator.trans('fof-discussion-language.admin.settings.show_flag_label'),
      }),
      50
    )
    .setting(
      () => ({
        setting: 'fof-discussion-language.composerLocaleDefault',
        type: 'bool',
        label: app.translator.trans('fof-discussion-language.admin.settings.composer_default_label'),
      }),
      40
    )
    .setting(
      () => ({
        setting: 'fof-discussion-language.filter_language_on_http_request',
        type: 'bool',
        label: app.translator.trans('fof-discussion-language.admin.settings.locale_sort_label'),
      }),
      30
    )
    .setting(
      () => ({
        setting: 'fof-discussion-language.showAnyLangOpt',
        type: 'bool',
        label: app.translator.trans('fof-discussion-language.admin.settings.show_any_lang_opt_label'),
      }),
      20
    )
    .setting(
      () => ({
        setting: 'fof-discussion-language.useLocaleForTagsPageLastDiscussion',
        type: 'bool',
        label: app.translator.trans('fof-discussion-language.admin.settings.tags_page_discussion_locale_label'),
      }),
      10
    )
    .permission(
      () => ({
        permission: 'discussion.changeLanguageModerate',
        icon: 'fas fa-globe',
        label: app.translator.trans('fof-discussion-language.admin.permissions.change_language_label'),
      }),
      'moderate',
      65
    )
    .permission(
      () => ({
        icon: 'fas fa-globe',
        label: app.translator.trans('fof-discussion-language.admin.permissions.allow_change_language_label'),
        // A row for the setting rather than a permission, keyed by it, as core's allow_renaming is.
        id: 'fof-discussion-language.allow_language_change',
        setting: () => {
          const minutes = parseInt(app.data.settings['fof-discussion-language.allow_language_change'], 10);

          return (
            <SettingDropdown
              defaultLabel={
                minutes
                  ? app.translator.trans('core.admin.permissions_controls.allow_some_minutes_button', { count: minutes })
                  : app.translator.trans('core.admin.permissions_controls.allow_indefinitely_button')
              }
              setting="fof-discussion-language.allow_language_change"
              options={[
                { value: '-1', label: app.translator.trans('core.admin.permissions_controls.allow_indefinitely_button') },
                { value: '10', label: app.translator.trans('core.admin.permissions_controls.allow_ten_minutes_button') },
                { value: 'reply', label: app.translator.trans('core.admin.permissions_controls.allow_until_reply_button') },
              ]}
            />
          );
        },
      }),
      'start',
      65
    ),
] satisfies object[];
