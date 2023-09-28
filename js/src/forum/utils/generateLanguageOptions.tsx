import Language from '../../common/models/Language';
import LanguageDisplay from '../components/LanguageDisplay';
import app from 'flarum/forum/app';
import type Mithril from 'mithril';

export default function generateLanguageOptions(extraOptions?: Record<string, Mithril.Children>): Record<string, Mithril.Children> {
  const options: Record<string, Mithril.Children> = {};
  const languages = app.store.all<Language>('discussion-languages');

  // Populate the options with the languages
  languages.forEach((lang) => {
    options[lang.code()] = <LanguageDisplay language={lang} />;
  });

  // Merge extra options, if provided
  return { ...options, ...extraOptions };
}
