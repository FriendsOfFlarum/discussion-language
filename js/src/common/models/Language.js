import Model from 'flarum/Model';

export default class Language extends Model {
    code = Model.attribute('code');
    country = Model.attribute('country');

    language = Model.attribute('language');
    emoji = Model.attribute('emoji');

    apiEndpoint() {
        return `/fof/discussion-language${this.exists ? `/${this.data.id}` : ''}`;
    }
}
