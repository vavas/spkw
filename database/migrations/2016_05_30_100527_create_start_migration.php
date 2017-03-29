<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStartMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('psa_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('identity', 50)->unique();
            $table->string('email', 50)->unique();
            $table->string('password', 60);
            $table->string('accessToken', 255)->nullable();
            $table->string('authToken', 255)->nullable();
            $table->enum('role', ['new','influencer','brand','admin','blocked'])->default('new');
            $table->tinyInteger('onboarding')->default(0);
            $table->timestamp('lastLog')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
            $table->rememberToken();
        });

        Schema::create('psa_version', function (Blueprint $table) {
            $table->string('bundle_id', 255)->primary();
            $table->string('minimum_version', 255)->nullable();
            $table->string('current_version', 255)->nullable();
            $table->string('store_url', 255)->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
        });

        Schema::create('psa_states', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
        });

        Schema::create('psa_brands', function (Blueprint $table) {
            $table->increments('id');
            $table->string('identity', 50)->unique();
            $table->string('email', 50)->unique();
            $table->foreign('email')->references('email')->on('psa_users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('first_name', 255);
            $table->string('last_name', 255);
            $table->string('brand_name', 255);
            $table->string('image_url', 255);
            $table->string('details', 255)->nullable();
            $table->string('media', 255)->nullable();
            $table->string('brand_media', 255)->nullable();
            $table->enum('submission', ['private','public'])->nullable();
            $table->string('category', 255)->nullable();
            $table->enum('status', ['project','public'])->default('project');
            $table->string('hashtag', 255)->nullable();
            $table->string('url', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('psa_influencers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email', 50)->unique();
            $table->foreign('email')->references('email')->on('psa_users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('first_name', 255);
            $table->string('last_name', 255);
            $table->string('image_url', 255)->nullable();
            $table->enum('gender', ['Female','Male'])->nullable();
            $table->integer('age')->nullable();
            $table->date('birthday')->nullable();
            $table->string('image', 255)->nullable();
            $table->string('location_state', 50)->nullable();
            $table->string('location_city', 50)->nullable();
            $table->text('interests')->nullable();
            $table->string('paypal', 50)->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
        });

        Schema::create('psa_campaign', function (Blueprint $table) {
            $table->increments('id');
            $table->string('brand_id', 50);
            $table->index('brand_id');
            $table->foreign('brand_id')->references('identity')->on('psa_brands')->onDelete('cascade')->onUpdate('cascade');
            $table->string('identity', 50)->unique();
            $table->enum('status', ['open','closed'])->default('open');
            $table->enum('type', ['public','private'])->nullable();
            $table->enum('visibility', ['draft','published'])->default('draft');
            $table->enum('disclosure', ['#Sp','#sp','#SP','#sponsored','#Ad','#ad','#AD'])->nullable();
            $table->string('title', 255);
            $table->text('details');
            $table->enum('social_network', ['Twitter','Vine','Instagram','YouTube'])->nullable();
            $table->date('submission_deadline');
            $table->date('application_deadline');
            $table->text('guidelines')->nullable();
            $table->enum('media', ['image','video','gif','brand provided']);
            $table->text('interests')->nullable();
            $table->integer('compensation');
            $table->text('other_compensation')->nullable();
            $table->integer('minimum_reach');
            $table->date('posting_date');
            $table->string('location_state', 255)->nullable();
            $table->string('location_city', 255)->nullable();
            $table->enum('condition', ['project','public']);
            $table->string('hashtag', 255)->nullable();
            $table->string('mention', 255)->nullable();
            $table->string('url', 255)->nullable();
            $table->string('campaign_image', 255)->nullable();
            $table->string('campaign_detail', 255)->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
        });

        Schema::create('psa_interests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('identity', 50)->unique();
            $table->string('interest_name', 255);
            $table->enum('status', ['active','inactive']);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
        });

        Schema::create('psa_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('identity', 50)->unique();
            $table->string('campaign_id', 50);
            $table->index('campaign_id');
            $table->foreign('campaign_id')->references('identity')->on('psa_campaign')->onDelete('cascade')->onUpdate('cascade');
            $table->string('influencer_id', 50);
            $table->index('influencer_id');
            $table->foreign('influencer_id')->references('identity')->on('psa_users');
            $table->string('text', 255)->nullable();
            $table->string('image_url', 255)->nullable();
            $table->string('video_url', 255)->nullable();
            $table->enum('status', ['created','pending','approved','rejected'])->nullable();
            $table->text('reject_reason')->nullable();
            $table->tinyInteger('posted')->default(0);
            $table->timestamp('publish_at')->nullable();
            $table->enum('timezone', ['PDT', 'EDT'])->default('PDT');
            $table->timestamp('posted_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        Schema::create('psa_interest_campaign', function (Blueprint $table) {
            $table->increments('id');
            $table->string('interest_id', 50);
            $table->index('interest_id');
            $table->foreign('interest_id')->references('identity')->on('psa_interests');
            $table->string('campaign_id', 50);
            $table->index('campaign_id');
            $table->foreign('campaign_id')->references('identity')->on('psa_campaign')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        Schema::create('psa_influencers_campaign', function (Blueprint $table) {
            $table->increments('id');
            $table->string('identity', 50)->unique();
            $table->string('campaign_id', 50);
            $table->index('campaign_id');
            $table->foreign('campaign_id')->references('identity')->on('psa_campaign')->onDelete('cascade')->onUpdate('cascade');
            $table->string('influencer_id', 50);
            $table->index('influencer_id');
            $table->foreign('influencer_id')->references('identity')->on('psa_users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('post_identity', 50)->nullable();
            $table->index('post_identity');
            $table->foreign('post_identity')->references('identity')->on('psa_posts')->onDelete('cascade')->onUpdate('cascade');
            $table->enum('app_brand', ['invitation','approved','rejected'])->default('invitation');
            $table->enum('app_influencer', ['invitation','approved','rejected'])->default('invitation');
            $table->enum('status', ['invited','applied','application accepted','application declined','ineligible'])
                ->nullable();
            $table->decimal('compensation', 10,2)->nullable();
            $table->string('consideration', 255)->nullable();
            $table->text('message', 255)->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
        });

        Schema::create('psa_interest_influencer', function (Blueprint $table) {
            $table->increments('id');
            $table->string('interest_identity', 50);
            $table->index('interest_identity');
            $table->foreign('interest_identity')->references('identity')->on('psa_interests');
            $table->string('influencer_identity', 50);
            $table->index('influencer_identity');
            $table->foreign('influencer_identity')->references('identity')->on('psa_users')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        Schema::create('psa_users_temporary', function (Blueprint $table) {
            $table->increments('id');
            $table->string('identity', 50);
            $table->index('identity');
            $table->foreign('identity')->references('identity')->on('psa_users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('email', 50);
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        Schema::create('psa_social_account', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_identity', 50);
            $table->index('user_identity');
            $table->foreign('user_identity')->references('identity')->on('psa_users')->onDelete('cascade')->onUpdate('cascade');
            $table->tinyInteger('twitter_verified')->nullable();
            $table->string('twitter_id', 255)->nullable();
            $table->string('twitter_token', 255)->nullable();
            $table->string('twitter_token_secret', 255)->nullable();
            $table->string('twitter_screen_name', 255)->nullable();
            $table->string('twitter_image', 255)->nullable();
            $table->integer('twitter_followers')->nullable();
            $table->tinyInteger('twitter_onboard')->default(0);
            $table->tinyInteger('youtube_verified')->nullable();
            $table->string('youtube_id', 255)->nullable();
            $table->string('youtube_token', 255)->nullable();
            $table->string('youtube_name', 255)->nullable();
            $table->string('youtube_image', 255)->nullable();
            $table->string('youtube_subscribers', 50)->nullable();
            $table->string('youtube_views', 50)->nullable();
            $table->string('youtube_videos', 50)->nullable();
            $table->string('youtube_channel_id', 50)->nullable();
            $table->string('youtube_channel_url', 50)->nullable();
            $table->tinyInteger('youtube_onboard')->default(0);
            $table->tinyInteger('instagram_verified')->nullable();
            $table->string('instagram_id', 255)->nullable();
            $table->string('instagram_token', 255)->nullable();
            $table->string('instagram_name', 255)->nullable();
            $table->string('instagram_image', 255)->nullable();
            $table->integer('instagram_followers')->nullable();
            $table->tinyInteger('instagram_onboard')->default(0);
            $table->string('total_reach', 50)->nullable();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        Schema::create('psa_social_instagram', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_identify', 50);
            $table->index('user_identify');
            $table->foreign('user_identify')->references('identity')->on('psa_users');
            $table->integer('media')->nullable();
            $table->integer('followed_by')->nullable();
            $table->integer('follows')->nullable();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        Schema::create('psa_social_twitter', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_identify', 50);
            $table->index('user_identify');
            $table->foreign('user_identify')->references('identity')->on('psa_users');
            $table->integer('followers_count')->nullable();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        Schema::create('psa_social_youtube', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_identify', 50);
            $table->index('user_identify');
            $table->foreign('user_identify')->references('identity')->on('psa_users');
            $table->string('number_subscribers', 50)->nullable();
            $table->string('number_views', 50)->nullable();
            $table->string('number_videos', 50)->nullable();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        DB::statement('CREATE FULLTEXT INDEX psa_influencers_fist_name_last_name ON psa_influencers (first_name, 
        last_name)');
        DB::statement('CREATE FULLTEXT INDEX psa_social_account_twitter_instagram_youtube ON psa_social_account (twitter_screen_name, youtube_name, instagram_name)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
