import app from 'flarum/admin/app';
// import locales from 'i18n-locales';

let ISO6392;

export const load = () => {
    __webpack_public_path__ = `${app.forum.attribute('baseUrl')}/assets/extensions/fof-discussion-language/`;

    return import(/* webpackChunkName: "iso-639-2" */ '../../../../resources/wikipedia-iso-639-2-codes.csv').then((pkg) => (ISO6392 = pkg.default));
};

export const getNameColumn = (data, native) => (native && data?.['Native name(s)']) || data?.['Language name(s)'];

export default (native) =>
    Array.from(Object.values(ISO6392 || {}))
        ?.sort((a, b) => getNameColumn(a, native)?.toLowerCase() > getNameColumn(b, native)?.toLowerCase())
        .reduce((o, data) => {
            o[data['639-1'] || data['639-2']] = getNameColumn(data, native);

            return o;
        }, {});

export const getName = (code, native) =>
    getNameColumn(
        ISO6392?.find((data) => [data['639-1'] || data['639-2']].includes(code)),
        native
    );
