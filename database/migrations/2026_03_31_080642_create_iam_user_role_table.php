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
        Schema::create('iam_user_role', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->foreignId('role_id')->constrained('iam_roles')->cascadeOnDelete();
            $table->foreignId('scope_id')->nullable()->constrained('iam_scopes')->nullOnDelete();
            
            // Relationships
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            
            // Prevent duplicate assignments
            $table->unique(['user_id', 'role_id', 'scope_id'], 'user_role_scope_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iam_user_role');
    }
};
