<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ejercicio_id')->constrained('ejercicios')->cascadeOnDelete();
            $table->text('respuesta');
            $table->boolean('es_correcto');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('desbloqueos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('alumno_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('unidad_id')->constrained('unidades')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['alumno_id', 'unidad_id']);
        });

        Schema::create('logros', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('descripcion', 255);
            $table->string('condicion', 50);
            $table->unsignedInteger('valor')->nullable();
        });

        Schema::create('logro_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logro_id')->constrained('logros')->cascadeOnDelete();
            $table->foreignId('alumno_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('obtenido_at')->useCurrent();
            $table->unique(['logro_id', 'alumno_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logro_user');
        Schema::dropIfExists('logros');
        Schema::dropIfExists('desbloqueos');
        Schema::dropIfExists('intentos');
    }
};
