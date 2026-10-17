import app from 'flarum/forum/app';
import FormModal, { type IFormModalAttrs } from 'flarum/common/components/FormModal';
import Button from 'flarum/common/components/Button';
import type Discussion from 'flarum/common/models/Discussion';
import type PostStreamState from 'flarum/forum/states/PostStreamState';
import type Mithril from 'mithril';

import type Language from '../../common/models/Language';
import { ANY } from '../../common/utils/any';
import LanguageDisplay from './LanguageDisplay';

export interface LanguageDiscussionModalAttrs extends IFormModalAttrs {
  /** The discussion whose language is changed, or none to choose one for the composer. */
  discussion?: Discussion;
  selected?: Language;
  /** Choosing a language submits it, rather than a button. */
  hideSubmitButton?: boolean;
  onsubmit?: (language: Language) => void;
}

export default class LanguageDiscussionModal extends FormModal<LanguageDiscussionModalAttrs> {
  languages!: Language[];
  current: Language | undefined;
  selected: Language | undefined;

  oninit(vnode: Mithril.Vnode<LanguageDiscussionModalAttrs, this>) {
    super.oninit(vnode);

    this.languages = app.store.all<Language>('discussion-languages').filter((language) => language.code() !== ANY);

    this.current = this.attrs.selected || this.attrs.discussion?.language?.() || undefined;
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
    return (
      <div className="Modal-body">
        <div className="Form-group">
          {this.languages.map((language) => (
            <Button
              onclick={this.select.bind(this, language)}
              className={`Button Button--block ${this.selected === language ? 'active' : ''}`}
              // Its content is a component, which the button can't read a name from.
              aria-label={language.name()}
            >
              <LanguageDisplay language={language} uppercase={true} />
            </Button>
          ))}
        </div>

        {!this.attrs.hideSubmitButton && (
          <div className="App-primaryControl">
            <Button
              type="submit"
              className="Button Button--primary"
              disabled={!this.selected || this.selected === this.current}
              loading={this.loading}
              icon="fas fa-check"
            >
              {app.translator.trans('fof-discussion-language.forum.change_language.submit_button')}
            </Button>
          </div>
        )}
      </div>
    );
  }

  select(language: Language) {
    this.selected = language;

    if (this.attrs.hideSubmitButton) return this.onsubmit();

    m.redraw();
  }

  onsubmit(e?: SubmitEvent) {
    e?.preventDefault();

    const { discussion, onsubmit } = this.attrs;
    const language = this.selected;

    if (!language) return;

    if (!discussion) {
      // Once closed: what follows may open a modal, as flarum/tags does to
      // choose tags, and one opened while this closes can't itself be closed.
      this.attrs.animateHide(() => onsubmit?.(language));

      return;
    }

    this.loading = true;

    discussion
      .save({ relationships: { language } }, { errorHandler: this.onerror.bind(this) })
      .then(() => {
        (app.current.get('stream') as PostStreamState | undefined)?.update();

        this.hide();
      })
      .catch(() => this.loaded());
  }
}
