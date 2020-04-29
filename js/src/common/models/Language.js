import Model from 'flarum/Model';

export default class Language extends Model {
    code = Model.attribute('code');
    country = Model.attribute('country');

    apiEndpoint() {
        return `/fof/discussion-language${this.exists ? `/${this.data.id}` : ''}`;
    }
}
