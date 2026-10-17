import Tag from 'ext:flarum/tags/common/models/Tag';
import Discussion from 'flarum/common/models/Discussion';
import Forum from 'flarum/common/models/Forum';
import type Language from '../common/models/Language';
import type { LocalisedLastDiscussions } from '../common/models/LocalisedLastDiscussions';

declare module 'ext:flarum/tags/common/models/Tag' {
  export default interface Tag {
    localisedLastDiscussion(): LocalisedLastDiscussions;
    subscriptionLanguage(): string | null;
  }
}

declare module 'flarum/common/models/Discussion' {
  export default interface Discussion {
    language(): Language | false | null;
    canChangeLanguage(): boolean | undefined;
  }
}

declare module 'flarum/common/models/Forum' {
  export default interface Forum {
    discussionLanguages(): Language[] | false;
  }
}
