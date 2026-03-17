import basename from 'twemoji-basename';
import icon from 'flarum/common/helpers/icon';
import type Language from '../models/Language';
import type Mithril from 'mithril';

export default (language: Language | string): Mithril.Children => {
  if (!language) return;

  const emoji = typeof language === 'string' ? language : language.emoji?.();

  return emoji ? (
    <img
      alt={(typeof language !== 'string' && language.country?.()) || ''}
      className="emoji"
      draggable="false"
      loading="lazy"
      src={`//cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/72x72/${basename(emoji)}.png`}
    />
  ) : (
    icon('fas fa-globe')
  );
};
