import loadAsset from './loadAsset';

let countries: undefined | Record<string, Record<string, unknown>>;

export const load = async () => {
  countries = await loadAsset('countries.json');

  return countries;
};

/**
 * The countries to choose from, by code, in the order of their names.
 */
export function countryOptions(entries: Record<string, Record<string, unknown>>, useNativeName: boolean): Record<string, string> {
  const key = useNativeName ? 'native' : 'name';

  return Object.entries(entries)
    .map(([code, info]) => [code, info[key] as string] as const)
    .sort(([, a], [, b]) => a.localeCompare(b))
    .reduce(
      (options, [code, name]) => {
        options[code] = name;

        return options;
      },
      {} as Record<string, string>
    );
}

export default function getCountries(useNativeName: boolean): Record<string, string> {
  return countryOptions(countries || {}, useNativeName);
}

export const getEmoji = (code: string) => countries?.[code]?.emoji;
