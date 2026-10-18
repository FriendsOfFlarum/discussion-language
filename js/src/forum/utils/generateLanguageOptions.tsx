import app from 'flarum/forum/app';
import type Mithril from 'mithril';

import type Language from '../../common/models/Language';
import LanguageDisplay from '../components/LanguageDisplay';

/**
 * The forum's languages, by code, with `any` among them when it is offered.
 */
export default function generateLanguageOptions(): Record<string, Mithril.Children> {
  const options: Record<string, Mithril.Children> = {};

  app.store.all<Language>('discussion-languages').forEach((language) => {
    options[language.code()] = <LanguageDisplay language={language} />;
  });

  return options;
}
