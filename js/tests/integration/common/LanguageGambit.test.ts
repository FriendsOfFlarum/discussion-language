import app from 'flarum/forum/app';
import { bootForum } from '../../helpers/forum';

beforeAll(() => bootForum());

describe('the language gambit', () => {
  it('turns `language:` in a search into the language filter', () => {
    expect(app.search.gambits.apply('discussions', { q: 'release language:de' })).toEqual({ q: 'release', language: 'de' });
  });

  it('takes several languages', () => {
    expect(app.search.gambits.apply('discussions', { q: 'language:en,de' })).toEqual({ q: '', language: 'en,de' });
  });

  it('takes a negated language', () => {
    expect(app.search.gambits.apply('discussions', { q: '-language:en' })).toEqual({ q: '', '-language': 'en' });
  });

  it('writes the language filter back into the search', () => {
    expect(app.search.gambits.from('discussions', '', { language: 'de' }).trim()).toBe('language:de');
  });

  it('is suggested with a hint', () => {
    const gambit = app.search.gambits.for('discussions').find((gambit) => gambit.filterKey() === 'language')!;

    expect(gambit.suggestion()).toMatchObject({ key: 'language', hint: 'language code or comma-separated list of language codes' });
  });

  it("suggests the forum's languages, without any language", () => {
    const gambit = app.search.gambits.for('discussions').find((gambit) => gambit.filterKey() === 'language')!;

    expect(gambit.values!()).toEqual([
      { value: 'en', label: 'English' },
      { value: 'de', label: 'German' },
    ]);
  });

  it('takes `language:` in English when the keyword is translated', () => {
    const key = 'fof-discussion-language.lib.gambits.language.key';
    const english = app.translator.translations[key];

    app.translator.addTranslations({ [key]: 'sprache' });

    try {
      expect(app.search.gambits.apply('discussions', { q: 'sprache:de' })).toEqual({ q: '', language: 'de' });
      expect(app.search.gambits.apply('discussions', { q: 'language:de' })).toEqual({ q: '', language: 'de' });
    } finally {
      app.translator.addTranslations({ [key]: english });
    }
  });
});
