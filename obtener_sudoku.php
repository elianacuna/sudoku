<?php

// Se realiza por aparte la obtencion de los sudokus, ya que para resolver no se enviara
// el sudoku como tal, si no solo el numero de la opcion.

include_once('sudokus.php');
$opcion = isset($_GET['opcion']) ? $_GET['opcion'] : 1;
main($opcion);


function main($opcion)
{
    switch ($opcion) {
        case 1:
            
            $sudokuOrigen = sudokusFaciles(1);
            // Devolver el Sudoku como JSON
            echo json_encode(['success' => true, 'sudoku' => $sudokuOrigen]);
            break;
            
            // Agregar mas casos para devolver sudokus faciles, intermedios o dificiles
        default:
            break;
    }
}
