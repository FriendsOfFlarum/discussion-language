/// <reference types="mithril" />
import getCountry, * as countries from './countries';
export declare const utils: {
    flag: (language: string | import("../../common/models/Language").default) => import("mithril").Children;
    getCountry: typeof getCountry;
    countries: typeof countries;
    locales: (native: boolean) => Record<string, string | undefined>;
    getName: (code: string, native: boolean) => string | undefined;
};
