import app from 'flarum/admin/app';

let countries;

export const load = () => {
  __webpack_public_path__ = `${app.forum.attribute('baseUrl')}/assets/extensions/fof-discussion-language/`;

  return import(/* webpackChunkName: "countries" */ '../../common/generated/countries.json').then((pkg) => (countries = pkg.default));
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
