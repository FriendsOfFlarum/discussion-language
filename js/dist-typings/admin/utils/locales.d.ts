interface ISO6392Entry {
    '639-1': string;
    '639-2': string;
    'Language name(s)': string;
    'Native name(s)': string;
}
export declare const load: () => Promise<ISO6392Entry[]>;
export declare const getNameColumn: (data: ISO6392Entry | undefined, native: boolean) => string | undefined;
declare const _default: (native: boolean) => Record<string, string | undefined>;
export default _default;
export declare const getName: (code: string, native: boolean) => string | undefined;
