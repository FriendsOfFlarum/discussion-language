import Model from 'flarum/common/Model';
export default class Language extends Model {
    code(): string;
    country(): string | null;
    name(): string | null;
    emoji(): string | null;
}
