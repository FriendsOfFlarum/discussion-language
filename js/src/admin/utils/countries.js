import loadAsset from './loadAsset';

let countries;

export const load = async () => {
  countries = await loadAsset('countries.json');

  return countries;
};

export default (native) => {
  if (!countries) return {};

  const data = Object.values(countries);
  const key = native ? 'native' : 'name';

  return data
    .map((c) => c[key])
    .sort((a, b) => a > b)
    .reduce((o, name) => {
      const i = data.indexOf(data.filter((c) => c[key] === name)[0]);

      o[Object.keys(countries)[i]] = name;

      return o;
    }, {});
};

export const getEmoji = (code) => countries?.[code]?.emoji;
