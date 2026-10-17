import app from 'flarum/forum/app';
import type DiscussionComposer from 'flarum/forum/components/DiscussionComposer';
import type Language from '../../common/models/Language';
import { composerFields } from './composerLanguage';

/**
 * Asks which language the discussion being written is in, then goes on with
 * what the author was doing, if anything.
 */
export default function chooseLanguage(composer: DiscussionComposer, hideSubmitButton: boolean, then?: () => void): void {
  app.modal.show(() => import('../components/LanguageDiscussionModal'), {
    selected: composerFields(composer).language ?? undefined,
    hideSubmitButton,
    onsubmit: (language: Language) => {
      composerFields(composer).language = language;
      app.composer.editor?.focus();

      then?.();
    },
  });
}
