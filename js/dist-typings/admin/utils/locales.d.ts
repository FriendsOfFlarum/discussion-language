export interface ISO6392Entry {
    '639-1': string;
    '639-2': string;
    '639-3': string;
    'Language name(s)': string;
    'Native name(s)': string;
}
export declare const load: () => Promise<ISO6392Entry[]>;
/**
 * The code a language is stored with: its shortest. Some are only in ISO 639-3,
 * such as Venetian (vec), so without it they all shared the code ''.
 */
export declare const codeOf: (data: ISO6392Entry) => string;
export declare const getNameColumn: (data: ISO6392Entry | undefined, native: boolean) => string | undefined;
/**
 * The languages to choose from, by code, in the order of their names.
 */
export declare function localeOptions(entries: ISO6392Entry[], native: boolean): Record<string, string | undefined>;
export default function getLocales(native: boolean): Record<string, string | undefined>;
export declare const getName: (code: string, native: boolean) => string | undefined;
