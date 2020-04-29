import countries from '../../common/generated/countries.json';

const data = Object.values(countries);

export const getCountries = (native) => {
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

export default countries;
