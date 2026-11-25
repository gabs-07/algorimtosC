<?php

namespace Database\Seeders;

use App\Models\Ejercicio;
use App\Models\Leccion;
use App\Models\Unidad;
use Illuminate\Database\Seeder;

/**
 * Contenido de ejemplo: 2 unidades con lecciones y ejercicios de los 3 tipos.
 * Ejecutar con: php artisan db:seed --class=ContenidoSeeder
 */
class ContenidoSeeder extends Seeder
{
    public function run(): void
    {
        if (Unidad::exists()) {
            $this->command?->warn('Ya existen unidades; no se cargó el contenido de ejemplo.');

            return;
        }

        /* ---------------- Unidad 1 ---------------- */
        $u1 = Unidad::create([
            'titulo' => 'Introducción a C',
            'descripcion' => 'Estructura de un programa, salida por pantalla y tipos de datos básicos.',
            'orden' => 1,
            'publicada' => true,
        ]);

        $l11 = Leccion::create([
            'unidad_id' => $u1->id,
            'titulo' => 'Mi primer programa',
            'orden' => 1,
            'contenido' => <<<'MD'
## Estructura de un programa en C

Todo programa en C empieza en la función `main`. Para mostrar texto en pantalla usamos `printf`, que vive en la biblioteca `stdio.h`.

```c
#include <stdio.h>

int main(void) {
    printf("Hola mundo\n");
    return 0;
}
```

- `#include <stdio.h>` incluye la biblioteca de entrada y salida.
- `int main(void)` es el punto de entrada del programa.
- `\n` es un salto de línea.
- `return 0;` indica que el programa terminó bien.
MD,
        ]);

        $this->ejercicio($l11, 'opcion_multiple', 1, '¿Qué imprime este programa?',
            "#include <stdio.h>\n\nint main(void) {\n    printf(\"Hola\");\n    return 0;\n}",
            [['Hola', true], ['hola', false], ['"Hola" con comillas', false], ['Nada, da error', false]]);

        $this->ejercicio($l11, 'completar', 2, 'Escribe la línea que imprime Hola mundo seguido de un salto de línea.',
            "#include <stdio.h>\n\nint main(void) {\n    ___\n    return 0;\n}",
            ['printf("Hola mundo\n");']);

        $this->ejercicio($l11, 'ordenar', 3, 'Ordena las líneas para formar un programa que imprime Hola.', null,
            ['#include <stdio.h>', 'int main(void) {', '    printf("Hola\n");', '    return 0;', '}']);

        $l12 = Leccion::create([
            'unidad_id' => $u1->id,
            'titulo' => 'Variables y tipos de datos',
            'orden' => 2,
            'contenido' => <<<'MD'
## Variables

Una variable guarda un valor y tiene un tipo.

| Tipo | Uso |
|------|-----|
| `int` | números enteros |
| `float` | decimales |
| `char` | un carácter |

```c
int edad = 20;
float promedio = 8.5;
char letra = 'A';
```
MD,
        ]);

        $this->ejercicio($l12, 'opcion_multiple', 1, '¿Qué tipo de dato conviene para guardar la edad de una persona?', null,
            [['int', true], ['float', false], ['char', false], ['void', false]]);

        $this->ejercicio($l12, 'completar', 2, 'Completa la declaración de una variable entera llamada edad con el valor 20.',
            "___ edad = 20;",
            ['int']);

        /* ---------------- Unidad 2 ---------------- */
        $u2 = Unidad::create([
            'titulo' => 'Estructuras de control',
            'descripcion' => 'Condicionales y ciclos para controlar el flujo del programa.',
            'orden' => 2,
            'publicada' => true,
        ]);

        $l21 = Leccion::create([
            'unidad_id' => $u2->id,
            'titulo' => 'Condicionales if y else',
            'orden' => 1,
            'contenido' => <<<'MD'
## Tomar decisiones

`if` ejecuta un bloque solo cuando la condición es verdadera. `else` cubre el caso contrario.

```c
int x = 5;
if (x > 3) {
    printf("A");
} else {
    printf("B");
}
```

Operadores de comparación: `>`, `<`, `>=`, `<=`, `==`, `!=`.
MD,
        ]);

        $this->ejercicio($l21, 'opcion_multiple', 1, '¿Qué imprime este código?',
            "int x = 5;\nif (x > 3) {\n    printf(\"A\");\n} else {\n    printf(\"B\");\n}",
            [['A', true], ['B', false], ['AB', false], ['Nada', false]]);

        $this->ejercicio($l21, 'completar', 2, 'Escribe el operador que compara si x es mayor que 10.',
            "if (x ___ 10) {\n    printf(\"grande\");\n}",
            ['>']);

        $l22 = Leccion::create([
            'unidad_id' => $u2->id,
            'titulo' => 'El ciclo for',
            'orden' => 2,
            'contenido' => <<<'MD'
## Repetir instrucciones

El ciclo `for` repite un bloque un número conocido de veces.

```c
for (int i = 0; i < 3; i++) {
    printf("%d\n", i);
}
```

Imprime `0`, `1` y `2`.
MD,
        ]);

        $this->ejercicio($l22, 'ordenar', 1, 'Ordena las líneas para imprimir los números del 0 al 2.', null,
            ['for (int i = 0; i < 3; i++) {', '    printf("%d\n", i);', '}']);

        $this->ejercicio($l22, 'opcion_multiple', 2, '¿Cuántas veces se ejecuta el cuerpo de este ciclo?',
            "for (int i = 0; i < 5; i++) {\n    printf(\"*\");\n}",
            [['5 veces', true], ['4 veces', false], ['6 veces', false], ['Infinitas veces', false]]);
    }

    /**
     * $datos: opción múltiple → [[texto, esCorrecta], ...]
     *         completar       → [solución, ...]
     *         ordenar         → [línea en orden correcto, ...]
     */
    private function ejercicio(Leccion $leccion, string $tipo, int $orden, string $enunciado, ?string $codigo, array $datos): void
    {
        $e = Ejercicio::create([
            'leccion_id' => $leccion->id,
            'tipo' => $tipo,
            'enunciado' => $enunciado,
            'codigo_base' => $codigo,
            'orden' => $orden,
            'publicado' => true,
        ]);

        foreach ($datos as $i => $dato) {
            $e->opciones()->create(match ($tipo) {
                'opcion_multiple' => ['texto' => $dato[0], 'es_correcta' => $dato[1]],
                'completar' => ['texto' => $dato, 'es_correcta' => true],
                'ordenar' => ['texto' => $dato, 'posicion_correcta' => $i + 1],
            });
        }
    }
}
