import { extend, override } from 'flarum/extend';
import IndexPage from 'flarum/components/IndexPage';
import DiscussionComposer from 'flarum/components/DiscussionComposer';

import LanguageDiscussionModal from './components/LanguageDiscussionModal';
import Language from './components/Language';

const sort = (a, b) => a.code().toLowerCase() > b.code().toLowerCase();

export default () => {
    extend(IndexPage.prototype, 'newDiscussionAction', function (promise) {
        promise.then((component) => (component.language = app.store.getBy('discussion-languages', 'code', this.params().language)));
    });

    DiscussionComposer.prototype.chooseLanguage = function () {
        app.modal.show(
            new LanguageDiscussionModal({
                selected: this.language,
                onsubmit: (language) => {
                    this.language = language;
                    this.$('textarea').focus();
                },
            })
        );
    };

    extend(DiscussionComposer.prototype, 'headerItems', function (items) {
        items.add(
            'language',
            <a className="DiscussionComposer-changeTags" onclick={this.chooseLanguage.bind(this)}>
                <span className={`LanguageLabel ${this.language ? '' : 'none'}`}>
                    {this.language
                        ? Language.component({ language: this.language, uppercase: true })
                        : app.translator.trans('fof-discussion-language.forum.composer_discussion.choose_language_link')}
                </span>
            </a>,
            20
        );
    });

    override(DiscussionComposer.prototype, 'onsubmit', function (original) {
        if (!this.language) return this.chooseLanguage();

        original();
    });

    extend(DiscussionComposer.prototype, 'data', function (data) {
        data.relationships = data.relationships || {};

        data.relationships.language = this.language || app.store.all('discussion-languages').sort(sort)[0];
    });
};
