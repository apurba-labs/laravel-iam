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
            /**
             * Change: Removed ->constrained('iam_scopes')
             * We use a raw unsignedBigInteger so it can represent ANY ID 
             * (Branch, Dept, Store) without requiring a specific table.
             */
            $table->unsignedBigInteger('scope_id')->nullable()->index();
            
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('iam_user_role');
        Schema::enableForeignKeyConstraints();
    }
};
