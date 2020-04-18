<?php


namespace FoF\DiscussionLanguage\Access;


use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\AbstractPolicy;
use Flarum\User\User;

class DiscussionPolicy extends AbstractPolicy
{
    protected $model = Discussion::class;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function changeLanguage(User $actor, Discussion $discussion) {
        if ($discussion->user_id == $actor->id && $actor->can('reply', $discussion)) {
            $allowEditLanguage = $this->settings->get('fof-discussion-language.allow_language_change');

            if ($allowEditLanguage === '-1'
                || ($allowEditLanguage === 'reply' && $discussion->participant_count <= 1)
                || (is_numeric($allowEditLanguage) && $discussion->created_at->diffInMinutes(new Carbon) < $allowEditLanguage)
            ) {
                return true;
            }
        }
    }
}
