import { languageToFilterBy } from '../../../src/forum/utils/languageFilter';

describe('the language a discussion list is filtered by', () => {
  it('is the one chosen', () => {
    expect(languageToFilterBy('de', true, 'en')).toBe('de');
    expect(languageToFilterBy('de', false, 'en')).toBe('de');
  });

  it('is none when every language is chosen', () => {
    expect(languageToFilterBy('any', true, 'en')).toBeUndefined();
  });

  it("is the reader's own when every language is asked for but the forum doesn't offer it", () => {
    expect(languageToFilterBy('any', false, 'en')).toBe('en');
  });

  it('is none when nothing is chosen and the forum offers every language', () => {
    expect(languageToFilterBy(undefined, true, 'en')).toBeUndefined();
  });

  it("is the reader's own when nothing is chosen and the forum doesn't offer every language", () => {
    expect(languageToFilterBy(undefined, false, 'en')).toBe('en');
  });
});
