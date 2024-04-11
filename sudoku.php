<?php

function generarSudoku() {
    
    if (isset($_POST['generar_tablero'])) {

        $sudoku = new SudokuGenetico();
        $sudoku->generarTableroAleatorio();
        $tablero = $sudoku->getTablero();
    }
    if (isset($_POST['resolver_sudoku'])){
        echo 'hola';
    }
}

class SudokuGenetico {
    
    private $tablero;
    

    public function __construct() {
        
        // Creando el tablero vacio
        $this->tablero = array_fill(0, 9, array_fill(0, 9, 0));
    }
    
    public function generarTableroAleatorio() {
        
        // Llenamos la diagonal principal
        for ($i = 0; $i < 9; $i += 3) {
            $this->llenarRegion($i, $i);
        }
    
        // Permutar números en cada fila, columna y región
        $this->permutarFilas();
        $this->permutarColumnas();
        $this->permutarRegiones();
    }
    
    private function llenarRegion($startRow, $startCol) {
        
        //Llenamos las regiones de las filas y columnas 9X9
        $nums = range(1, 9);
        shuffle($nums);
        $index = 0;
        for ($i = $startRow; $i < $startRow + 3; $i++) {
            for ($j = $startCol; $j < $startCol + 3; $j++) {
                $this->tablero[$i][$j] = $nums[$index++];
            }
        }
    }
    
    private function permutarFilas() {
        for ($i = 0; $i < 9; $i++) {
            shuffle($this->tablero[$i]);
        }
    }
    
    private function permutarColumnas() {
        for ($j = 0; $j < 9; $j++) {
            $column = array_column($this->tablero, $j);
            shuffle($column);
            foreach ($column as $index => $value) {
                $this->tablero[$index][$j] = $value;
            }
        }
    }
    
    private function permutarRegiones() {
        for ($i = 0; $i < 9; $i += 3) {
            for ($j = 0; $j < 9; $j += 3) {
                $this->permutarRegion($i, $j);
            }
        }
    }
    
    private function permutarRegion($startRow, $startCol) {
        $nums = [];
        for ($i = $startRow; $i < $startRow + 3; $i++) {
            for ($j = $startCol; $j < $startCol + 3; $j++) {
                $nums[] = $this->tablero[$i][$j];
            }
        }
        shuffle($nums);
        $index = 0;
        for ($i = $startRow; $i < $startRow + 3; $i++) {
            for ($j = $startCol; $j < $startCol + 3; $j++) {
                $this->tablero[$i][$j] = $nums[$index++];
            }
        }
    }
        public function calcularAptitud() {
        $aptitud = 0;
        
        // Calcula aptitud por filas
        for ($i = 0; $i < 9; $i++) {
            $aptitud += $this->calcularAptitudGrupo($this->tablero[$i]);
        }
        
        // Calcula aptitud por columnas
        for ($j = 0; $j < 9; $j++) {
            $columna = array();
            for ($i = 0; $i < 9; $i++) {
                $columna[] = $this->tablero[$i][$j];
            }
            $aptitud += $this->calcularAptitudGrupo($columna);
        }
        
        // Calcular aptitud por cuadrados de 3x3
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
    
    private function calcularAptitudGrupo($grupo) {
        $apariciones = array_fill(1, 9, 0);
        foreach ($grupo as $num) {
            if ($num != 0) {
                $apariciones[$num]++;
            }
        }
        return array_sum($apariciones);
    }
    
    public function getTablero() {
        return $this->tablero;
    }
}

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
            <button class="btn btn-primary" name="generar_tablero" type="submit">Generar tablero</button>
            <button class="btn btn-outline-success" name="resolver_sudoku" type="submit">Resolver</button>
            
        </div>    

    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    
</body>
</html>


