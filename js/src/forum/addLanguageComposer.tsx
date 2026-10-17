import app from 'flarum/forum/app';
import { extend, override } from 'flarum/common/extend';
import Button from 'flarum/common/components/Button';
import classList from 'flarum/common/utils/classList';
import type DiscussionComposer from 'flarum/forum/components/DiscussionComposer';
import type ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';

import LanguageDisplay from './components/LanguageDisplay';
import type Language from '../common/models/Language';
import chooseLanguage from './utils/chooseLanguage';
import { composerFields, isPrivateComposer, startingLanguage } from './utils/composerLanguage';
import { selectedLanguage } from './utils/languageFilter';

export default () => {
  // Here rather than where the composer is opened: loading the composer's chunk
  // clears its fields, and it can be opened from anywhere.
  extend('flarum/forum/components/DiscussionComposer', 'oninit', function (this: DiscussionComposer) {
    if (isPrivateComposer(this) || composerFields(this).language) return;

    const code = startingLanguage(
      selectedLanguage(),
      !!app.forum.attribute<boolean>('fof-discussion-language.composerLocaleDefault'),
      app.translator.getLocale()
    );

    composerFields(this).language = (code && app.store.getBy<Language>('discussion-languages', 'code', code)) || null;
  });

  extend('flarum/forum/components/DiscussionComposer', 'headerItems', function (this: DiscussionComposer, items: ItemList<Mithril.Children>) {
    if (isPrivateComposer(this)) return;

    const language = composerFields(this).language;

    items.add(
      'language',
      <Button
        className="DiscussionComposer-changeTags Button Button--ua-reset"
        onclick={() => chooseLanguage(this, true)}
        // The language is shown by a component, which the button can't read a name from.
        aria-label={language?.name()}
      >
        <span className={classList('LanguageLabel', !language && 'none')}>
          {language ? (
            <LanguageDisplay language={language} uppercase={true} />
          ) : (
            app.translator.trans('fof-discussion-language.forum.composer_discussion.choose_language_link')
          )}
        </span>
      </Button>,
      20
    );
  });

  override('flarum/forum/components/DiscussionComposer', 'onsubmit', function (this: DiscussionComposer, original: () => void) {
    if (isPrivateComposer(this) || composerFields(this).language) return original();

    chooseLanguage(this, true, original);
  });

  extend('flarum/forum/components/DiscussionComposer', 'data', function (this: DiscussionComposer, data: Record<string, unknown>) {
    const language = composerFields(this).language;

    if (isPrivateComposer(this) || !language) return;

    data.relationships = { ...(data.relationships as Record<string, unknown> | undefined), language };
  });
};
