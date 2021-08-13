import app from 'flarum/admin/app';
// import locales from 'i18n-locales';

let ISO6392;

export const load = () => {
    __webpack_public_path__ = `${app.forum.attribute('baseUrl')}/assets/extensions/fof-discussion-language/`;

    return import(/* webpackChunkName: "iso-639-2" */ 'iso-639-2').then((pkg) => (window.ISO6392 = ISO6392 = pkg));
};

export default (native) =>
    ISO6392?.iso6392
        .sort((a, b) => a.name.toLowerCase() > b.name.toLowerCase())
        ?.reduce((o, data) => {
            o[data.iso6392T || data.iso6392B] = data.name;

            return o;
        }, {})

// export const getName = (code, native) => localeCodes?.find(data => data.tag === code)?.[native ? 'local' : 'name'];

export const getName = (code, native) => ISO6392?.iso6392?.find(data => data.iso6392B === code || data.iso6391 === code)?.name;
