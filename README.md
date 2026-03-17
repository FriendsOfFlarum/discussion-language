# Discussion Language by FriendsOfFlarum

![License](https://img.shields.io/badge/license-MIT-blue.svg) [![Latest Stable Version](https://img.shields.io/packagist/v/fof/discussion-language.svg)](https://packagist.org/packages/fof/discussion-language) [![OpenCollective](https://img.shields.io/badge/opencollective-fof-blue.svg)](https://opencollective.com/fof/donate)  [![Donate](https://img.shields.io/badge/donate-datitisev-important.svg)](https://datitisev.me/donate)

![image](https://extiverse.com/extension/fof/discussion-language/open-graph-image)

A [Flarum](http://flarum.org) extension. Tag discussions with the language they are written in, filter the discussion list by language, and optionally auto-detect a visitor's preferred language to show relevant content by default.

### Features

- **Language tagging** — Assign a language (with optional country flag) to each discussion. Authors are prompted to choose a language when composing, moderators can change it at any time.
- **Discussion list filtering** — A language dropdown appears on the index page. Discussions are filtered by language automatically based on the visitor's browser locale, cookie, or user preference — or they can manually pick "Any Language".
- **Search gambit** — Filter search results by language using `language:<code>` in the search bar.
- **Composer pre-selection** — Optionally pre-fill the language picker in the composer with the user's current Flarum locale.
- **Country flags** — Display emoji flags alongside language names (backed by [twemoji](https://twemoji.twitter.com/)). Uses ISO 639-2 language codes and country data.
- **Native language names** — Optionally display language names in their own script (e.g. "Deutsch" instead of "German").
- **Tags page integration** — Optionally show only the most recent discussion in the user's locale on the tags page instead of the overall most recent discussion.
- **fof/follow-tags integration** — When [fof/follow-tags](https://github.com/FriendsOfFlarum/follow-tags) is enabled, users can choose which discussion language they want to receive tag subscription notifications for.

### Installation

```sh
composer require fof/discussion-language:"*"
```

### Updating

```sh
composer update fof/discussion-language
php flarum assets:publish
php flarum migrate
```

### Configuration

All settings are available under the extension's admin page:

| Setting | Description | Default |
|---|---|---|
| Use native language names | Show language names in their own script | Off |
| Show country flags | Display emoji flags next to language names | On |
| Pre-select current locale in composer | Automatically pick the user's Flarum locale when starting a discussion | Off |
| Use detected language for default filter | Redirect visitors to their detected language on page load | Off |
| Show "Any Language" option | Add an option to the dropdown to view all discussions regardless of language | On |
| Only show locale discussions on tags page | Replace the tag's last discussion link with the most recent one in the user's locale | Off |

Languages and their associated countries (for flag display) are managed from the **Languages** section of the extension's admin page.

#### Permissions

- **Allow language editing** — Controls which user groups can change the language of their own discussions, and for how long after posting (immediately, within 10 minutes, or until someone else replies).
- **Allow moderate language editing** — Controls which groups can change the language on any discussion at any time.

### Links

[![OpenCollective](https://img.shields.io/badge/donate-friendsofflarum-44AEE5?style=for-the-badge&logo=open-collective)](https://opencollective.com/fof/donate) [![GitHub](https://img.shields.io/badge/donate-datitisev-ea4aaa?style=for-the-badge&logo=github)](https://datitisev.me/donate/github)

- [Packagist](https://packagist.org/packages/fof/discussion-language)
- [GitHub](https://github.com/FriendsOfFlarum/discussion-language)

An extension by [FriendsOfFlarum](https://github.com/FriendsOfFlarum), commissioned by [mailcow](https://mailcow.email/).
