<?php

class Individuo
{
    public $puntuacion;
    public $valores;
    public $posiciones_inmutables;

    public function __construct()
    {
        $this->puntuacion = 0;
        $this->valores = array_fill(0, 81, 0); // Array de 81 posiciones inicializado a 0
        $this->posiciones_inmutables = array(); // Array dinámico de posiciones ocupadas
    }

    function setValores($valores)
    {
        $this->valores = $valores;
    }

    function getValores()
    {
        return $this->valores;
    }

    function getPuntuacion()
    {
        return $this->puntuacion;
    }

    function inicializarValores($sudoku)
    {
        $this->llenarPosicionesOcupadas($sudoku);
        for ($i = 0; $i < count($sudoku); $i++) {
            if ($sudoku[$i] != 0) {
                // Si el valor del sudoku no es cero, se asigna directamente
                $this->valores[$i] = $sudoku[$i];
            } else {
                // Si es cero, se asigna un número aleatorio entre 1 y 9
                $this->valores[$i] = mt_rand(1, 9); 
            }
        }
    }

    function llenarPosicionesOcupadas($sudoku)
    {
        for ($i = 0; $i < count($sudoku); $i++) {
            if ($sudoku[$i] != 0) {
                $this->posiciones_inmutables[] = $i; // Agregar la posición al array de posiciones ocupadas
            }
        }
    }

    // En este metodo de llevan a cabo las 3 evaluaciones con las reglas basicas de sudoku
    public function evaluarIndividuo()
    {
        $puntuacionTotal = 0;

        // Evaluar por filas y columnas
        for ($i = 0; $i < 9; $i++) {
            $filaSudoku = array_slice($this->valores, $i * 9, 9); // Obtener una fila del sudoku
            $columnaSudoku = array(); // Array para almacenar la columna actual
            for ($j = 0; $j < 9; $j++) {
                $indice = $j * 9 + $i; // Calcular el índice correspondiente a la columna actual
                $columnaSudoku[] = $this->valores[$indice]; // Agregar el valor al array de la columna
            }
            $puntuacionTotal += $this->calcularPuntuacionRegla($filaSudoku);
            $puntuacionTotal += $this->calcularPuntuacionRegla($columnaSudoku);
        }

        // Evaluar por regiones de 3x3
        for ($filaRegion = 0; $filaRegion < 3; $filaRegion++) {
            for ($columnaRegion = 0; $columnaRegion < 3; $columnaRegion++) {

                // Array para almacenar la región actual
                $regionSudoku = array(); 
                for ($i = 0; $i < 3; $i++) {
                    for ($j = 0; $j < 3; $j++) {
                        // Calcular el índice correspondiente a la región actual
                        $indice = ($filaRegion * 3 + $i) * 9 + $columnaRegion * 3 + $j;
                        $regionSudoku[] = $this->valores[$indice];
                    }
                }
                $puntuacionTotal += $this->calcularPuntuacionRegla($regionSudoku);
            }
        }

        // Asignar la puntuación total al individuo
        $this->puntuacion = $puntuacionTotal / 3;
    }

    /* Metodo auxiliar para contar los numeros unicos */
    private function calcularPuntuacionRegla($grupo)
    {
        // Contar la cantidad de números únicos en el grupo (fila, columna o región)
        $numerosUnicos = count(array_unique($grupo));

        // Devolver la cantidad de números únicos como puntuación del grupo
        return $numerosUnicos;
    }

    public function mutar()
    {
        do {
            // Seleccionar aleatoriamente una posición para mutar
            $posicionMutar = mt_rand(0, count($this->valores) - 1);
        } while (in_array($posicionMutar, $this->posiciones_inmutables));

        // Generar un nuevo valor aleatorio para la posición seleccionada
        $nuevoValor = mt_rand(1, 9);

        // Mutar el individuo cambiando el valor en la posición seleccionada
        $this->valores[$posicionMutar] = $nuevoValor;
    }

    /* La visualizacion del invididuo, solo se lleva a cavo con este metodo si se esta trabajando en consola */
    public function visualizarIndividuo()
    {
        for ($i = 0; $i < count($this->valores); $i++) {
            echo $this->valores[$i] . " ";
            if (($i + 1) % 9 == 0 && $i + 1 != count($this->valores)) {
                echo "\n"; // Salto de línea después de cada fila de 9 elementos
            }
        }
        echo "\n";
        echo "\n";
    }

    /* Con este metodo se visualizan las posiciones ocupadas, solo si se esta trabajando en consola */
    public function visualizarPosicionesOcupadas()
    {
        echo "<br>";
        for ($i = 0; $i < count($this->posiciones_inmutables); $i++) {
            echo $this->posiciones_inmutables[$i] . " ";
        }
        echo "<br>";
    }

}