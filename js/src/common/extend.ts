import Extend from 'flarum/common/extenders';
import Language from '../common/models/Language';
import Discussion from 'flarum/common/models/Discussion';
import Forum from 'flarum/common/models/Forum';
import Tag from 'flarum/tags/common/models/Tag';

export default [
  new Extend.Store() //
    .add('discussion-languages', Language),

  new Extend.Model(Discussion) //
    .hasOne('language')
    .attribute<boolean>('canChangeLanguage'),

  new Extend.Model(Forum) //
    .hasMany<Language>('discussionLanguages'),

  new Extend.Model(Tag) //
    .attribute<string>('subscriptionLanguage')
    .attribute<string>('localisedLastDiscussion'),
];
