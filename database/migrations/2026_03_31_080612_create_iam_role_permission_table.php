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
        Schema::create('iam_role_permission', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('iam_roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('iam_permissions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iam_role_permission');
    }
};
