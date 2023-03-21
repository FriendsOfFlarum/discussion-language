import Model from 'flarum/common/Model';

export default class Language extends Model {
  code() {
    return Model.attribute<string>('code').call(this);
  }

  country() {
    return Model.attribute<string>('country').call(this);
  }

  name() {
    return Model.attribute<string>('name').call(this);
  }

  emoji() {
    return Model.attribute<string>('emoji').call(this);
  }

  apiEndpoint() {
    return `/fof/discussion-language${this.exists ? `/${this.data.id}` : ''}`;
  }
}
