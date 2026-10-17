declare module 'twemoji-basename' {
  function basename(emoji: string): string;
  export = basename;
}

declare module '@fof-follow-tags' {
  // @TODO: import from `ext:vendor/extension/module-path` format.
  import type Component from 'flarum/common/Component';
  const components: {
    SubscriptionModal: typeof Component & { prototype: any };
  };
  export { components };
}
