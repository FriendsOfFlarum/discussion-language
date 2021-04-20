import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import Button from 'flarum/common/components/Button';
import Select from 'flarum/common/components/Select';
import Switch from 'flarum/common/components/Switch';
import icon from 'flarum/common/helpers/icon';
import saveSettings from 'flarum/admin/utils/saveSettings';
import Stream from 'flarum/common/utils/Stream';
import getLocales from '../utils/locales';
import { default as getCountries, getCountryEmoji } from '../utils/countries';
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
        this.native = app.data.settings[this.nativeKey];

        this.showFlagsKey = 'fof-discussion-language.showFlags';
        this.showFlags = app.data.settings[this.showFlagsKey];

        this.composerLocaleDefaultKey = 'fof-discussion-language.composerLocaleDefault';
        this.composerLocaleDefault = app.data.settings[this.composerLocaleDefaultKey] || 0;

        this.localeSortKey = 'fof-discussion-language.filter_language_on_http_request';
        this.localeSort = app.data.settings[this.localeSortKey];
    }

    content() {
        const locales = getLocales(this.native);
        const countries = getCountries(this.native);

        return [
            <div className="container">
                <div className="FofDiscussionLanguagesSettingsPage">
                    <div className="Form-group">
                        {Switch.component(
                            {
                                state: this.native,
                                onchange: (val) => {
                                    this.native = val;
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

                    <hr />

                    <div className="Form-group flex">
                        {Select.component({
                            onchange: this.newLocale,
                            value: this.newLocale(),
                            options: locales,
                        })}

                        {Select.component({
                            onchange: this.newCountry,
                            value: this.newCountry(),
                            options: countries,
                        })}

                        {flag(getCountryEmoji(this.newCountry()))}

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
                                        options: countries,
                                        disabled: updating || deleting,
                                    })}

                                    {flag(getCountryEmoji(country))}

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
                    });
            }),
            saveSettings({
                [this.nativeKey]: this.native,
                [this.showFlagsKey]: this.showFlags,
                [this.composerLocaleDefaultKey]: this.composerLocaleDefault,
                [this.localeSortKey]: this.localeSort,
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

        return dirty || native || flag || composerLocale || locale;
    }
}
