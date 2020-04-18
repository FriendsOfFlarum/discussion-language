import Model from 'flarum/Model';

export default class Language extends Model {
    code = Model.attribute('code');

    apiEndpoint() {
        return `/fof/discussion-language${this.exists ? `/${this.data.id}` : ''}`;
    }
}
