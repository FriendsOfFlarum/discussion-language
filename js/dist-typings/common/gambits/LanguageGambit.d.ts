import { KeyValueGambit } from 'flarum/common/query/IGambit';
/**
 * `language:de` or `language:en,de` in a search, which the API takes as the
 * `language` filter.
 */
export default class LanguageGambit extends KeyValueGambit {
    key(): string;
    hint(): string;
    filterKey(): string;
}
