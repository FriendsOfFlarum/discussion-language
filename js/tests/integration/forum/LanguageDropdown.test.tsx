import mq from 'mithril-query';
import { jest } from '@jest/globals';
import LanguageDropdown from '../../../src/forum/components/LanguageDropdown';
import { bootForum } from '../../helpers/forum';

beforeAll(() => bootForum());

describe('the language dropdown', () => {
  it("offers each of the forum's languages", () => {
    const out = mq(LanguageDropdown, { selected: 'any' });

    expect(out).toHaveElement('button[data-language=any]');
    expect(out).toHaveElement('button[data-language=en]');
    expect(out).toHaveElement('button[data-language=de]');
  });

  it('shows the chosen language on its button', () => {
    const out = mq(LanguageDropdown, { selected: 'de' });

    expect(out.first('.Dropdown-toggle .Button-labelText')).toBeDefined();
    expect(out.find('.Dropdown-toggle .Button-labelText')[0].textContent).toContain('German');
  });

  it('names each language for assistive technology', () => {
    expect(mq(LanguageDropdown, { selected: 'any' })).toHaveElement('button[data-language=de][aria-label=German]');
  });

  it('marks the chosen language for assistive technology', () => {
    const out = mq(LanguageDropdown, { selected: 'de' });

    expect(out).toHaveElement('li[aria-current=true] button[data-language=de]');
    expect(out).not.toHaveElement('li[aria-current=true] button[data-language=en]');
  });

  it('names its toggle, and is no form button', () => {
    const toggle = mq(LanguageDropdown, { selected: 'de' }).first('.Dropdown-toggle');

    expect(toggle.getAttribute('type')).toBe('button');
    expect(toggle.getAttribute('aria-label')).toBe('Choose a discussion language');
  });

  it('says which language was chosen', () => {
    const onselect = jest.fn();
    const out = mq(LanguageDropdown, { selected: 'any', onselect });

    out.click('button[data-language=de]');

    expect(onselect).toHaveBeenCalledWith('de');
  });
});
