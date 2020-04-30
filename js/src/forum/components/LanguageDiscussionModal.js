import Modal from 'flarum/components/Modal';
import Button from 'flarum/components/Button';
import DiscussionPage from 'flarum/components/DiscussionPage';

import flag from '../../common/utils/flag';

export default class LanguageDiscussionModal extends Modal {
    init() {
        super.init();

        this.languages = app.store.all('discussion-languages');

        this.selected = this.props.discussion && this.props.discussion.language();
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
                            {flag(language)}
                            &nbsp;
                            <span>{language.language().toUpperCase()}</span>
                        </Button>
                    ))}
                </div>

                <div className="App-primaryControl">
                    {Button.component({
                        type: 'submit',
                        className: 'Button Button--primary',
                        disabled: !this.selected || this.selected === this.props.discussion.language(),
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
