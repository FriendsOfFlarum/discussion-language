import Modal from 'flarum/components/Modal';
import Button from 'flarum/components/Button';
import Select from 'flarum/components/Select';
import Switch from 'flarum/components/Switch';
import icon from 'flarum/helpers/icon';
import saveSettings from 'flarum/utils/saveSettings';
import Stream from 'flarum/utils/Stream';
import getLocales from '../utils/locales';
import { default as getCountries, getCountryEmoji } from '../utils/countries';
import flag from '../../common/utils/flag';

export default class LanguagesSettingsModal extends Modal {
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
    }

    className() {
        return 'FofDiscussionLanguagesSettingsModal Modal--medium';
    }

    title() {
        return app.translator.trans('fof-discussion-language.admin.settings.title');
    }

    content() {
        const locales = getLocales(this.native);
        const countries = getCountries(this.native);

        return [
            <div className="Modal-body">
                <div className="Form-group">
                    {Switch.component({
                        state: this.native,
                        onchange: (val) => (this.native = val),
                    }, app.translator.trans('fof-discussion-language.admin.settings.native_label'))}
                </div>

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

                    {Button.component({
                        className: 'Button Button--primary',
                        onclick: this.add.bind(this),
                        disabled: !this.newLocale() || !this.newCountry() || this.adding,
                    }, icon(this.adding ? 'fas fa-spinner fa-spin' : 'fas fa-plus'))}
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

                                {Button.component({
                                    className: `Button Button--danger`,
                                    disabled: deleting,
                                    onclick: this.remove.bind(this, language),
                                }, icon(deleting ? 'fas fa-spinner fa-spin' : 'fas fa-times'))}
                            </div>
                        );
                    })}
                </div>

                <div className="Form-group">
                    <Button
                        type="submit"
                        className="Button Button--primary"
                        loading={this.loading}
                        disabled={!this.changed()}
                        onclick={this.save.bind(this)}
                    >
                        {app.translator.trans('core.admin.settings.submit_button')}
                    </Button>
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

    save() {
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
            saveSettings({ [this.nativeKey]: this.native }),
        ]).then(this.hide.bind(this), this.loaded.bind(this));
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

    changed() {
        return this.dirty().length || Number(this.native) !== Number(app.data.settings[this.nativeKey] || 0);
    }
}
