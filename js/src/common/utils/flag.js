import basename from 'twemoji-basename';

import { default as countries } from './countries';

export default (country) =>
    countries[country] && (
        <img
            alt={country}
            className="emoji"
            draggable="false"
            src={`//cdn.jsdelivr.net/gh/twitter/twemoji@12/assets/72x72/${basename(countries[country].emoji)}.png`}
        />
    );
