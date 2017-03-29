<?php
/**
 * Created by PhpStorm.
 * User: user8
 * Date: 30.03.16
 * Time: 15:09
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    |
    */

    'INFLUENCER_CAMPAIGN_STATUS_INVITED' => 'invited',
    'INFLUENCER_CAMPAIGN_STATUS_APPLIED' => 'applied',
    'INFLUENCER_CAMPAIGN_STATUS_APPLICATION_ACCEPTED' => 'application accepted',
    'INFLUENCER_CAMPAIGN_STATUS_APPLICATION_DECLINED' => 'application declined',
    'INFLUENCER_CAMPAIGN_STATUS_INELIGIBLE' => 'ineligible',
    'INFLUENCER_CAMPAIGN_STATUS_NULL' => null,

    'INFLUENCER_CAMPAIGN_STATUS_INVITED_REASON' => 'You were invited to participate in this campaign.',
    'INFLUENCER_CAMPAIGN_STATUS_APPLIED_REASON' => 'Your application to the campaign is pending review.',
    'INFLUENCER_CAMPAIGN_STATUS_APPLICATION_ACCEPTED_REASON' => 'Your application to the campaign has been accepted.',
    'INFLUENCER_CAMPAIGN_STATUS_APPLICATION_DECLINED_REASON' => 'Sorry your application to the campaign was not accepted.',
    'INFLUENCER_CAMPAIGN_STATUS_INELIGIBLE_REASON' => 'Sorry your account does not meet the minimum follower count.',
    'INFLUENCER_CAMPAIGN_STATUS_NULL_REASON' => null,


    'POST_STATUS_CREATED' => 'created',
    'POST_STATUS_PENDING' => 'pending',
    'POST_STATUS_APPROVED' => 'approved',
    'POST_STATUS_REJECTED' => 'rejected',

    'CAMPAIGN_STATUS_OPEN' => 'open',
    'CAMPAIGN_STATUS_CLOSED' => 'closed',

    'CAMPAIGN_VISIBILITY_DRAFT' => 'draft',
    'CAMPAIGN_VISIBILITY_PUBLISHED' => 'published',

    'INTEREST_ACTIVE' => 'active',
    'INTEREST_INACTIVE' => 'inactive',

    'CAMPAIGN_TYPE_PRIVATE' => 'private',
    'CAMPAIGN_TYPE_PUBLIC' => 'public',

    'USER_ROLE_ADMIN' => 'admin',
    'USER_ROLE_BRAND' => 'brand',
    'USER_ROLE_INFLUENCER' => 'influencer',
    'USER_ROLE_NEW' => 'new',
    'USER_ROLE_BLOCKED' => 'blocked',

    'CAMPAIGN_SOCIAL_YOUTUBE' => 'YouTube',
    'CAMPAIGN_SOCIAL_TWITTER' => 'Twitter',
    'CAMPAIGN_SOCIAL_VINE' => 'Vine',
    'CAMPAIGN_SOCIAL_INSTAGRAM' => 'Instagram',

    'GOOGLE_CLIENT_ID' => function_exists('env') ? env('GOOGLE_CLIENT_ID', '') : '',
    'GOOGLE_CLIENT_SECRET' => function_exists('env') ? env('GOOGLE_CLIENT_SECRET', '') : '',
    'GOOGLE_REDIRECT_URI' => function_exists('env') ? env('GOOGLE_REDIRECT_URI', '') : '',

    'INSTAGRAM_CLIENT_ID' => function_exists('env') ? env('INSTAGRAM_CLIENT_ID', '') : '',
    'INSTAGRAM_CLIENT_SECRET' => function_exists('env') ? env('INSTAGRAM_CLIENT_SECRET', '') : '',
    'INSTAGRAM_REDIRECT_URI' => function_exists('env') ? env('INSTAGRAM_REDIRECT_URI', '') : '',

    'TWITTER_CONSUMER_KEY' => function_exists('env') ? env('TWITTER_CONSUMER_KEY', '') : '',
    'TWITTER_CONSUMER_SECRET' => function_exists('env') ? env('TWITTER_CONSUMER_SECRET', '') : '',
    'TWITTER_ACCESS_TOKEN' => function_exists('env') ? env('TWITTER_ACCESS_TOKEN', '') : '',
    'TWITTER_ACCESS_TOKEN_SECRET' => function_exists('env') ? env('TWITTER_ACCESS_TOKEN_SECRET', '') : '',
];
