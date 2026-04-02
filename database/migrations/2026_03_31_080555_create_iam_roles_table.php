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
        Schema::create('iam_roles', function (Blueprint $table) {
            $table->id();
        
            // The display name (e.g., "Inventory Manager")
            $table->string('name')->unique(); 
            
            // The programmatic identifier (e.g., "inventory-manager")
            $table->string('slug')->unique()->index(); 
            
            // The helper text for UI
            $table->text('description')->nullable(); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iam_roles');
    }
};
