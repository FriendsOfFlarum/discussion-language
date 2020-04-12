import Modal from 'flarum/components/Modal';
import Button from 'flarum/components/Button';

export default class LanguagesSettingsModal extends Modal {
    init() {
        super.init();

        this.deleting = {};
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
                <div className="Form-group">
                    <label>Locale</label>

                    <div>
                        <input className="FormControl" bidi={this.newLocale} onKeyDown={this.onkeydown.bind(this)} />
                        {Button.component({
                            className: 'Button Button--primary',
                            children: 'Add',
                            onclick: this.add.bind(this),
                            disabled: !this.validate(),
                            loading: this.adding,
                        })}
                    </div>
                </div>

                <div className="Form-group">
                    {app.store.all('discussion-languages').map((language) =>
                        Button.component({
                            className: 'Button Button--link',
                            children: <code>{language.code()}</code>,
                            onclick: this.remove.bind(this, language),
                            loading: this.deleting[language.id()],
                        })
                    )}
                </div>
            </div>,
        ];
    }

    validate() {
        try {
            Intl.getCanonicalLocales(this.newLocale());
        } catch (err) {
            return false;
        }

        return true;
    }

    onkeydown(e) {
        if (e.key === 'Enter') {
            this.add();
            e.preventDefault();
        }
    }

    add() {
        if (this.adding || !this.validate()) return;

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

    async remove(language) {
        this.deleting[language.id()] = true;

        language
            .delete()
            .then(() => delete this.deleting[language.id()])
            .catch(() => delete this.deleting[language.id()])
            .then(() => m.redraw());
    }
}
