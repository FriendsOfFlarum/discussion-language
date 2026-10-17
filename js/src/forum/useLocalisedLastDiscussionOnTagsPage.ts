import app from 'flarum/forum/app';
import { override } from 'flarum/common/extend';
import Discussion from 'flarum/common/models/Discussion';
import Tag from 'ext:flarum/tags/common/models/Tag';

import { ANY } from '../common/utils/any';
import { localisedLastDiscussion } from './utils/localisedLastDiscussion';

export default function useLocalisedLastDiscussionOnTagsPage() {
  override(Tag.prototype, 'lastPostedDiscussion', function (this: Tag, original: () => Discussion | false | null | undefined) {
    if (!app.forum.attribute<boolean>('fof-discussion-language.useLocaleForTagsPageLastDiscussion')) {
      return original();
    }

    const languages = (app.forum.discussionLanguages() || []).filter((language) => language.code() !== ANY);

    // The reader's chosen locale, or else the forum's, or else its first language.
    const discussion = localisedLastDiscussion(this.localisedLastDiscussion(), languages, [
      app.session.user?.preferences()?.locale as string | undefined,
      app.translator.getLocale(),
      languages[0]?.code(),
    ]);

    if (!discussion) return original();

    // Enough of the discussion for the tags page to show and link to it.
    return new Discussion({
      attributes: {
        title: discussion.title,
        slug: String(discussion.id),
        lastPostedAt: new Date(discussion.at * 1000).toISOString(),
      },
    });
  });
}
