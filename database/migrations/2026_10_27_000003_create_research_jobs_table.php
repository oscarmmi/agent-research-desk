<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_jobs', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->longText('brief')->nullable();
            $table->string('status')->default('queued')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_jobs');
    }
};
