export declare const load: () => Promise<Record<string, Record<string, unknown>> | undefined>;
/**
 * The countries to choose from, by code, in the order of their names.
 */
export declare function countryOptions(entries: Record<string, Record<string, unknown>>, useNativeName: boolean): Record<string, string>;
export default function getCountries(useNativeName: boolean): Record<string, string>;
export declare const getEmoji: (code: string) => unknown;
