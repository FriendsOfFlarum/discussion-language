import app from 'flarum/forum/app';
import { ANY } from '../../common/utils/any';

/**
 * The language the discussion list was asked for: its `filter[language]`, or
 * 1.x's `?language=`, which the forum's middleware still accepts.
 */
export function selectedLanguage(): string | undefined {
  const filter = m.route.param('filter') as Record<string, unknown> | undefined;
  const language = filter?.language ?? m.route.param('language');

  return typeof language === 'string' && language ? language : undefined;
}

/**
 * The language to filter a discussion list by, if any: the one chosen, or the
 * reader's own when the forum doesn't offer every language at once.
 */
export function languageToFilterBy(selected: string | undefined, showAnyLanguage: boolean, locale: string): string | undefined {
  if (!showAnyLanguage) return selected && selected !== ANY ? selected : locale;

  return selected === ANY ? undefined : selected;
}

/**
 * The language the dropdown shows as chosen when the list was asked for none.
 */
export function defaultLanguage(): string {
  return app.forum.attribute<boolean>('fof-discussion-language.showAnyLangOpt') ? ANY : app.translator.getLocale();
}
