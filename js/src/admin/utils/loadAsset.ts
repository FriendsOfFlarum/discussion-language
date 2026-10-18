import app from 'flarum/admin/app';

export default async function loadAsset<T>(name: string): Promise<T> {
  const URL = `${app.forum.attribute('assetsBaseUrl')}/extensions/fof-discussion-language/${name}`;

  const response = await fetch(URL);

  if (!response.ok) {
    throw new Error(response.status === 404 ? `Missing asset (\`${name}\`): ${URL}` : `Couldn't load asset (\`${name}\`): ${response.status} ${URL}`);
  }

  return await response.json();
}
