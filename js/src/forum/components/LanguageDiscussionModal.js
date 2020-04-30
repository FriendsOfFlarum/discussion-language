import Modal from 'flarum/components/Modal';
import Button from 'flarum/components/Button';
import DiscussionPage from 'flarum/components/DiscussionPage';

import Language from './Language';

export default class LanguageDiscussionModal extends Modal {
    init() {
        super.init();

        this.languages = app.store.all('discussion-languages');

        this.current = this.props.selected || (this.props.discussion && this.props.discussion.language());
        this.selected = this.current;
    }

    className() {
        return 'FoFLanguageDiscussionModal';
    }

    title() {
        return this.props.discussion
            ? app.translator.trans('fof-discussion-language.forum.change_language.edit_title', { title: <em>{this.props.discussion.title()}</em> })
            : app.translator.trans('fof-discussion-language.forum.change_language.title');
    }

    content() {
        return [
            <div className="Modal-body">
                <div className="Form-group">
                    {this.languages.map((language) => (
                        <Button
                            onclick={this.select.bind(this, language)}
                            className={`Button Button--block ${this.selected === language ? 'active' : ''}`}
                        >
                            <Language language={language} uppercase={true} />
                        </Button>
                    ))}
                </div>

                <div className="App-primaryControl">
                    {Button.component({
                        type: 'submit',
                        className: 'Button Button--primary',
                        disabled: !this.selected || this.selected === this.current,
                        loading: this.loading,
                        icon: 'fas fa-check',
                        children: app.translator.trans('fof-discussion-language.forum.change_language.submit_button'),
                    })}
                </div>
            </div>,
        ];
    }

    select(language) {
        this.selected = language;

        m.redraw();
    }

    onsubmit(e) {
        e.preventDefault();

        this.loading = true;

        if (this.props.onsubmit) {
            this.props.onsubmit(this.selected);
            this.hide();
            return;
        }

        const discussion = this.props.discussion;
        const language = this.selected;

        discussion
            .save({ relationships: { language } })
            .then(() => {
                if (app.current instanceof DiscussionPage) {
                    app.current.stream.update();
                }

                return this.hide();
            })
            .catch(this.loaded.bind(this));
    }
}
