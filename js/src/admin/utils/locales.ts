import loadAsset from './loadAsset';

export interface ISO6392Entry {
  '639-1': string;
  '639-2': string;
  '639-3': string;
  'Language name(s)': string;
  'Native name(s)': string;
}

let ISO6392: ISO6392Entry[] | undefined;

export const load = async () => {
  ISO6392 = await loadAsset<ISO6392Entry[]>('iso-639-2.json');

  return ISO6392;
};

/**
 * The code a language is stored with: its shortest. Some are only in ISO 639-3,
 * such as Venetian (vec), so without it they all shared the code ''.
 */
export const codeOf = (data: ISO6392Entry): string => data['639-1'] || data['639-2'] || data['639-3'];

export const getNameColumn = (data: ISO6392Entry | undefined, native: boolean): string | undefined =>
  (native && data?.['Native name(s)']) || data?.['Language name(s)'];

/**
 * The languages to choose from, by code, in the order of their names.
 */
export function localeOptions(entries: ISO6392Entry[], native: boolean): Record<string, string | undefined> {
  const name = (data: ISO6392Entry) => getNameColumn(data, native)?.toLowerCase() ?? '';

  return entries
    .filter((data) => codeOf(data))
    .sort((a, b) => name(a).localeCompare(name(b)))
    .reduce(
      (options, data) => {
        options[codeOf(data)] = getNameColumn(data, native);

        return options;
      },
      {} as Record<string, string | undefined>
    );
}

export default function getLocales(native: boolean): Record<string, string | undefined> {
  return localeOptions(ISO6392 || [], native);
}

export const getName = (code: string, native: boolean): string | undefined =>
  getNameColumn(
    ISO6392?.find((data) => codeOf(data) === code),
    native
  );
