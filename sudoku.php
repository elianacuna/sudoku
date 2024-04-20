<?php

if (isset($_POST['Jugar'])) {
    $sudoku = new SudokuGenetico();
    $sudoku->generarTableroAleatorio();
    $tablero = $sudoku->getTablero();

    $aptitud = $sudoku->calcularAptitud();

    if ($aptitud == 81) {
        echo "El tablero Sudoku está completo y correcto.";
    } else {
        echo "El tablero Sudoku no está completo o tiene errores.";
    }
}

class SudokuGenetico {

    // Variable para el tablero de juego
    private $tablero;

    // Constructor de la clase
    public function __construct() {

        //Creando el array de 9*9 llenos de ceros
        $this->tablero = array_fill(0, 9, array_fill(0, 9, 0));
    }

    // Genera un tablero sudoku aleatorio
    public function generarTableroAleatorio() {
       
        // Llena la diagonal principal con números aleatorios
        for ($i = 0; $i < 9; $i += 3) {
            $this->llenarRegion($i, $i);
        }

        // Permuta las filas, columnas y regiones del tablero
        $this->permutarFilas();
        $this->permutarColumnas();
        $this->permutarRegiones();
    }

    // Llena una región 3x3 del tablero con números aleatorios
    private function llenarRegion($InicioFila, $InciaCol) {
        $nums = range(1, 9);
        shuffle($nums);
        $index = 0;
        for ($i = $InicioFila; $i < $InicioFila + 3; $i++) {
            for ($j = $InciaCol; $j < $InciaCol + 3; $j++) {
                $this->tablero[$i][$j] = $nums[$index++];
            }
        }
    }

    // Permuta las filas del tablero de forma aleatoria
    private function permutarFilas() {
        for ($i = 0; $i < 9; $i++) {
            shuffle($this->tablero[$i]);
        }
    }

    // Permuta las columnas del tablero de forma aleatoria
    private function permutarColumnas() {
        for ($j = 0; $j < 9; $j++) {
            $column = array_column($this->tablero, $j);
            shuffle($column);
            foreach ($column as $index => $value) {
                $this->tablero[$index][$j] = $value;
            }
        }
    }

    // Permuta las regiones 3x3 del tablero de forma aleatoria
    private function permutarRegiones() {
        for ($i = 0; $i < 9; $i += 3) {
            for ($j = 0; $j < 9; $j += 3) {
                $this->permutarRegion($i, $j);
            }
        }
    }

    // Permuta una región 3x3 del tablero de forma aleatoria
    private function permutarRegion($InicioFila, $InicioColum) {
        $nums = [];
        for ($i = $InicioFila; $i < $InicioFila + 3; $i++) {
            for ($j = $InicioColum; $j < $InicioColum + 3; $j++) {
                $nums[] = $this->tablero[$i][$j];
            }
        }
        shuffle($nums);
        $index = 0;
        for ($i = $InicioFila; $i < $InicioFila + 3; $i++) {
            for ($j = $InicioColum; $j < $InicioColum + 3; $j++) {
                $this->tablero[$i][$j] = $nums[$index++];
            }
        }
    }

    // Calcula la aptitud del tablero
    public function calcularAptitud() {
        $aptitud = 0;

        // Calcula la aptitud por filas
        for ($i = 0; $i < 9; $i++) {
            $aptitud += $this->calcularAptitudGrupo($this->tablero[$i]);
        }

        // Calcula la aptitud por columnas
        for ($j = 0; $j < 9; $j++) {
            $columna = array();
            for ($i = 0; $i < 9; $i++) {
                $columna[] = $this->tablero[$i][$j];
            }
            $aptitud += $this->calcularAptitudGrupo($columna);
        }

        // Calcula la aptitud por cuadrados de 3x3
        for ($i = 0; $i < 9; $i += 3) {
            for ($j = 0; $j < 9; $j += 3) {
                $cuadrado = array();
                for ($di = 0; $di < 3; $di++) {
                    for ($dj = 0; $dj < 3; $dj++) {
                        $cuadrado[] = $this->tablero[$i + $di][$j + $dj];
                    }
                }
                $aptitud += $this->calcularAptitudGrupo($cuadrado);
            }
        }

        return $aptitud;
    }

    // Calcula la aptitud de un grupo (fila, columna o región)
    private function calcularAptitudGrupo($grupo) {
        
        $apariciones = array_fill(1, 9, 0);
        
        foreach ($grupo as $num) {
            if ($num != 0) {
                $apariciones[$num]++;
            }
        }
        return array_sum($apariciones);
    }

    // Retorna el tablero generado
    public function getTablero() {
        return $this->tablero;
    }
}

// Crear una instancia de SudokuGenetico, generar un tablero aleatorio y obtenerlo
$sudoku = new SudokuGenetico();
$sudoku->generarTableroAleatorio();
$tablero = $sudoku->getTablero();




?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sudoku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/sudoku.css">
</head>
<body>
    
    <p class="title">Haber si me puedes ganar:
    
    <?php
    if (isset($_GET["nombre"])) {
        $nombre = $_GET["nombre"];
        if (!empty($nombre)) {
            echo htmlspecialchars($nombre);
        }
    }
    ?>
    </p>


    <table>

    <?php for ($i = 0; $i < 9; $i++): ?>
            <tr>
                <?php for ($j = 0; $j < 9; $j++): ?>
                    <?php $cell_value = $tablero[$i][$j]; ?>
                    <td class="<?php echo $cell_value == 0 ? 'blank' : ''; ?>">
                        <?php echo $cell_value == 0 ? '' : $cell_value; ?>
                    </td>
                <?php endfor; ?>
            </tr>
        <?php endfor; ?>


    </table>


    <form action="" method="post">
        
        <div class="d-grid gap-2 d-md-block topcss">
            <button class="btn btn-primary" name="Jugar" type="submit">Comenzar juego</button>
            
        </div>    

    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    
</body>
</html>


