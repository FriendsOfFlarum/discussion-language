import loadAsset from './loadAsset';

interface ISO6392Entry {
  '639-1': string;
  '639-2': string;
  'Language name(s)': string;
  'Native name(s)': string;
}

let ISO6392: ISO6392Entry[] | undefined;

export const load = async () => {
  ISO6392 = await loadAsset<ISO6392Entry[]>('iso-639-2.json');

  return ISO6392;
};

export const getNameColumn = (data: ISO6392Entry | undefined, native: boolean): string | undefined =>
  (native && data?.['Native name(s)']) || data?.['Language name(s)'];

export default (native: boolean): Record<string, string | undefined> =>
  Array.from(Object.values(ISO6392 || {}))
    ?.sort((a, b) => ((getNameColumn(a, native)?.toLowerCase() ?? '') > (getNameColumn(b, native)?.toLowerCase() ?? '') ? 1 : -1))
    .reduce(
      (o, data) => {
        o[data['639-1'] || data['639-2']] = getNameColumn(data, native);

        return o;
      },
      {} as Record<string, string | undefined>
    );

export const getName = (code: string, native: boolean): string | undefined =>
  getNameColumn(
    ISO6392?.find((data) => [data['639-1'] || data['639-2']].includes(code)),
    native
  );
