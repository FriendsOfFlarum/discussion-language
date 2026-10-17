import bootstrapForum from '@flarum/jest-config/src/bootstrap/forum';
import app from 'flarum/forum/app';
import flatten from 'flat';
import jsYaml from 'js-yaml';
import fs from 'fs';
import path from 'path';
import tagsExtend from 'ext:flarum/tags/common/extend';
import extend from '../../src/forum/extend';
import type Language from '../../src/common/models/Language';

export const languages = [
  { id: 'any', code: 'any', name: 'Any Language', emoji: null, country: null },
  { id: '1', code: 'en', name: 'English', emoji: '🇬🇧', country: 'GB' },
  { id: '2', code: 'de', name: 'German', emoji: '🇩🇪', country: 'DE' },
];

/**
 * The forum app with this extension's extenders, its translations, and the
 * forum's languages, as the API sends them: the "any" choice first.
 */
export function bootForum(forumAttributes: Record<string, unknown> = {}): void {
  bootstrapForum();

  // flarum/tags is a dependency, so it boots first, as it does on a forum.
  app.bootExtensions({ 'flarum-tags': { extend: tagsExtend }, 'fof-discussion-language': { extend } });
  app.boot();

  // Jest runs from the extension's js directory.
  const locale = path.resolve(process.cwd(), '../resources/locale/en.yml');
  app.translator.addTranslations(flatten(jsYaml.load(fs.readFileSync(locale, 'utf8')) as object));

  app.store.pushPayload({
    data: languages.map(({ id, ...attributes }) => ({ type: 'discussion-languages', id, attributes })),
  });

  app.forum.pushData({
    attributes: { 'fof-discussion-language.showAnyLangOpt': true, ...forumAttributes },
    relationships: { discussionLanguages: { data: languages.map(({ id }) => ({ type: 'discussion-languages', id })) } },
  });
}

export function language(code: string): Language {
  return app.store.getBy<Language>('discussion-languages', 'code', code)!;
}
