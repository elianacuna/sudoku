<?php

ini_set('memory_limit', '-1');
include_once('sudokus.php');
include_once('algoritmo_sudoku.php');

$opcion = !empty($_GET['opcion']) ? $_GET['opcion'] : 1;


$opcion = isset($_GET['opcion']) ? $_GET['opcion'] : 1;
$poblacion = isset($_GET['poblacion']) ? $_GET['poblacion'] : 500;
$generaciones = isset($_GET['generaciones']) ? $_GET['generaciones'] : 1000;
$mutacion = isset($_GET['mutacion']) ? $_GET['mutacion'] : 50;
$seleccion = isset($_GET['seleccion']) ? $_GET['seleccion'] : 3;
$cruce = isset($_GET['cruce']) ? $_GET['cruce'] : 1;

// Llamar a la función main con los parámetros recibidos
main($opcion, $poblacion, $generaciones, $mutacion, $seleccion, $cruce);


function main($opcion, $poblacionTotal, $numeroGeneraciones, $generacionesParaMutacion, $tipoSeleccion, $tipoCruce)
{

    // TIPO SELECCION: 1 -> ELITISTA, 2 -> POR TORNEO, 3 -> ALEATORIO
    $tipoSeleccion = 3;

    // TIPO CRUCE 1 -> UN PUNTO, 2 -> DOS PUNTOS, 3 -> UNIFORME
    $tipoCruce = 1;

    $puntuacionPerfecta = 81;
    $mejorPuntuacion = 0;
    $conteoGeneraciones = 1;

    $individuo = new Individuo();

    // Se obtiene el sudoku, este devuelve una matriz
    $sudokuOrigen = sudokusFaciles($opcion);

    // el algoritmo soloa cepta un vector unidimencional, se convierte la matriz en un vector
    $sudoku = array_merge(...$sudokuOrigen);

    //Inicializamos la poblacion con el numero de la poblacion y el sudoku de entrada
    $poblacion = inicializarPoblacion($sudoku, $poblacionTotal);

    // Realizamos repetidamente el proceso genetico para hallar la solucion:
    while ($mejorPuntuacion < $puntuacionPerfecta & $conteoGeneraciones < $numeroGeneraciones) {

        // echo "\n";
        // echo "Generacion: $conteoGeneraciones";
        // echo "\n";

        //Se guarda el mejor individuo de cada generacion

        $individuoAux = obtenerMejorIndividuo($poblacion);
        $mejorPuntuacion = $individuoAux->getPuntuacion();

        // Se guarda la mejor puntuacion alcanzada, en caso de que la puntuacion por generacion baje
        if ($individuo->getPuntuacion() < $individuoAux->getPuntuacion()) {
            $individuo = $individuoAux;
        }

        // // Imprimimos la puntuacion del individuo por cada generacion
        // echo "Mejor Puntuacion: " . round($mejorPuntuacion, 2);

        // Se seleccionan los individuos a reproducirse segun el metodo elegido
        $padres = seleccionaPadres($poblacion, $tipoSeleccion);

        if (count($padres) % 2 !== 0) {
            // Si la cantidad de padres es impar, eliminar uno de ellos para que quede una cantidad par
            array_pop($padres);
        }

        // los individuos seleccionados, se mandan a reproducir, segun el cruce especificado
        $hijos = reproducirPoblacion($padres, $tipoCruce);

        // Se agregan los hijos a la poblacion inicial
        $poblacion = array_merge($poblacion, $hijos);

        // Se espera la mutacion a cada (valor de la variable $generacionesParaMutacion)
        if ($conteoGeneraciones % $generacionesParaMutacion == 0) {
            mutarPoblacion($poblacion);
        }

        //Evaluamos la poblacion
        evaluarPoblacion($poblacion);

        // Mandamos a reducir la poblacion, esto se dara solo si ya haya superado por el doble la poblacion inicial
        // la condicion se encuentra dentro del metodo
        $poblacion = reducirPoblacion($poblacion, $poblacionTotal);
        $conteoGeneraciones++;

    }

    
    // Al final, cuando tengas la solución del Sudoku, debes devolverla como un JSON válido
    $posibleSolucion = $individuo->getValores();
    echo json_encode(['success' => true, 'sudoku' => convertirAMatriz($posibleSolucion, 9)]);

    
}

function convertirAMatriz($array, $columnas) {
    $matriz = array();

    // Dividir el array en segmentos de tamaño igual al número de columnas
    $filas = array_chunk($array, $columnas);

    // Agregar cada segmento como una fila en la matriz
    foreach ($filas as $fila) {
        $matriz[] = $fila;
    }

    return $matriz;
}
