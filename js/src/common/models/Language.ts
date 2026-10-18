import Model from 'flarum/common/Model';

export default class Language extends Model {
  code() {
    return Model.attribute<string>('code').call(this);
  }

  country() {
    return Model.attribute<string | null>('country').call(this);
  }

  name() {
    return Model.attribute<string | null>('name').call(this);
  }

  emoji() {
    return Model.attribute<string | null>('emoji').call(this);
  }
}
