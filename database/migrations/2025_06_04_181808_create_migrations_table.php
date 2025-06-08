<?php

use LadyPHP\Database\Migrations\Migration;

class CreateMigrationsTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('migrations', function ($table) {
            $table->id();
            $table->string('migration')->unique();
            $table->integer('batch');
            $table->timestamp('executed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        if ($this->schema->hasTable('migrations')) {
            $this->schema->drop('migrations');
        }
    }
} 