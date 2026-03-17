import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import Language from '../../common/models/Language';
import type Mithril from 'mithril';
export default class LanguagesSettingsPage extends ExtensionPage<never> {
    recordsUpdating: Record<string, boolean>;
    recordsDeleting: Record<string, boolean>;
    newLocaleValue: any;
    newCountryValue: any;
    loadingData: boolean;
    loadingDataError: boolean;
    errorDetails: null | Error;
    isAddingNewRecord: boolean;
    oncreate(vnode: Mithril.Vnode<never, this>): void;
    loadData(): Promise<void>;
    content(vnode: Mithril.VnodeDOM<never, this>): JSX.Element;
    localeSettings(): JSX.Element;
    onkeydown(e: KeyboardEvent): void;
    add(): void;
    remove(language: Language): void;
}
