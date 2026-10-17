import { localeOptions, type ISO6392Entry } from '../../../src/admin/utils/locales';
import { countryOptions } from '../../../src/admin/utils/countries';

const entry = (codes: Partial<ISO6392Entry>, name: string, native = ''): ISO6392Entry => ({
  '639-1': '',
  '639-2': '',
  '639-3': '',
  'Language name(s)': name,
  'Native name(s)': native,
  ...codes,
});

describe('the languages to choose from', () => {
  const entries = [
    entry({ '639-1': 'de', '639-2': 'ger' }, 'German', 'Deutsch'),
    entry({ '639-3': 'vec' }, 'Venetian', 'vèneto'),
    entry({ '639-2': 'ast', '639-3': 'ast' }, 'Asturian'),
    entry({ '639-3': 'lij' }, 'Ligurian'),
    entry({}, 'Without a code'),
  ];

  it('are each keyed by their shortest code, so none share one', () => {
    expect(localeOptions(entries, false)).toEqual({ ast: 'Asturian', de: 'German', lij: 'Ligurian', vec: 'Venetian' });
  });

  it('are in the order of their names', () => {
    expect(Object.values(localeOptions(entries, false))).toEqual(['Asturian', 'German', 'Ligurian', 'Venetian']);
  });

  it('use native names where there are any', () => {
    expect(Object.values(localeOptions(entries, true))).toEqual(['Asturian', 'Deutsch', 'Ligurian', 'vèneto']);
  });
});

describe('the countries to choose from', () => {
  const entries = {
    AD: { name: 'Andorra', native: 'Andorra' },
    AE: { name: 'United Arab Emirates', native: 'دولة الإمارات العربية المتحدة' },
    AF: { name: 'Afghanistan', native: 'افغانستان' },
  };

  it('are in the order of their names, not their codes', () => {
    expect(Object.keys(countryOptions(entries, false))).toEqual(['AF', 'AD', 'AE']);
  });
});
