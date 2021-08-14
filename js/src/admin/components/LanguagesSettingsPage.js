import app from 'flarum/admin/app';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import Button from 'flarum/common/components/Button';
import Select from 'flarum/common/components/Select';
import Switch from 'flarum/common/components/Switch';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import icon from 'flarum/common/helpers/icon';
import saveSettings from 'flarum/admin/utils/saveSettings';
import Stream from 'flarum/common/utils/Stream';
import Alert from 'flarum/common/components/Alert';
import getLocales, * as locales from '../utils/locales';
import getCountries, * as countries from '../utils/countries';
import flag from '../../common/utils/flag';

export default class LanguagesSettingsPage extends ExtensionPage {
    oninit(vnode) {
        super.oninit(vnode);

        this.updating = {};
        this.deleting = {};

        this.codes = {};
        this.countries = {};

        this.newLocale = Stream('');
        this.newCountry = Stream('');

        this.nativeKey = 'fof-discussion-language.native';
        this.native = !!Number(app.data.settings[this.nativeKey]);

        this.showFlagsKey = 'fof-discussion-language.showFlags';
        this.showFlags = app.data.settings[this.showFlagsKey];

        this.composerLocaleDefaultKey = 'fof-discussion-language.composerLocaleDefault';
        this.composerLocaleDefault = app.data.settings[this.composerLocaleDefaultKey] || 0;

        this.localeSortKey = 'fof-discussion-language.filter_language_on_http_request';
        this.localeSort = app.data.settings[this.localeSortKey];

        this.showAnyLangOptKey = 'fof-discussion-language.showAnyLangOpt';
        this.showAnyLangOpt = app.data.settings[this.showAnyLangOptKey];

        this.loadingData = true;
        this.loadingDataError = false;
    }

    oncreate(vnode) {
        super.oncreate(vnode);

        this.refresh();
    }

    refresh() {
        this.loadingData = true;
        this.loadingDataError = false;

        m.redraw();

        return Promise.all([locales.load(), countries.load()])
            .then(() => {
                this.loadingData = false;
                this.loadingDataError = false;

                m.redraw();
            })
            .catch((e) => {
                console.error(e);

                this.loadingData = false;
                this.loadingDataError = true;

                m.redraw();
            });
    }

    content() {
        const locales = getLocales(this.native);
        const countryData = getCountries(this.native);

        return [
            <div className="container">
                <div className="FofDiscussionLanguagesSettingsPage">
                    <div className="Form-group">
                        {Switch.component(
                            {
                                state: this.native,
                                onchange: (val) => {
                                    this.native = val;
                                    this.refresh();
                                    m.redraw.sync();
                                },
                            },
                            app.translator.trans('fof-discussion-language.admin.settings.native_label')
                        )}
                    </div>

                    <div className="Form-group">
                        {Switch.component(
                            {
                                state: this.showFlags,
                                onchange: (val) => (this.showFlags = val),
                            },
                            app.translator.trans('fof-discussion-language.admin.settings.show_flag_label')
                        )}
                    </div>

                    <div className="Form-group">
                        {Switch.component(
                            {
                                state: this.composerLocaleDefault,
                                onchange: (val) => (this.composerLocaleDefault = val),
                            },
                            app.translator.trans('fof-discussion-language.admin.settings.composer_default_label')
                        )}
                    </div>

                    <div className="Form-group">
                        {Switch.component(
                            {
                                state: this.localeSort,
                                onchange: (value) => (this.localeSort = value),
                            },
                            app.translator.trans('fof-discussion-language.admin.settings.locale_sort_label')
                        )}
                    </div>

                    <div className="Form-group">
                        {Switch.component(
                            {
                                state: this.showAnyLangOpt,
                                onchange: (value) => (this.showAnyLangOpt = value),
                            },
                            app.translator.trans('fof-discussion-language.admin.settings.show_any_lang_opt_label')
                        )}
                    </div>

                    <hr />

                    {this.loadingData ? (
                        <LoadingIndicator />
                    ) : this.loadingDataError ? (
                        <Alert ondismiss={this.refresh.bind(this)} type="error">
                            {app.translator.trans('fof-discussion-language.admin.settings.errors.loading_data')}
                        </Alert>
                    ) : (
                        <form>
                            <div className="Form-group flex">
                                {Select.component({
                                    onchange: this.newLocale,
                                    value: this.newLocale(),
                                    options: locales,
                                })}

                                {Select.component({
                                    onchange: this.newCountry,
                                    value: this.newCountry(),
                                    options: countryData,
                                })}

                                {flag(countries.getEmoji(this.newCountry()))}

                                {Button.component(
                                    {
                                        className: 'Button Button--primary',
                                        onclick: this.add.bind(this),
                                        disabled: !this.newLocale() || !this.newCountry() || this.adding,
                                    },
                                    icon(this.adding ? 'fas fa-spinner fa-spin' : 'fas fa-plus')
                                )}
                            </div>

                            <div className="Form-group">
                                {app.store.all('discussion-languages').map((language) => {
                                    const id = language.id();
                                    const updating = this.updating[id];
                                    const deleting = this.deleting[id];

                                    const country = this.countries[id] || language.country();

                                    return (
                                        <div className="flex">
                                            {Select.component({
                                                onchange: (val) => (this.codes[id] = val),
                                                value: this.codes[id] || language.code(),
                                                options: locales,
                                                disabled: updating || deleting,
                                            })}

                                            {Select.component({
                                                onchange: (val) => (this.countries[id] = val),
                                                value: country,
                                                options: countryData,
                                                disabled: updating || deleting,
                                            })}

                                            {flag(countries.getEmoji(country))}

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

                            <div className="Form-group">
                                <Button
                                    type="submit"
                                    className="Button Button--primary"
                                    loading={this.loading}
                                    disabled={!this.isChanged()}
                                    onclick={this.save.bind(this)}
                                >
                                    {app.translator.trans('core.admin.settings.submit_button')}
                                </Button>
                            </div>
                        </form>
                    )}
                </div>
            </div>,
        ];
    }

    onkeydown(e) {
        if (e.key === 'Enter') {
            this.add();
            e.preventDefault();
        }
    }

    add() {
        if (this.adding || !this.newLocale()) return;

        this.adding = true;

        const code = this.newLocale();
        const country = this.newCountry();
        const language = app.store.createRecord('discussion-languages');

        language
            .save({ code, country })
            .then(() => {
                this.newLocale('');

                this.adding = false;

                m.redraw();
            })
            .catch(() => (this.adding = false));
    }

    save(e) {
        e.preventDefault();

        this.loading = true;

        Promise.all([
            ...this.dirty().map((language) => {
                const id = language.id();

                this.updating[id] = true;

                return language
                    .save({ code: this.codes[id], country: this.countries[id] })
                    .then(
                        () => {},
                        () => {}
                    )
                    .then(() => {
                        this.updating[id] = false;

                        m.redraw();
                    });
            }),
            saveSettings({
                [this.nativeKey]: this.native,
                [this.showFlagsKey]: this.showFlags,
                [this.composerLocaleDefaultKey]: this.composerLocaleDefault,
                [this.localeSortKey]: this.localeSort,
                [this.showAnyLangOptKey]: this.showAnyLangOpt,
            }).then(this.onsaved.bind(this)),
        ]);
    }

    remove(language) {
        this.deleting[language.id()] = true;

        language
            .delete()
            .then(
                () => {},
                () => {}
            )
            .then(() => {
                delete this.deleting[language.id()];
                m.redraw();
            });
    }

    dirty() {
        return app.store.all('discussion-languages').filter((l) => {
            const id = l.id();

            return (this.codes[id] && this.codes[id] !== l.code()) || (this.countries[id] && this.countries[id] !== l.country());
        });
    }

    isChanged() {
        const dirty = this.dirty().length;
        const native = Number(this.native) !== Number(app.data.settings[this.nativeKey] || 0);
        const flag = Number(this.showFlags) !== Number(app.data.settings[this.showFlagsKey] || 0);
        const composerLocale = Number(this.composerLocaleDefault) !== Number(app.data.settings[this.composerLocaleDefaultKey] || 0);
        const locale = Number(this.localeSort) !== Number(app.data.settings[this.localeSortKey] || 0);
        const anyOpt = Number(this.showAnyLangOpt) !== Number(app.data.settings[this.showAnyLangOptKey] || 0);

        return dirty || native || flag || composerLocale || locale || anyOpt;
    }
}
