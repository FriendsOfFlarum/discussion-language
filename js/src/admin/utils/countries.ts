import loadAsset from './loadAsset';

let countries: undefined | Record<string, Record<string, unknown>>;

export const load = async () => {
  countries = await loadAsset('countries.json');

  return countries;
};

export default function getCountries(useNativeName: boolean) {
  if (!countries) return {};

  const key = useNativeName ? 'native' : 'name';

  return Object.entries(countries).reduce((acc, [code, info]) => {
    acc[code] = info[key];

    return acc;
  }, {} as Record<string, string>);
}

export const getEmoji = (code: string) => countries?.[code]?.emoji;
