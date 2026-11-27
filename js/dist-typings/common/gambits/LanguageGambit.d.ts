import { KeyValueGambit, type GambitValueOption } from 'flarum/common/query/IGambit';
/**
 * `language:de` or `language:en,de` in a search, which the API takes as the
 * `language` filter.
 */
export default class LanguageGambit extends KeyValueGambit {
    predicates: boolean;
    key(): string;
    canonicalKey(): string;
    hint(): string;
    filterKey(): string;
    values(): GambitValueOption[];
}
