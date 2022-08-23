import app from 'flarum/admin/app';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import Button from 'flarum/common/components/Button';
import Select from 'flarum/common/components/Select';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import icon from 'flarum/common/helpers/icon';
import Stream from 'flarum/common/utils/Stream';
import Alert from 'flarum/common/components/Alert';
import getLocales, * as locales from '../utils/locales';
import getCountries, * as countries from '../utils/countries';
import Language from '../../common/models/Language';

import type Mithril from 'mithril';

export default class LanguagesSettingsPage extends ExtensionPage<never> {
  recordsUpdating: Record<string, boolean> = {};
  recordsDeleting: Record<string, boolean> = {};

  newLocaleValue = Stream<string>('');
  newCountryValue = Stream<string>('');

  loadingData: boolean = true;
  loadingDataError: boolean = false;

  errorDetails: null | Error = null;

  isAddingNewRecord: boolean = false;

  oncreate(vnode: Mithril.Vnode<never, this>) {
    super.oncreate(vnode);

    this.loadData();
  }

  async loadData() {
    this.loadingData = true;
    this.loadingDataError = false;

    m.redraw();

    try {
      await Promise.all([locales.load(), countries.load()]);

      this.loadingData = false;
      this.loadingDataError = false;

      m.redraw();
    } catch (e) {
      console.error(e);

      this.loadingData = false;
      this.loadingDataError = true;
      this.errorDetails = e as Error;

      m.redraw();
    }
  }

  content(vnode: Mithril.VnodeDOM<never, this>) {
    return (
      <div className="container">
        <div className="FofDiscussionLanguagesSettingsPage">
          <div className="Form-group">
            {this.buildSettingComponent({
              type: 'bool',
              label: app.translator.trans('fof-discussion-language.admin.settings.native_label'),
              setting: 'fof-discussion-language.native',
            })}
          </div>

          <div className="Form-group">
            {this.buildSettingComponent({
              type: 'bool',
              label: app.translator.trans('fof-discussion-language.admin.settings.show_flag_label'),
              setting: 'fof-discussion-language.showFlags',
            })}
          </div>

          <div className="Form-group">
            {this.buildSettingComponent({
              type: 'bool',
              label: app.translator.trans('fof-discussion-language.admin.settings.composer_default_label'),
              setting: 'fof-discussion-language.composerLocaleDefault',
            })}
          </div>

          <div className="Form-group">
            {this.buildSettingComponent({
              type: 'bool',
              label: app.translator.trans('fof-discussion-language.admin.settings.locale_sort_label'),
              setting: 'fof-discussion-language.filter_language_on_http_request',
            })}
          </div>

          <div className="Form-group">
            {this.buildSettingComponent({
              type: 'bool',
              label: app.translator.trans('fof-discussion-language.admin.settings.show_any_lang_opt_label'),
              setting: 'fof-discussion-language.showAnyLangOpt',
            })}
          </div>

          <div className="Form-group">
            {this.buildSettingComponent({
              type: 'bool',
              label: app.translator.trans('fof-discussion-language.admin.settings.tags_page_discussion_locale_label'),
              setting: 'fof-discussion-language.useLocaleForTagsPageLastDiscussion',
            })}
          </div>

          {this.submitButton(vnode)}

          <hr />

          {this.localeSettings()}

          <p className="helpText">
            This extension uses material from the Wikipedia article{' '}
            <a href="https://en.wikipedia.org/wiki/List_of_ISO_639-2_codes" target="_blank" rel="noreferrer nofollow noopener">
              List of ISO 639-2 Codes
            </a>
            , which is released under the{' '}
            <a href="https://creativecommons.org/licenses/by-sa/3.0/">Creative Commons Attribution-Share-Alike License 3.0</a>.
          </p>
        </div>
      </div>
    );
  }

  localeSettings() {
    if (this.loadingData) return <LoadingIndicator />;

    if (this.loadingDataError) {
      return (
        <Alert dismissible={false} type="error">
          {app.translator.trans('fof-discussion-language.admin.settings.errors.missing_assets')}
          <br />
          <b>Error:</b> <code>{this.errorDetails?.message}</code>
        </Alert>
      );
    }

    const nativeNames = !!Number(this.setting('fof-discussion-language.native')());

    const locales = getLocales(nativeNames);
    const countryData = getCountries(nativeNames);

    return (
      <>
        <div className="Form-group flex" style={{ 'margin-bottom': 0, 'font-weight': 'bold' }}>
          <span>Language</span>
          <span>Country (for flag)</span>
          <span></span>
          <span></span>
        </div>
        <div className="Form-group flex">
          {Select.component({
            onchange: this.newLocaleValue,
            value: this.newLocaleValue(),
            options: locales,
          })}

          {Select.component({
            onchange: this.newCountryValue,
            value: this.newCountryValue(),
            options: countryData,
          })}

          {Button.component(
            {
              className: 'Button Button--primary',
              onclick: this.add.bind(this),
              disabled: !this.newLocaleValue() || !this.newCountryValue() || this.isAddingNewRecord,
            },
            icon(this.isAddingNewRecord ? 'fas fa-spinner fa-spin' : 'fas fa-plus')
          )}
        </div>

        <div className="Form-group">
          {app.store.all<Language>('discussion-languages').map((language) => {
            const id = language.id();

            const updating = this.recordsUpdating[id];
            const deleting = this.recordsDeleting[id];

            const country = language.country();

            return (
              <div className="flex">
                {Select.component({
                  onchange: (val: string) => {
                    this.recordsUpdating[id!] = true;

                    language
                      .save({
                        code: val,
                      })
                      .then(() => {
                        this.recordsUpdating[id!] = false;
                        m.redraw();
                      });
                  },
                  value: language.code(),
                  options: locales,
                  disabled: updating || deleting,
                })}

                {Select.component({
                  onchange: (val: string) => {
                    this.recordsUpdating[id!] = true;

                    language
                      .save({
                        country: val,
                      })
                      .then(() => {
                        this.recordsUpdating[id!] = false;
                        m.redraw();
                      });
                  },
                  value: country,
                  options: countryData,
                  disabled: updating || deleting,
                })}

                {Button.component(
                  {
                    className: `Button Button--danger`,
                    disabled: deleting,
                    onclick: this.remove.bind(this, language),
                  },
                  icon(deleting ? 'fas fa-spinner fa-spin' : 'fas fa-times')
                )}
              </div>
            );
          })}
        </div>
      </>
    );
  }

  onkeydown(e) {
    if (e.key === 'Enter') {
      this.add();
      e.preventDefault();
    }
  }

  add() {
    if (this.isAddingNewRecord || !this.newLocaleValue()) return;

    this.isAddingNewRecord = true;

    const code = this.newLocaleValue();
    const country = this.newCountryValue();
    const language = app.store.createRecord('discussion-languages');

    language
      .save({ code, country })
      .then(() => {
        this.newLocaleValue('');
        this.newCountryValue('');
      })
      .catch(() => {})
      .finally(() => {
        this.isAddingNewRecord = false;
        m.redraw();
      });
  }

  remove(language) {
    this.recordsDeleting[language.id()] = true;

    language
      .delete()
      .then(
        () => {},
        () => {}
      )
      .then(() => {
        delete this.recordsDeleting[language.id()];
        m.redraw();
      });
  }
}
