declare module 'twemoji-basename' {
  function basename(emoji: string): string;
  export = basename;
}

declare module '@fof-follow-tags' {
  import type Component from 'flarum/common/Component';
  const components: {
    SubscriptionModal: typeof Component & { prototype: any };
  };
  export { components };
}
