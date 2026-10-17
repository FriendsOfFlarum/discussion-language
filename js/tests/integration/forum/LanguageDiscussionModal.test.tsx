import mq from 'mithril-query';
import { jest } from '@jest/globals';
import ModalManagerState from 'flarum/common/states/ModalManagerState';
import LanguageDiscussionModal from '../../../src/forum/components/LanguageDiscussionModal';
import { bootForum, language } from '../../helpers/forum';

beforeAll(() => bootForum());

const modal = (attrs: Record<string, unknown>) =>
  mq(LanguageDiscussionModal, { state: new ModalManagerState(), animateShow: () => {}, animateHide: (closed?: () => void) => closed?.(), ...attrs });

describe('choosing the language a discussion is in', () => {
  it("offers the forum's languages, but not every language at once", () => {
    const out = modal({});

    expect(out).toContainRaw('ENGLISH');
    expect(out).toContainRaw('GERMAN');
    expect(out).not.toContainRaw('ANY LANGUAGE');
  });

  it('names each language for assistive technology', () => {
    expect(modal({})).toHaveElement('.Button--block[aria-label=German]');
  });

  it('marks the language chosen so far', () => {
    expect(modal({ selected: language('de') })).toHaveElement('.Button--block.active');
  });

  it('is done as soon as a language is chosen in the composer', () => {
    const onsubmit = jest.fn();
    const out = modal({ hideSubmitButton: true, onsubmit });

    expect(out).not.toHaveElement('button[type=submit]');

    out.click('.Button--block:nth-child(2)');

    expect(onsubmit).toHaveBeenCalledWith(language('de'));
  });

  it('goes on only once it has closed, as what follows may open a modal', () => {
    const order: string[] = [];
    const out = modal({
      hideSubmitButton: true,
      animateHide: (closed?: () => void) => {
        order.push('closing');
        closed?.();
      },
      onsubmit: () => order.push('submitted'),
    });

    out.click('.Button--block:nth-child(2)');

    expect(order).toEqual(['closing', 'submitted']);
  });
});
