import type DiscussionComposer from 'flarum/forum/components/DiscussionComposer';
/**
 * Asks which language the discussion being written is in, then goes on with
 * what the author was doing, if anything.
 */
export default function chooseLanguage(composer: DiscussionComposer, hideSubmitButton: boolean, then?: () => void): void;
