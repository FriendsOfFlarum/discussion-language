const fs = require('fs');
const path = require('path');

const { countries } = require('countries-list');

const outputPath = path.resolve(__dirname, '../src/common/generated/countries.json');

const data = {};

for (const country in countries) {
  const { name, native, emoji } = countries[country];

  data[country.toLowerCase()] = { name, native, emoji };
}

const outputDir = path.dirname(outputPath);

if (!fs.existsSync(outputDir)) {
  fs.mkdirSync(outputDir);
}

fs.writeFileSync(outputPath, JSON.stringify(data));
