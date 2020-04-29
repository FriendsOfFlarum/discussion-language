import ISO6391 from 'iso-639-1';

export default (native) =>
    ISO6391[native ? 'getAllNativeNames' : 'getAllNames']()
        .sort((a, b) => a > b)
        .reduce((o, name) => {
            o[ISO6391.getCode(name)] = name;

            return o;
        }, {});

export const getName = (code, native) => ISO6391[native ? 'getNativeName' : 'getName'](code);
