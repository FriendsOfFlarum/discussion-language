import app from 'flarum/forum/app';
import Modal from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';
import DiscussionPage from 'flarum/forum/components/DiscussionPage';

import LanguageDisplay from './LanguageDisplay';

export default class LanguageDiscussionModal extends Modal {
  oninit(vnode) {
    super.oninit(vnode);

    this.languages = app.store.all('discussion-languages');

    this.current = this.attrs.selected || (this.attrs.discussion && this.attrs.discussion.language());
    this.selected = this.current;
  }

  className() {
    return 'FoFLanguageDiscussionModal';
  }

  title() {
    return this.attrs.discussion
      ? app.translator.trans('fof-discussion-language.forum.change_language.edit_title', { title: <em>{this.attrs.discussion.title()}</em> })
      : app.translator.trans('fof-discussion-language.forum.change_language.title');
  }

  content() {
    return [
      <div className="Modal-body">
        <div className="Form-group">
          {this.languages.map((language) => (
            <Button onclick={this.select.bind(this, language)} className={`Button Button--block ${this.selected === language ? 'active' : ''}`}>
              <LanguageDisplay language={language} uppercase={true} />
            </Button>
          ))}
        </div>

        {!this.attrs.hideSubmitButton && (
          <div className="App-primaryControl">
            {Button.component(
              {
                type: 'submit',
                className: 'Button Button--primary',
                disabled: !this.selected || this.selected === this.current,
                loading: this.loading,
                icon: 'fas fa-check',
              },
              app.translator.trans('fof-discussion-language.forum.change_language.submit_button')
            )}
          </div>
        )}
      </div>,
    ];
  }

  select(language) {
    this.selected = language;

    if (this.attrs.hideSubmitButton) return this.onsubmit();

    m.redraw();
  }

  onsubmit(e) {
    if (e) e.preventDefault();

    const { discussion, onsubmit } = this.attrs;

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
