import { extend, override } from 'flarum/common/extend';
import IndexPage from 'flarum/forum/components/IndexPage';
import DiscussionComposer from 'flarum/forum/components/DiscussionComposer';

import LanguageDiscussionModal from './components/LanguageDiscussionModal';
import Language from './components/Language';

const sort = (a, b) => a.code().toLowerCase() > b.code().toLowerCase();

export default () => {
    extend(IndexPage.prototype, 'newDiscussionAction', function (promise) {
        const dislang = app.search.params().language;

        if (dislang) {
            promise.then((composer) => (composer.fields.language = app.store.getBy('discussion-languages', 'code', dislang)));
        } else {
            const localeComposer = app.forum.attribute('fof-discussion-language.composerLocaleDefault');
            app.composer.fields.language = localeComposer ? app.store.getBy('discussion-languages', 'code', app.translator.locale) : '';
        }
    });

    DiscussionComposer.prototype.chooseLanguage = function (hide, callback) {
        app.modal.show(LanguageDiscussionModal, {
            selected: this.composer.fields.language,
            hideSubmitButton: hide,
            onsubmit: (language) => {
                this.composer.fields.language = language;
                this.$('textarea').focus();

                if (callback) callback();
            },
        });
    };

    extend(DiscussionComposer.prototype, 'headerItems', function (items) {
        items.add(
            'language',
            <a className="DiscussionComposer-changeTags" onclick={this.chooseLanguage.bind(this, true, null)}>
                <span className={`LanguageLabel ${this.composer.fields.language ? '' : 'none'}`}>
                    {this.composer.fields.language
                        ? Language.component({ language: this.composer.fields.language, uppercase: true })
                        : app.translator.trans('fof-discussion-language.forum.composer_discussion.choose_language_link')}
                </span>
            </a>,
            20
        );
    });

    override(DiscussionComposer.prototype, 'onsubmit', function (original) {
        if (!this.composer.fields.language) return this.chooseLanguage(true, original);

        original();
    });

    extend(DiscussionComposer.prototype, 'data', function (data) {
        data.relationships = data.relationships || {};

        data.relationships.language = this.composer.fields.language || app.store.all('discussion-languages').sort(sort)[0];
    });
};
