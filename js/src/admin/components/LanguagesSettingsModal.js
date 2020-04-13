import Modal from 'flarum/components/Modal';
import Button from 'flarum/components/Button';
import icon from 'flarum/helpers/icon';

export default class LanguagesSettingsModal extends Modal {
    init() {
        super.init();

        this.updating = {};
        this.deleting = {};

        this.codes = {};
        this.newLocale = m.prop('');
    }

    className() {
        return 'FofDiscussionLanguagesSettingsModal Modal--medium';
    }

    title() {
        return app.translator.trans('fof-discussion-language.admin.settings.title');
    }

    content() {
        return [
            <div className="Modal-body">
                <div className="Form-group flex">
                    <input className="FormControl" bidi={this.newLocale} onkeydown={this.onkeydown.bind(this)} />
                    {Button.component({
                        className: 'Button Button--primary',
                        children: icon(this.adding ? 'fas fa-spinner fa-spin' : 'fas fa-plus'),
                        onclick: this.add.bind(this),
                        disabled: !this.newLocale() || this.adding,
                    })}
                </div>

                <div className="Form-group">
                    {app.store.all('discussion-languages').map((language) => {
                        const id = language.id();
                        const updating = this.updating[id];
                        const deleting = this.deleting[id];
                        const value = this.codes[id];

                        return (
                            <div className="flex">
                                <input
                                    className="FormControl"
                                    value={value || value === '' ? value : language.code()}
                                    placeholder={language.code()}
                                    oninput={m.withAttr('value', (val) => (this.codes[id] = val))}
                                    onchange={this.update.bind(this, language)}
                                    disabled={updating || deleting}
                                />
                                {Button.component({
                                    className: `Button Button--danger`,
                                    children: icon(deleting ? 'fas fa-spinner fa-spin' : 'fas fa-times'),
                                    disabled: deleting,
                                    onclick: this.remove.bind(this, language),
                                })}
                            </div>
                        );
                    })}
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
        const language = app.store.createRecord('discussion-languages');

        language
            .save({ code })
            .then(() => {
                this.newLocale('');

                this.adding = false;

                m.redraw();
            })
            .catch(() => (this.adding = false));
    }

    update(language) {
        this.updating[language.id()] = true;

        language
            .save({ code: this.codes[language.id()] })
            .then(
                () => {},
                () => {}
            )
            .then(() => {
                this.updating[language.id()] = false;

                m.redraw();
            });
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
}
