/**
 * The language the discussion list was asked for: its `filter[language]`, or
 * 1.x's `?language=`, which the forum's middleware still accepts.
 */
export declare function selectedLanguage(): string | undefined;
/**
 * The language to filter a discussion list by, if any: the one chosen, or the
 * reader's own when the forum doesn't offer every language at once.
 */
export declare function languageToFilterBy(selected: string | undefined, showAnyLanguage: boolean, locale: string): string | undefined;
/**
 * The language the dropdown shows as chosen when the list was asked for none.
 */
export declare function defaultLanguage(): string;
