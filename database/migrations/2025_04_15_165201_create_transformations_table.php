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
        Schema::create('transformations', function (Blueprint $table) {
            $table->id();
            $table->string('image_name'); // Имя файла изображения
            $table->json('transformations'); // Применённые трансформации в формате JSON
            $table->string('output_image'); // Имя обработанного изображения
            $table->timestamps(); // Временные метки для created_at и updated_at
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transformations');
    }
};
