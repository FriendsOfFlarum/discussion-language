import app from 'flarum/forum/app';
import Tag from 'ext:flarum/tags/common/models/Tag';
import useLocalisedLastDiscussionOnTagsPage from '../../../src/forum/useLocalisedLastDiscussionOnTagsPage';
import { bootForum } from '../../helpers/forum';

beforeAll(() => {
  bootForum({ 'fof-discussion-language.useLocaleForTagsPageLastDiscussion': true });
  useLocalisedLastDiscussionOnTagsPage();
});

function tag(localisedLastDiscussion: Record<string, unknown>): Tag {
  app.store.pushPayload({
    data: [
      { type: 'discussions', id: '10', attributes: { title: 'Latest overall', slug: '10-latest-overall' } },
      {
        type: 'tags',
        id: '1',
        attributes: { name: 'General', slug: 'general', localisedLastDiscussion },
        relationships: { lastPostedDiscussion: { data: { type: 'discussions', id: '10' } } },
      },
    ],
  });

  return app.store.getById<Tag>('tags', '1')!;
}

describe("a tag's last discussion on the tags page", () => {
  it("is the last one in the reader's language", () => {
    const discussion = tag({ '1': { id: 5, at: 1700000000, user_id: 1, title: 'Latest in English' } }).lastPostedDiscussion();

    expect(discussion && discussion.title()).toBe('Latest in English');
    expect(discussion && discussion.slug()).toBe('5');
  });

  it("is the tag's own when there is none in the reader's language", () => {
    const discussion = tag({ '2': { id: 6, at: 1700000000, user_id: 1, title: 'Latest in German' } }).lastPostedDiscussion();

    expect(discussion && discussion.title()).toBe('Latest overall');
  });

  it("is the tag's own when the reader can't see the one in their language", () => {
    // The API leaves the title out of a discussion the reader can't see.
    const discussion = tag({ '1': { id: 7, at: 1700000000, user_id: 1, title: '' } }).lastPostedDiscussion();

    expect(discussion && discussion.title()).toBe('Latest overall');
  });

  it("is the tag's own when the forum isn't set to show it by language", () => {
    app.forum.pushAttributes({ 'fof-discussion-language.useLocaleForTagsPageLastDiscussion': false });

    const discussion = tag({ '1': { id: 5, at: 1700000000, user_id: 1, title: 'Latest in English' } }).lastPostedDiscussion();

    expect(discussion && discussion.title()).toBe('Latest overall');

    app.forum.pushAttributes({ 'fof-discussion-language.useLocaleForTagsPageLastDiscussion': true });
  });
});
