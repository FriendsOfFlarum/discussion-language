import app from 'flarum/common/app';
import { KeyValueGambit } from 'flarum/common/query/IGambit';

export default class LanguageGambit extends KeyValueGambit {
  key() {
    return app.translator.trans('fof-discussion-language.lib.gambits.language.key', {}, true);
  }

  hint() {
    return app.translator.trans('fof-discussion-language.lib.gambits.language.hint', {}, true);
  }

  filterKey() {
    return 'language';
  }
}
