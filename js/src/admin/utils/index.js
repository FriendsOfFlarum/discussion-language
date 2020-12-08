import countries, { getCountryEmoji } from './countries';
import locales, { getName } from './locales';
import { utils as commonUtils } from '../../common/utils';

export const utils = {
    countries,
    getCountryEmoji,
    locales,
    getName,
    ...commonUtils,
};
