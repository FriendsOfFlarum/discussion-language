import { startingLanguage } from '../../../src/forum/utils/composerLanguage';

describe('the language a new discussion starts in', () => {
  it('is the one the discussion list shows', () => {
    expect(startingLanguage('de', false, 'en')).toBe('de');
    expect(startingLanguage('de', true, 'en')).toBe('de');
  });

  it("is the reader's own when the forum is set to start there", () => {
    expect(startingLanguage(undefined, true, 'en')).toBe('en');
    expect(startingLanguage('any', true, 'en')).toBe('en');
  });

  it('is left to the author otherwise', () => {
    expect(startingLanguage(undefined, false, 'en')).toBeUndefined();
    expect(startingLanguage('any', false, 'en')).toBeUndefined();
  });
});
