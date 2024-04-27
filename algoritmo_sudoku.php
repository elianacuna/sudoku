<?php

include('individuo.php');



function obtenerMejorIndividuo($poblacion)
{
    return $poblacion[0];
}

function inicializarPoblacion($sudoku, $poblacionTotal)
{
    $poblacion = array(); // Crear un array para almacenar la población inicial

    for ($i = 0; $i < $poblacionTotal; $i++) {
        $individuo = new Individuo(); // Crear un nuevo individuo
        $individuo->inicializarValores($sudoku); // Inicializar los valores del individuo
        $individuo->llenarPosicionesOcupadas($sudoku);
        // $individuo->visualizarIndividuo();
        $individuo->evaluarIndividuo();
        //echo "$individuo->puntuacion";
        //echo"<br>";// Llenar las posiciones ocupadas del individuo
        $poblacion[] = $individuo; // Agregar el individuo a la población
    }

    return $poblacion; // Devolver la población inicial
}

function evaluarPoblacion(&$poblacion)
{
    foreach ($poblacion as $individuo) {
        $individuo->evaluarIndividuo();
    }
}

function seleccionaPadres($poblacion, $tipoSeleccion)
{

    $padresSeleccionados = array();

    switch ($tipoSeleccion) {
        case 1:
            $padresSeleccionados = seleccionaPadresElitista($poblacion);
            break;
        case 2:
            $padresSeleccionados = seleccionaPadresPorTorneo($poblacion);
            break;
        case 3:
            $padresSeleccionados = seleccionaPadresAleatorio($poblacion);
            break;
        default:
            break;
    }

    return $padresSeleccionados;
}

function seleccionaPadresElitista($poblacion)
{
    $parejas = array();
    ordenarPoblacionDescendente($poblacion);

    // Seleccionar padres por parejas
    for ($i = 0; $i < count($poblacion); $i += 2) {
        if ($i + 1 < count($poblacion)) {
            $padre1 = $poblacion[$i];
            $padre2 = $poblacion[$i + 1];
            $parejas[] = array($padre1, $padre2);
        }
    }

    return $parejas;
}

function seleccionaPadresAleatorio($poblacion)
{
    $parejas = array();

    // Obtener una lista de índices únicos de la población
    $indices = array_keys($poblacion);

    // Barajar los índices de manera aleatoria
    shuffle($indices);

    // Iterar sobre los índices barajados y seleccionar parejas de padres
    for ($i = 0; $i < count($indices); $i += 2) {
        $padre1 = $poblacion[$indices[$i]];
        
        // Verificar si hay otro índice disponible para seleccionar el segundo padre
        if ($i + 1 < count($indices)) {
            $padre2 = $poblacion[$indices[$i + 1]];
            $parejas[] = array($padre1, $padre2);
        }
    }

    return $parejas;
}


function seleccionaPadresPorTorneo($poblacion) {
    $parejas = array();

    // Tamaño del torneo dinámico
    $tamanoTorneo = max(2, intval(0.1 * (count($poblacion)/2)));

    while (count($parejas) < count($poblacion) / 2) {
        // Seleccionar dos padres por torneo
        $padre1 = seleccionarIndividuoPorTorneo($poblacion, $tamanoTorneo);
        $padre2 = seleccionarIndividuoPorTorneo($poblacion, $tamanoTorneo);

        // Verificar si alguno de los padres es nulo o son el mismo individuo
        if ($padre1 !== null && $padre2 !== null && $padre1 !== $padre2) {
            $parejas[] = array($padre1, $padre2);
        }
    }

    return $parejas;
}

function seleccionarIndividuoPorTorneo($poblacion, $tamanoTorneo) {
    // Seleccionar aleatoriamente $tamanoTorneo individuos para el torneo
    $individuosTorneo = array_rand($poblacion, min($tamanoTorneo, count($poblacion)));

    // Encontrar al mejor individuo dentro del torneo
    $mejorIndividuo = null;
    foreach ($individuosTorneo as $indice) {
        $individuo = $poblacion[$indice];
        if ($mejorIndividuo === null || $individuo->getPuntuacion() > $mejorIndividuo->getPuntuacion()) {
            $mejorIndividuo = $individuo;
        }
    }

    return $mejorIndividuo;
}

function ordenarPoblacionDescendente(&$poblacion)
{
    usort($poblacion, function ($a, $b) {
        return $b->puntuacion - $a->puntuacion;
    });
}

function reproducirPoblacion($padres, $opcion)
{
    $hijos = array();

    // Cruzar cada pareja de padres y generar hijos
    foreach ($padres as $padre) {
        $padre1 = $padre[0];
        $padre2 = $padre[1];
        switch ($opcion) {
            case 1:
                $hijo = crucePorUnPunto($padre1, $padre2);
                break;
            case 2:
                $hijo = crucePorDosPuntos($padre1, $padre2);
                break;
            case 3:
                $hijo = cruceUniforme($padre1, $padre2);
                break;
        }
        $hijos[] = $hijo;
    }


    return $hijos;
}

function crucePorUnPunto($padre1, $padre2)
{
    $puntoCruce = mt_rand(1, count($padre1->valores) - 1);

    // Generar el primer hijo (1a mitad del padre, 2a mitad de la madre)
    $hijo1Valores = array_merge(
        array_slice($padre1->valores, 0, $puntoCruce),
        array_slice($padre2->valores, $puntoCruce)
    );

    // Generar el segundo hijo (1a mitad de la madre, 2a mitad del padre)
    $hijo2Valores = array_merge(
        array_slice($padre2->valores, 0, $puntoCruce),
        array_slice($padre1->valores, $puntoCruce)
    );

    // Crear los dos hijos
    $hijo1 = new Individuo();
    $hijo2 = new Individuo();

    // Asignar los valores a los hijos
    $hijo1->valores = $hijo1Valores;
    $hijo2->valores = $hijo2Valores;

    // Evaluar los hijos
    $hijo1->evaluarIndividuo();
    $hijo2->evaluarIndividuo();

    // Devolver el mejor hijo (el que tenga la mayor puntuación)
    return $hijo1->puntuacion >= $hijo2->puntuacion ? $hijo1 : $hijo2;
}

function crucePorDosPuntos($padre1, $padre2)
{
    // Seleccionar dos puntos de cruce aleatorios
    $puntoCruce1 = mt_rand(1, count($padre1->valores) - 1);
    $puntoCruce2 = mt_rand(1, count($padre1->valores) - 1);

    // Asegurar que los puntos de cruce sean diferentes
    while ($puntoCruce1 == $puntoCruce2) {
        $puntoCruce2 = mt_rand(1, count($padre1->valores) - 1);
    }

    // Ordenar los puntos de cruce
    $puntoInicio = min($puntoCruce1, $puntoCruce2);
    $puntoFin = max($puntoCruce1, $puntoCruce2);

    // Crear los dos hijos
    $hijo1 = new Individuo();
    $hijo2 = new Individuo();

    // Generar el primer hijo
    $hijo1->valores = array_merge(
        array_slice($padre1->valores, 0, $puntoInicio),
        array_slice($padre2->valores, $puntoInicio, $puntoFin - $puntoInicio),
        array_slice($padre1->valores, $puntoFin)
    );

    // Generar el segundo hijo
    $hijo2->valores = array_merge(
        array_slice($padre2->valores, 0, $puntoInicio),
        array_slice($padre1->valores, $puntoInicio, $puntoFin - $puntoInicio),
        array_slice($padre2->valores, $puntoFin)
    );

    // Evaluar los hijos
    $hijo1->evaluarIndividuo();
    $hijo2->evaluarIndividuo();

    // Devolver el mejor hijo (el que tenga la mayor puntuación)
    return $hijo1->puntuacion >= $hijo2->puntuacion ? $hijo1 : $hijo2;
}

function cruceUniforme($padre1, $padre2)
{
    // Crear los dos hijos
    $hijo1 = new Individuo();
    $hijo2 = new Individuo();

    // Alternar los genes de los padres para generar los hijos
    $genesPadre1 = $padre1->valores;
    $genesPadre2 = $padre2->valores;

    // Iterar sobre los genes de los padres
    for ($i = 0; $i < count($genesPadre1); $i++) {
        // Usar operador ternario para asignar los genes a los hijos de forma alternada
        $hijo1Valores[] = ($i % 2 == 0) ? $genesPadre1[$i] : $genesPadre2[$i];
        $hijo2Valores[] = ($i % 2 == 0) ? $genesPadre2[$i] : $genesPadre1[$i];
    }

    // Asignar los valores a los hijos
    $hijo1->valores = $hijo1Valores;
    $hijo2->valores = $hijo2Valores;

    // Evaluar los hijos
    $hijo1->evaluarIndividuo();
    $hijo2->evaluarIndividuo();

    // Devolver el mejor hijo (el que tenga la mayor puntuación)
    return $hijo1->puntuacion >= $hijo2->puntuacion ? $hijo1 : $hijo2;
}

function mutarPoblacion(&$poblacion)
{
    foreach ($poblacion as $individuo) {
        $individuo->mutar();
    }
}

// Metodo para reducir la poblacion.
function reducirPoblacion($poblacion, $tamanioInicial)
{
    $tamanioActual = count($poblacion);

    // Si la población actual es el doble de tamaño inicial 
    if ($tamanioActual <= $tamanioInicial * 2) {
        return $poblacion;
    }

    // Ordenar la población por puntuación de manera ascendente
    usort($poblacion, function ($a, $b) {
        return $a->puntuacion - $b->puntuacion;
    });

    // Eliminar los individuos con menor puntuación hasta que la población alcance el tamaño deseado
    while (count($poblacion) > $tamanioInicial) {
        array_shift($poblacion); // Eliminar el primer elemento (menor puntuación)
    }

    return $poblacion;
}

function visualizarPoblacion($poblacion)
{
    foreach ($poblacion as $indice => $individuo) {
        echo "Individuo " . ($indice + 1) . "\n";
        echo "Puntuación: " . $individuo->puntuacion . "\n";
        echo "Valores: " . implode(", ", $individuo->valores) . "\n";
        echo "----------------------\n";
    }
}

function imprimirParejasPadres($parejas)
{
    foreach ($parejas as $indice => $pareja) {
        $padre1 = $pareja[0];
        $padre2 = $pareja[1];
        echo "Pareja " . ($indice + 1) . "<br>";
        echo "Padre 1:<br>";
        echo "Puntuación: " . $padre1->puntuacion . "<br>";
        echo "Valores: " . implode(", ", $padre1->valores) . "<br>";
        echo "Padre 2:<br>";
        echo "Puntuación: " . $padre2->puntuacion . "<br>";
        echo "Valores: " . implode(", ", $padre2->valores) . "<br>";
        echo "----------------------<br>";
    }
}

