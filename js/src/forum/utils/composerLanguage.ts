import type DiscussionComposer from 'flarum/forum/components/DiscussionComposer';
import type Language from '../../common/models/Language';
import { ANY } from '../../common/utils/any';

type LanguageFields = { language?: Language | null };

/**
 * The language a discussion being written is in, kept with the composer's
 * other fields so that it outlives the composer being minimised.
 */
export function composerFields(composer: DiscussionComposer): LanguageFields {
  return composer.attrs.composer.fields as unknown as LanguageFields;
}

/**
 * fof/byobu's private discussions have no language.
 */
export function isPrivateComposer(composer: DiscussionComposer): boolean {
  return !!(composer as unknown as { _isByobuComposer?: boolean })._isByobuComposer;
}

/**
 * The code of the language a new discussion starts in: the one the discussion
 * list shows, or the reader's own when the forum is set to start there.
 */
export function startingLanguage(listLanguage: string | undefined, startInLocale: boolean, locale: string): string | undefined {
  if (listLanguage && listLanguage !== ANY) return listLanguage;

  return startInLocale ? locale : undefined;
}
