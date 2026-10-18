import type Language from '../../common/models/Language';
import type { LocalisedLastDiscussions } from '../../common/models/LocalisedLastDiscussions';
/**
 * A tag's last discussion in the reader's language: the first of their
 * preferences that is set, if the forum has it, and if the reader can see the
 * discussion, as its title is empty otherwise.
 */
export declare function localisedLastDiscussion(discussions: LocalisedLastDiscussions | null | undefined, languages: Language[], preferredCodes: (string | null | undefined)[]): LocalisedLastDiscussions[string] | undefined;
