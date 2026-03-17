import Model from 'flarum/common/Model';
export default class Language extends Model {
    code(): string;
    country(): string;
    name(): string;
    emoji(): string;
    apiEndpoint(): string;
}
