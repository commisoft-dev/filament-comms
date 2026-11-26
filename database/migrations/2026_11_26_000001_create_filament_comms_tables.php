<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comms_email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->default('newsletters')->index();
            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->longText('body_html');
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('comms_email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->nullable()->constrained('comms_email_templates')->nullOnDelete();
            $table->string('title');
            $table->string('category')->default('newsletters')->index();
            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->longText('body_html');
            $table->json('audience_filter')->nullable();
            $table->string('status')->default('draft')->index(); // draft, scheduled, sending, paused, sent, cancelled
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('comms_email_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained('comms_email_campaigns')->cascadeOnDelete();
            $table->string('recipient_type')->nullable();
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('email')->index();
            $table->string('category')->index();
            $table->string('status')->default('queued')->index(); // queued, sent, delivered, bounced, complained, failed, skipped_unsubscribed
            $table->string('provider_message_id')->nullable()->index();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'email']);
        });

        Schema::create('comms_email_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->json('preferences')->nullable();
            $table->boolean('unsubscribed_all')->default(false)->index();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('unsubscribe_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('comms_email_suppressions', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('reason')->default('hard_bounce'); // hard_bounce, spam_complaint, manual_block
            $table->json('provider_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comms_email_suppressions');
        Schema::dropIfExists('comms_email_preferences');
        Schema::dropIfExists('comms_email_deliveries');
        Schema::dropIfExists('comms_email_campaigns');
        Schema::dropIfExists('comms_email_templates');
    }
};
