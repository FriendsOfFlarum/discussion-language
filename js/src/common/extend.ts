import Extend from 'flarum/common/extenders';
import Discussion from 'flarum/common/models/Discussion';
import Forum from 'flarum/common/models/Forum';
import Tag from 'ext:flarum/tags/common/models/Tag';
import Language from './models/Language';
import LanguageGambit from './gambits/LanguageGambit';
import type { LocalisedLastDiscussions } from './models/LocalisedLastDiscussions';

export default [
  new Extend.Store() //
    .add('discussion-languages', Language),

  new Extend.Model(Discussion) //
    .hasOne<Language>('language')
    .attribute<boolean>('canChangeLanguage'),

  new Extend.Model(Forum) //
    .hasMany<Language>('discussionLanguages'),

  new Extend.Model(Tag) //
    .attribute<string | null>('subscriptionLanguage')
    .attribute<LocalisedLastDiscussions>('localisedLastDiscussion'),

  new Extend.Search() //
    .gambit('discussions', LanguageGambit),
] satisfies object[];
