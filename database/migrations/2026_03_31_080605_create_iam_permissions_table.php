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
        Schema::create('iam_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // The unique identifier (e.g., 'invoice.approve')
            $table->string('name');           // The display name (e.g., 'Approve Invoice')
            $table->string('resource')->nullable(); 
            $table->string('action')->nullable();   
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iam_permissions');
    }
};
