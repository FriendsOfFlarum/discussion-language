/**
 * A tag's last discussion in each language, by the language's ID: an empty
 * string for discussions without a language.
 */
export type LocalisedLastDiscussions = Record<string, {
    id: number;
    at: number;
    user_id: number | null;
    title: string;
}>;
