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

                {!this.props.hideSubmitButton && (
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
                )}
            </div>,
        ];
    }

    select(language) {
        this.selected = language;

        if (this.props.hideSubmitButton) return this.onsubmit();

        m.redraw();
    }

    onsubmit(e) {
        if (e) e.preventDefault();

        const { discussion, onsubmit } = this.props;

        this.loading = true;

        if (!discussion) {
            this.hide();

            if (onsubmit) onsubmit(this.selected);

            return;
        }

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
