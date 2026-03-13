import app from 'flarum/forum/app';
import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';
import DiscussionPage from 'flarum/forum/components/DiscussionPage';
import type Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';

import Language from '../../common/models/Language';
import LanguageDisplay from './LanguageDisplay';

export interface LanguageDiscussionModalAttrs extends IInternalModalAttrs {
  discussion?: Discussion;
  selected?: Language;
  hideSubmitButton?: boolean;
  onsubmit?: (language: Language) => void;
}

export default class LanguageDiscussionModal extends Modal<LanguageDiscussionModalAttrs> {
  languages!: Language[];
  current!: Language | undefined;
  selected!: Language | undefined;

  oninit(vnode: Mithril.Vnode<LanguageDiscussionModalAttrs, this>) {
    super.oninit(vnode);

    this.languages = app.store.all<Language>('discussion-languages').filter((language) => language.code() !== 'any');

    this.current = this.attrs.selected || (this.attrs.discussion && this.attrs.discussion.language?.()) || undefined;
    this.selected = this.current;
  }

  className() {
    return 'FoFLanguageDiscussionModal';
  }

  title(): Mithril.Children {
    return this.attrs.discussion
      ? app.translator.trans('fof-discussion-language.forum.change_language.edit_title', { title: <em>{this.attrs.discussion.title()}</em> })
      : app.translator.trans('fof-discussion-language.forum.change_language.title');
  }

  content(): Mithril.Children {
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

  select(language: Language) {
    this.selected = language;

    if (this.attrs.hideSubmitButton) return this.onsubmit();

    m.redraw();
  }

  onsubmit(e?: SubmitEvent) {
    if (e) e.preventDefault();

    const { discussion, onsubmit } = this.attrs;

    this.loading = true;

    if (!discussion) {
      this.hide();

      if (onsubmit && this.selected) onsubmit(this.selected);

      return;
    }

    const language = this.selected ?? null;

    discussion
      .save({ relationships: { language } })
      .then(() => {
        if (app.current instanceof DiscussionPage) {
          (app.current as any).stream.update();
        }

        return this.hide();
      })
      .catch(this.loaded.bind(this));
  }
}
