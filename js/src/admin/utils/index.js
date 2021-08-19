import getCountry, * as countries from './countries';
import locales, { getName } from './locales';
import { utils as commonUtils } from '../../common/utils';

export const utils = {
  getCountry,
  countries,
  locales,
  getName,
  ...commonUtils,
};
