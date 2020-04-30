import basename from 'twemoji-basename';

export default (language) =>
    language.emoji() && (
        <img
            alt={language.country()}
            className="emoji"
            draggable="false"
            src={`//cdn.jsdelivr.net/gh/twitter/twemoji@12/assets/72x72/${basename(language.emoji())}.png`}
        />
    );
