import mq from 'mithril-query';
import LanguageDisplay from '../../../src/forum/components/LanguageDisplay';
import { bootForum, language } from '../../helpers/forum';

beforeAll(() => bootForum());

describe('a language', () => {
  it('is shown by its flag and name', () => {
    const out = mq(LanguageDisplay, { language: language('de') });

    expect(out).toContainRaw('German');
    expect(out).toHaveElementAttr('img.emoji', 'alt', 'DE');
  });

  it('can be shown in capitals', () => {
    expect(mq(LanguageDisplay, { language: language('de'), uppercase: true })).toContainRaw('GERMAN');
  });

  it('without a flag is shown with a globe', () => {
    const out = mq(LanguageDisplay, { language: language('any') });

    expect(out).toContainRaw('Any Language');
    expect(out).toHaveElement('i.fa-globe');
  });
});
