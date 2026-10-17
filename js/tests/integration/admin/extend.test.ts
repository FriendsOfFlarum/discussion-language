import bootstrapAdmin from '@flarum/jest-config/src/bootstrap/admin';
import app from 'flarum/admin/app';
import tagsExtend from 'ext:flarum/tags/common/extend';
import extend from '../../../src/admin/extend';

beforeAll(() => {
  bootstrapAdmin();

  // flarum/tags is a dependency, so it boots first, as it does on a forum.
  app.bootExtensions({ 'flarum-tags': { extend: tagsExtend }, 'fof-discussion-language': { extend } });
  app.boot();
});

describe("the extension's admin", () => {
  it('registers its permissions, including the time allowed to change a language', () => {
    expect(app.registry.getExtensionPermissions('fof-discussion-language', 'moderate').has('discussion.changeLanguageModerate')).toBe(true);
    expect(app.registry.getExtensionPermissions('fof-discussion-language', 'start').has('fof-discussion-language.allow_language_change')).toBe(true);
  });

  it('registers its settings, so the admin can search for them', () => {
    const settings = (app.registry.getSettings('fof-discussion-language') ?? []).map((setting) => ('setting' in setting ? setting.setting : null));

    expect(settings).toEqual([
      'fof-discussion-language.native',
      'fof-discussion-language.showFlags',
      'fof-discussion-language.composerLocaleDefault',
      'fof-discussion-language.filter_language_on_http_request',
      'fof-discussion-language.showAnyLangOpt',
      'fof-discussion-language.useLocaleForTagsPageLastDiscussion',
    ]);
  });

  it('registers its page', () => {
    expect(app.registry.getPage('fof-discussion-language')).toBeDefined();
  });
});
