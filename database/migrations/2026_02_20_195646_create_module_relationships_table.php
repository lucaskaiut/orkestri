<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnDelete();
            $table->string('type');
            $table->unsignedBigInteger('related_module');
            $table->foreign('related_module')
                ->references('id')
                ->on('modules')
                ->cascadeOnDelete();
            $table->string('foreign_key')->nullable();
            $table->string('owner_key')->default('id');
            $table->string('relation_name')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_relationships');
    }
};