import basename from 'twemoji-basename';
import Icon from 'flarum/common/components/Icon';
import type Language from '../models/Language';
import type Mithril from 'mithril';

// Named: flarum-webpack-config 3.0.6 registers a one-parameter arrow default
// export under its parameter's name, which is undefined at runtime.
export default function flag(language: Language | string): Mithril.Children {
  if (!language) return;

  const emoji = typeof language === 'string' ? language : language.emoji?.();

  return emoji ? (
    <img
      alt={(typeof language !== 'string' && language.country?.()) || ''}
      className="emoji DiscussionLanguage-flag"
      draggable="false"
      loading="lazy"
      src={`//cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/72x72/${basename(emoji)}.png`}
    />
  ) : (
    <Icon name="fas fa-globe" />
  );
}
