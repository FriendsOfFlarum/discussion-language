import { extend, override } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import IndexPage from 'flarum/forum/components/IndexPage';
import DiscussionComposer from 'flarum/forum/components/DiscussionComposer';
import type ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';

import LanguageDiscussionModal from './components/LanguageDiscussionModal';
import LanguageDisplay from './components/LanguageDisplay';
import type Language from '../common/models/Language';

const sort = (a: Language, b: Language) => (a.code()!.toLowerCase() > b.code()!.toLowerCase() ? 1 : -1);

export default () => {
  extend(IndexPage.prototype, 'newDiscussionAction', function (promise: Promise<unknown>) {
    const dislang = app.search.params().language;

    if (dislang) {
      promise.then((composer) => ((composer as any).fields.language = app.store.getBy('discussion-languages', 'code', dislang)));
    } else {
      const localeComposer = app.forum.attribute('fof-discussion-language.composerLocaleDefault');
      (app.composer as any).fields.language = localeComposer ? app.store.getBy('discussion-languages', 'code', app.translator.getLocale()) : '';
    }
  });

  DiscussionComposer.prototype.chooseLanguage = function (this: DiscussionComposer, hide: boolean, callback: (() => void) | null) {
    app.modal.show(LanguageDiscussionModal, {
      selected: (this as any).composer.fields.language,
      hideSubmitButton: hide,
      onsubmit: (language: Language) => {
        (this as any).composer.fields.language = language;
        (this as any).$('textarea').focus();

        if (callback) callback();
      },
    });
  };

  extend(DiscussionComposer.prototype, 'headerItems', function (this: DiscussionComposer, items: ItemList<Mithril.Children>) {
    // Don't add lang item to byobu composers
    if ((this as any)._isByobuComposer) return;

    items.add(
      'language',
      <a className="DiscussionComposer-changeTags" onclick={this.chooseLanguage.bind(this, true, null)}>
        <span className={`LanguageLabel ${(this as any).composer.fields.language ? '' : 'none'}`}>
          {(this as any).composer.fields.language
            ? LanguageDisplay.component({ language: (this as any).composer.fields.language, uppercase: true })
            : app.translator.trans('fof-discussion-language.forum.composer_discussion.choose_language_link')}
        </span>
      </a>,
      20
    );
  });

  override(DiscussionComposer.prototype, 'onsubmit', function (this: DiscussionComposer, original: () => void) {
    // Don't add lang stuff to byobu composers
    if ((this as any)._isByobuComposer) return original();

    if (!(this as any).composer.fields.language) return this.chooseLanguage(true, original);

    original();
  });

  extend(DiscussionComposer.prototype, 'data', function (this: DiscussionComposer, data: Record<string, unknown>) {
    // Don't add lang stuff to byobu composers
    if ((this as any)._isByobuComposer) return;

    data.relationships = (data.relationships as Record<string, unknown>) || {};

    (data.relationships as Record<string, unknown>).language =
      (this as any).composer.fields.language || app.store.all<Language>('discussion-languages').sort(sort)[0];
  });
};
