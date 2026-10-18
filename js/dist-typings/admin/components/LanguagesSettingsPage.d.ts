import ExtensionPage, { type ExtensionPageAttrs } from 'flarum/admin/components/ExtensionPage';
import type ItemList from 'flarum/common/utils/ItemList';
import Language from '../../common/models/Language';
import type Mithril from 'mithril';
export default class LanguagesSettingsPage extends ExtensionPage {
    recordsUpdating: Record<string, boolean>;
    recordsDeleting: Record<string, boolean>;
    newLocaleValue: any;
    newCountryValue: any;
    loadingData: boolean;
    loadingDataError: boolean;
    errorDetails: null | Error;
    isAddingNewRecord: boolean;
    oncreate(vnode: Mithril.VnodeDOM<ExtensionPageAttrs, this>): void;
    loadData(): Promise<void>;
    sections(vnode: Mithril.VnodeDOM<ExtensionPageAttrs, this>): ItemList<unknown>;
    languagesSection(): JSX.Element;
    languageList(): JSX.Element;
    add(): void;
    update(language: Language, attributes: {
        code?: string;
        country?: string;
    }): void;
    remove(language: Language): void;
}
