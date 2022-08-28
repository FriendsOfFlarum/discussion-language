import app from 'flarum/admin/app';

export default async function loadAsset<T>(name: string): Promise<T> {
  const URL = `${app.forum.attribute('baseUrl')}/assets/extensions/fof-discussion-language/${name}`;

  const response = await fetch(URL);

  if (!response.ok) {
    if (response.status === 404) {
      throw new Error(`Missing asset (\`${name}\`): ${URL}`);
    }
  }

  return await response.json();
}
