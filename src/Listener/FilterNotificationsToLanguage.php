<?php

namespace FoF\DiscussionLanguage\Listener;

use Flarum\Discussion\Discussion;
use Flarum\Notification\Blueprint\BlueprintInterface;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use FoF\FollowTags\Notifications as FollowTags;

class FilterNotificationsToLanguage
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;
    
    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }
    
    public function __invoke(BlueprintInterface $blueprint, array $recipients): array
    {
        if (!($blueprint instanceof FollowTags\NewDiscussionBlueprint || $blueprint instanceof FollowTags\NewDiscussionTagBlueprint || $blueprint instanceof FollowTags\NewPostBlueprint)) {
            return $recipients;
        }

        $discussion = $blueprint->getSubject();

        if (!$discussion instanceof Discussion) {
            // for some reason, the discussion is not available
            return $recipients;
        }

        foreach ($recipients as $user) {
            /** @var User $user */
            if ($user->getPreference('locale') !== $discussion->language()->code) {
                // remove the user from the array
                
            }
        }

        return $recipients;
    }
}
