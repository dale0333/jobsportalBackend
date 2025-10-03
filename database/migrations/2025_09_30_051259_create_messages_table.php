<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Chats Table
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('chat_users');
            $table->integer('unseen_msgs')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Messages Table
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_id');
            $table->unsignedBigInteger('sender_id');
            $table->text('message');
            $table->timestamp('time')->useCurrent();
            $table->timestamps();

            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Message Statuses Table
        Schema::create('message_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->boolean('is_sent')->default(false);
            $table->boolean('is_delivered')->default(false);
            $table->boolean('is_seen')->default(false);
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_statuses');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('chats');
    }
};
