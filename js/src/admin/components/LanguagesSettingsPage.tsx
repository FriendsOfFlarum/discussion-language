import app from 'flarum/admin/app';
import ExtensionPage, { type ExtensionPageAttrs } from 'flarum/admin/components/ExtensionPage';
import Button from 'flarum/common/components/Button';
import Select from 'flarum/common/components/Select';
import Form from 'flarum/common/components/Form';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import Stream from 'flarum/common/utils/Stream';
import Alert from 'flarum/common/components/Alert';
import type ItemList from 'flarum/common/utils/ItemList';
import getLocales, * as locales from '../utils/locales';
import getCountries, * as countries from '../utils/countries';
import Language from '../../common/models/Language';

import type Mithril from 'mithril';

export default class LanguagesSettingsPage extends ExtensionPage {
  recordsUpdating: Record<string, boolean> = {};
  recordsDeleting: Record<string, boolean> = {};

  newLocaleValue = Stream<string>('');
  newCountryValue = Stream<string>('');

  loadingData: boolean = true;
  loadingDataError: boolean = false;

  errorDetails: null | Error = null;

  isAddingNewRecord: boolean = false;

  oncreate(vnode: Mithril.VnodeDOM<ExtensionPageAttrs, this>) {
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

  sections(vnode: Mithril.VnodeDOM<ExtensionPageAttrs, this>): ItemList<unknown> {
    const items = super.sections(vnode);

    // Between the settings and the permissions.
    items.setPriority('content', 20);
    items.add('languages', this.languagesSection(), 10);

    return items;
  }

  languagesSection() {
    return (
      <div className="ExtensionPage-settings FofDiscussionLanguages">
        <div className="container">
          <Form
            label={app.translator.trans('fof-discussion-language.admin.settings.languages_heading', {}, true)}
            description={app.translator.trans('fof-discussion-language.admin.settings.languages_help', {}, true)}
          >
            {this.languageList()}
          </Form>

          <p className="helpText FofDiscussionLanguages-attribution">
            {app.translator.trans('fof-discussion-language.admin.settings.attribution', {
              article: ({ children }: { children: Mithril.Children }) => (
                <a href="https://en.wikipedia.org/wiki/List_of_ISO_639-2_codes" target="_blank" rel="noreferrer nofollow noopener">
                  {children}
                </a>
              ),
              license: ({ children }: { children: Mithril.Children }) => (
                <a href="https://creativecommons.org/licenses/by-sa/3.0/" target="_blank" rel="noreferrer nofollow noopener">
                  {children}
                </a>
              ),
            })}
          </p>
        </div>
      </div>
    );
  }

  languageList() {
    if (this.loadingData) return <LoadingIndicator />;

    if (this.loadingDataError) {
      return (
        <Alert dismissible={false} type="error">
          {app.translator.trans('fof-discussion-language.admin.settings.errors.missing_assets')}
          <br />
          <code>{this.errorDetails?.message}</code>
        </Alert>
      );
    }

    const nativeNames = !!Number(this.setting('fof-discussion-language.native')());

    const localeOptions = getLocales(nativeNames);
    const countryOptions = getCountries(nativeNames);

    const languageLabel = app.translator.trans('fof-discussion-language.admin.settings.config.language_label', {}, true);
    const countryLabel = app.translator.trans('fof-discussion-language.admin.settings.config.country_label', {}, true);

    // A disabled first option, shown while nothing is chosen.
    const chooseLanguage = {
      '': { label: app.translator.trans('fof-discussion-language.admin.settings.config.choose_language', {}, true), disabled: true },
    };
    const chooseCountry = {
      '': { label: app.translator.trans('fof-discussion-language.admin.settings.config.choose_country', {}, true), disabled: true },
    };

    const languages = app.store.all<Language>('discussion-languages').filter((language) => language.id() !== 'any');

    return (
      <div className="FofDiscussionLanguages-table">
        <div className="FofDiscussionLanguages-row FofDiscussionLanguages-header" aria-hidden="true">
          <span>{languageLabel}</span>
          <span>{countryLabel}</span>
        </div>

        <div className="FofDiscussionLanguages-row">
          <Select
            aria-label={languageLabel}
            options={{ ...chooseLanguage, ...localeOptions }}
            value={this.newLocaleValue()}
            onchange={this.newLocaleValue}
            disabled={this.isAddingNewRecord}
          />
          <Select
            aria-label={countryLabel}
            options={{ ...chooseCountry, ...countryOptions }}
            value={this.newCountryValue()}
            onchange={this.newCountryValue}
            disabled={this.isAddingNewRecord}
          />
          <Button
            className="Button Button--primary Button--icon"
            icon="fas fa-plus"
            aria-label={app.translator.trans('fof-discussion-language.admin.settings.config.add_button', {}, true)}
            loading={this.isAddingNewRecord}
            disabled={!this.newLocaleValue() || !this.newCountryValue()}
            onclick={this.add.bind(this)}
          />
        </div>

        <div className="FofDiscussionLanguages-list">
          {languages.map((language) => {
            const id = language.id()!;
            const code = language.code();
            const busy = this.recordsUpdating[id] || this.recordsDeleting[id];

            return (
              <div className="FofDiscussionLanguages-row" key={id}>
                <Select
                  aria-label={languageLabel}
                  // A code outside the list is still shown as itself.
                  options={{ ...chooseLanguage, ...(code in localeOptions ? {} : { [code]: code }), ...localeOptions }}
                  value={code}
                  onchange={(value: string) => this.update(language, { code: value })}
                  disabled={busy}
                />
                <Select
                  aria-label={countryLabel}
                  options={{ ...chooseCountry, ...countryOptions }}
                  value={language.country() ?? ''}
                  onchange={(value: string) => this.update(language, { country: value })}
                  disabled={busy}
                />
                <Button
                  className="Button Button--danger Button--icon"
                  icon="fas fa-times"
                  aria-label={app.translator.trans(
                    'fof-discussion-language.admin.settings.config.remove_button',
                    { language: localeOptions[code] ?? code },
                    true
                  )}
                  loading={this.recordsDeleting[id]}
                  disabled={busy}
                  onclick={this.remove.bind(this, language)}
                />
              </div>
            );
          })}
        </div>
      </div>
    );
  }

  add() {
    if (this.isAddingNewRecord || !this.newLocaleValue()) return;

    this.isAddingNewRecord = true;

    const code = this.newLocaleValue();
    const country = this.newCountryValue();
    const language = app.store.createRecord<Language>('discussion-languages');

    language
      .save({ code, country })
      .then(() => {
        this.newLocaleValue('');
        this.newCountryValue('');
      })
      // The API's error is shown as an alert.
      .catch(() => {})
      .finally(() => {
        this.isAddingNewRecord = false;
        m.redraw();
      });
  }

  update(language: Language, attributes: { code?: string; country?: string }) {
    const id = language.id()!;
    this.recordsUpdating[id] = true;

    language
      .save(attributes)
      // The API's error is shown as an alert; the row comes back either way.
      .catch(() => {})
      .finally(() => {
        delete this.recordsUpdating[id];
        m.redraw();
      });
  }

  remove(language: Language) {
    const id = language.id()!;
    this.recordsDeleting[id] = true;

    language
      .delete()
      .catch(() => {})
      .finally(() => {
        delete this.recordsDeleting[id];
        m.redraw();
      });
  }
}
