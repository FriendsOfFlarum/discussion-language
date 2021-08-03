import app from 'flarum/admin/app';

let ISO6391;

export const load = () => {
    __webpack_public_path__ = `${app.forum.attribute('baseUrl')}/assets/extensions/fof-discussion-language/`;

    return import(/* webpackChunkName: "iso-639-1" */ 'iso-639-1').then((pkg) => (ISO6391 = pkg.default));
};

export default (native) =>
    ISO6391?.[native ? 'getAllNativeNames' : 'getAllNames']?.()
        ?.sort((a, b) => a > b)
        ?.reduce((o, name) => {
            o[ISO6391.getCode(name)] = name;

            return o;
        }, {});

export const getName = (code, native) => ISO6391?.[native ? 'getNativeName' : 'getName']?.(code);
