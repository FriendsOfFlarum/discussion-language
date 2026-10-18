import type DiscussionComposer from 'flarum/forum/components/DiscussionComposer';
import type Language from '../../common/models/Language';
type LanguageFields = {
    language?: Language | null;
};
/**
 * The language a discussion being written is in, kept with the composer's
 * other fields so that it outlives the composer being minimised.
 */
export declare function composerFields(composer: DiscussionComposer): LanguageFields;
/**
 * fof/byobu's private discussions have no language.
 */
export declare function isPrivateComposer(composer: DiscussionComposer): boolean;
/**
 * The code of the language a new discussion starts in: the one the discussion
 * list shows, or the reader's own when the forum is set to start there.
 */
export declare function startingLanguage(listLanguage: string | undefined, startInLocale: boolean, locale: string): string | undefined;
export {};
