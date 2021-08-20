import app from 'flarum/forum/app';

import Tag from 'flarum/tags/common/models/Tag';

import Discussion from 'flarum/common/models/Discussion';
import Model from 'flarum/common/Model';

export default function useLocalisedLastDiscussionOnTagsPage() {
  if (!('flarum-tags' in flarum.extensions)) return;

  Tag.prototype.lastPostedDiscussion = function (this: Tag) {
    const original = Model.hasOne('lastPostedDiscussion').bind(this);

    if (!app.forum.attribute('fof-discussion-language.useLocaleForTagsPageLastDiscussion')) {
      return original();
    }

    const data = this.localisedLastDiscussion();

    /**
     * Priorities:
     *
     * 1. User's selected forum locale
     * 2. Detected locale
     * 3. First disucssion language available
     */
    const desiredLanguageCode =
      app.session.user?.preferences?.()?.locale || (app.translator as any).formatter.locale || (app.forum as any).discussionLanguages()[0].code();

    const desiredLanguageId = (app.forum as any)
      .discussionLanguages()
      .find((lang: any) => lang.code() === desiredLanguageCode)
      ?.id();

    // If forum locale not available as a discussion lang, default to any language
    if (!desiredLanguageId) {
      return original();
    }

    const discussion = data[desiredLanguageId];

    console.log(data, desiredLanguageCode, desiredLanguageId, discussion);

    // fall back to original attribute
    if (!discussion) return original();

    return new Discussion({
      attributes: {
        title: discussion.title,
        slug: discussion.id, // navigating using only ID works fine
        lastPostedAt: new Date(discussion.at * 1000).toISOString(),
      },
    });
  };
}
