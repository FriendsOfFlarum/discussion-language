import app from 'flarum/common/app';
import { KeyValueGambit, type GambitValueOption } from 'flarum/common/query/IGambit';
import { ANY } from '../utils/any';

/**
 * `language:de` or `language:en,de` in a search, which the API takes as the
 * `language` filter.
 */
export default class LanguageGambit extends KeyValueGambit {
  // The filter takes several codes: this keeps the others when a suggestion is picked.
  predicates = true;

  key() {
    return app.translator.trans('fof-discussion-language.lib.gambits.language.key', {}, true);
  }

  canonicalKey() {
    return 'language';
  }

  hint() {
    return app.translator.trans('fof-discussion-language.lib.gambits.language.hint', {}, true);
  }

  filterKey() {
    return 'language';
  }

  values(): GambitValueOption[] {
    return (app.forum?.discussionLanguages() || [])
      .filter((language) => language.code() !== ANY)
      .map((language) => ({ value: language.code(), label: language.name() || language.code() }));
  }
}
