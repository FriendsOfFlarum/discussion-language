import Model from 'flarum/common/Model';

export default class Language extends Model {
    code = Model.attribute('code');
    country = Model.attribute('country');

    name = Model.attribute('name');
    emoji = Model.attribute('emoji');

    apiEndpoint() {
        return `/fof/discussion-language${this.exists ? `/${this.data.id}` : ''}`;
    }
}
