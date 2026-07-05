<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('analytics_page_views')) {
            Schema::create('analytics_page_views', function (Blueprint $table) {
                $table->id();
                $table->string('visitor_uuid', 64)->nullable()->index();
                $table->string('session_id', 120)->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('item_id')->nullable()->index();
                $table->string('route_name')->nullable()->index();
                $table->string('page_type', 60)->default('pagina')->index();
                $table->string('page_title')->nullable();
                $table->string('path', 500);
                $table->longText('full_url')->nullable();
                $table->longText('query_string')->nullable();
                $table->longText('referrer')->nullable();
                $table->string('referrer_host')->nullable()->index();
                $table->string('utm_source')->nullable()->index();
                $table->string('utm_medium')->nullable()->index();
                $table->string('utm_campaign')->nullable()->index();
                $table->string('utm_content')->nullable();
                $table->string('utm_term')->nullable();
                $table->string('device_type', 30)->nullable()->index();
                $table->string('browser', 80)->nullable();
                $table->string('platform', 80)->nullable();
                $table->string('ip_hash', 64)->nullable()->index();
                $table->longText('user_agent')->nullable();
                $table->boolean('is_bot')->default(false)->index();
                $table->timestamps();

                $table->index(['created_at', 'page_type']);
                $table->index(['item_id', 'created_at']);
                $table->index(['route_name', 'created_at']);
                $table->index(['visitor_uuid', 'created_at']);
            });
        }

        if (!Schema::hasTable('analytics_events')) {
            Schema::create('analytics_events', function (Blueprint $table) {
                $table->id();
                $table->string('visitor_uuid', 64)->nullable()->index();
                $table->string('session_id', 120)->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('item_id')->nullable()->index();
                $table->string('event_name', 100)->index();
                $table->string('event_category', 80)->nullable()->index();
                $table->string('page_type', 60)->nullable()->index();
                $table->string('path', 500)->nullable();
                $table->decimal('value', 12, 2)->nullable();
                $table->longText('metadata')->nullable();
                $table->timestamps();

                $table->index(['event_name', 'created_at']);
                $table->index(['item_id', 'created_at']);
                $table->index(['visitor_uuid', 'created_at']);
            });
        }

        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                if (!Schema::hasColumn('items', 'seo_title')) {
                    $table->string('seo_title')->nullable()->after('meta_description');
                }
                if (!Schema::hasColumn('items', 'seo_focus_keyword')) {
                    $table->string('seo_focus_keyword')->nullable()->after('seo_title');
                }
                if (!Schema::hasColumn('items', 'seo_canonical_url')) {
                    $table->string('seo_canonical_url', 500)->nullable()->after('seo_focus_keyword');
                }
                if (!Schema::hasColumn('items', 'seo_robots')) {
                    $table->string('seo_robots', 60)->default('index,follow')->after('seo_canonical_url');
                }
                if (!Schema::hasColumn('items', 'og_title')) {
                    $table->string('og_title')->nullable()->after('seo_robots');
                }
                if (!Schema::hasColumn('items', 'og_description')) {
                    $table->text('og_description')->nullable()->after('og_title');
                }
                if (!Schema::hasColumn('items', 'og_image')) {
                    $table->string('og_image', 500)->nullable()->after('og_description');
                }
                if (!Schema::hasColumn('items', 'twitter_title')) {
                    $table->string('twitter_title')->nullable()->after('og_image');
                }
                if (!Schema::hasColumn('items', 'twitter_description')) {
                    $table->text('twitter_description')->nullable()->after('twitter_title');
                }
                if (!Schema::hasColumn('items', 'twitter_image')) {
                    $table->string('twitter_image', 500)->nullable()->after('twitter_description');
                }
                if (!Schema::hasColumn('items', 'seo_score')) {
                    $table->unsignedTinyInteger('seo_score')->default(0)->after('twitter_image');
                }
                if (!Schema::hasColumn('items', 'seo_analysis')) {
                    $table->longText('seo_analysis')->nullable()->after('seo_score');
                }
                if (!Schema::hasColumn('items', 'seo_last_analyzed_at')) {
                    $table->timestamp('seo_last_analyzed_at')->nullable()->after('seo_analysis');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                foreach ([
                    'seo_last_analyzed_at',
                    'seo_analysis',
                    'seo_score',
                    'twitter_image',
                    'twitter_description',
                    'twitter_title',
                    'og_image',
                    'og_description',
                    'og_title',
                    'seo_robots',
                    'seo_canonical_url',
                    'seo_focus_keyword',
                    'seo_title',
                ] as $column) {
                    if (Schema::hasColumn('items', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('analytics_page_views');
    }
};
