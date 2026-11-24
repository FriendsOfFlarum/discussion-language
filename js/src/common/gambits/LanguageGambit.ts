import app from 'flarum/common/app';
import { KeyValueGambit } from 'flarum/common/query/IGambit';
import type { GambitValueOption } from 'flarum/common/query/IGambit';
import type Language from '../models/Language';
import { ANY } from '../utils/any';

/**
 * `language:de` or `language:en,de` in a search, which the API takes as the
 * `language` filter.
 */
export default class LanguageGambit extends KeyValueGambit {
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
    const languages: Language[] = (app.forum?.discussionLanguages() || []) as Language[];

    return languages
      .filter((language) => language.code() !== ANY)
      .map((language) => ({ value: language.code(), label: language.name() || language.code() }));
  }
}
