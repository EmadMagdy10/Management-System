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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('technician_id');
            $table->text('description');
            $table->date('start_date');
            $table->date('due_date');
            $table->text('note_to_clients');
            $table->string('client_email');
            $table->enum('status', ['Open', 'In Progress', 'Completed', 'Closed', 'Rejected']);
            $table->foreign('technician_id')->references('id')->on('technicians')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};