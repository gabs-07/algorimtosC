<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 150);
            $table->text('descripcion')->nullable();
            $table->unsignedInteger('orden');
            $table->boolean('publicada')->default(false);
        });

        Schema::create('lecciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidad_id')->constrained('unidades')->cascadeOnDelete();
            $table->string('titulo', 150);
            $table->longText('contenido');
            $table->unsignedInteger('orden');
        });

        Schema::create('ejercicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leccion_id')->constrained('lecciones')->cascadeOnDelete();
            $table->enum('tipo', ['opcion_multiple', 'completar', 'ordenar']);
            $table->text('enunciado');
            $table->text('codigo_base')->nullable();
            $table->unsignedInteger('orden');
            $table->boolean('publicado')->default(false);
        });

        Schema::create('opciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ejercicio_id')->constrained('ejercicios')->cascadeOnDelete();
            $table->text('texto');
            $table->boolean('es_correcta')->nullable();
            $table->unsignedInteger('posicion_correcta')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opciones');
        Schema::dropIfExists('ejercicios');
        Schema::dropIfExists('lecciones');
        Schema::dropIfExists('unidades');
    }
};
