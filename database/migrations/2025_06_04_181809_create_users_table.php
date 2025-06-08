<?php

use LadyPHP\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('users');
    }
} 