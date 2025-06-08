<?php

namespace LadyPHP\Database\Migrations;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->createTable('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_admin')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        $this->dropTable('users');
    }
} 