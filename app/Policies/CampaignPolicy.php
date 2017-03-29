<?php

namespace App\Policies;

use App\Campaign;
use App\User;

class CampaignPolicy
{

    /**
     * @param User $user
     * @param Campaign $campaign
     * @param array $data
     * @return bool
     */
    public function createPost(Campaign $campaign, $data)
    {
        switch ($campaign->media) {
            case 'image':
                if (!isset($data['image_url'])) {
                    return false;
                } else {
                    return true;
                }
                break;
            case 'video':
                if (!isset($data['video_url'])) {
                    return false;
                } else {
                    return true;
                }
                break;
            case 'default':
                return true;
                break;
        }

    }

    /**
     * Check url in string
     * @param User $user
     * @param Campaign $campaign
     * @param string $text
     * @return bool
     */
    public function checkUrlCampaign(Campaign $campaign, $text)
    {
        if (isset($campaign->url) && !empty($campaign->url)) {
            if (strpos($text, $campaign->url) !== false) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * @param User $user
     * @param Campaign $campaign
     * @param string $text
     * @return bool
     */
    public function checkHashTagsCampaign(User $user, Campaign $campaign, $text)
    {
        if ($campaign->hashtag) {
            $tags = unserialize($campaign->hashtag);
            if (!is_array($tags)) {
                $tags = explode(',', $tags);
            }
            foreach ($tags as $tag) {
                if (strpos($text, $tag) === false) {
                    return false;
                }
            }
            return true;
        } else {
            return true;
        }
    }

    /**
     * @param User $user
     * @param Campaign $campaign
     * @param string $text
     * @return bool
     */
    public function checkMentionCampaign(User $user, Campaign $campaign, $text)
    {
        if ($campaign->mention) {
            $mentions = unserialize($campaign->mention);
            if (!is_array($mentions)) {
                $mentions = explode(',', $mentions);
            }
            foreach ($mentions as $mention) {
                if (strpos($text, $mention) === false) {
                    return false;
                }
            }
            return true;
        } else {
            return true;
        }
    }

    /**
     * @param User $user
     * @param Campaign $campaign
     * @return bool
     */
    public function checkDeadline(User $user, Campaign $campaign)
    {
        if ($campaign->type === config('constants.CAMPAIGN_TYPE_PUBLIC')) {
            if ($campaign->application_deadline < $campaign->posting_date
                && $campaign->posting_date > $campaign->submission_deadline) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }

    }

}
