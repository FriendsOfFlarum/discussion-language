import loadAsset from './loadAsset';

let ISO6392;

export const load = async () => {
  ISO6392 = await loadAsset('iso-639-2.json');

  return ISO6392;
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
