import basename from 'twemoji-basename';
import icon from 'flarum/common/helpers/icon';

export default (language) => {
  if (!language) return;

  const emoji = language.emoji ? language.emoji() : language;

  return emoji ? (
    <img
      alt={(language.country && language.country()) || ''}
      className="emoji"
      draggable="false"
      loading="lazy"
      src={`//cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/72x72/${basename(emoji)}.png`}
    />
  ) : (
    icon('fas fa-globe')
  );
};
