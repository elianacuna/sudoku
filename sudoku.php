<?php

$tablero = null; 

if (isset($_POST['Jugar'])) {
    $mutaciones = $_POST['mutaciones'];
    $poblacion = $_POST['poblacion'];
    $cruzamiento = $_POST['cruzamiento'];

    $sudoku = new SudokuGenetico($mutaciones, $poblacion, $cruzamiento);
    $sudoku->generarTableroCompleto();
    $tablero = $sudoku->getTablero();


    
}


class SudokuGenetico {

    // Variable para el tablero de juego
    private $tablero;
    private $mutaciones;
    private $poblacion;
    private $cruzamiento;

    // Constructor de la clase
    public function __construct($mutaciones, $poblacion, $cruzamiento) {
        $this->mutaciones = $mutaciones;
        $this->poblacion = $poblacion;
        $this->cruzamiento = $cruzamiento;

        //  array de 9*9 
        $this->tablero = array_fill(0, 9, array_fill(0, 9, 0));
    }

    // tablero sudoku aleatorio
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

    // Genera tablero del sudoku completo
    public function generarTableroCompleto() {
        $nums = range(1, 9);
        shuffle($nums);
        $this->llenarTablero(0, 0, $nums);
    }

    // Llena recursivamente el tablero sudoku
    private function llenarTablero($fila, $columna, $nums) {
        if ($fila == 9) {
            return true; // Se completó todo el tablero
        }

        $siguienteFila = ($columna == 8) ? $fila + 1 : $fila;
        $siguienteColumna = ($columna == 8) ? 0 : $columna + 1;

        foreach ($nums as $num) {
            if ($this->esNumeroValido($fila, $columna, $num)) {
                $this->tablero[$fila][$columna] = $num;
                if ($this->llenarTablero($siguienteFila, $siguienteColumna, $nums)) {
                    return true; // Se completó todo el tablero
                }
                $this->tablero[$fila][$columna] = 0; 
            }
        }

        return false; // No se pudo completar el tablero
    }

    // Verifica si el número es válido en una posición específica
    private function esNumeroValido($fila, $columna, $num) {
        for ($i = 0; $i < 9; $i++) {
            if ($this->tablero[$fila][$i] == $num || $this->tablero[$i][$columna] == $num) {
                return false; // Número repetido en fila o columna
            }
        }

        $inicioFila = $fila - ($fila % 3);
        $inicioColumna = $columna - ($columna % 3);
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                if ($this->tablero[$inicioFila + $i][$inicioColumna + $j] == $num) {
                    return false; // Número repetido en cuadrado 3x3
                }
            }
        }

        return true; // Número válido en esta posición
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

    // Calcula la aptitud de (fila, columna o región)
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


    // Selección por torneo
    private function seleccionTorneo($tamanoTorneo) {
        $seleccionados = array();
        for ($i = 0; $i < $this->poblacion; $i++) {
            $indices = array_rand($this->tablero, $tamanoTorneo);
            $mejor = null;
            foreach ($indices as $indice) {
                if ($mejor === null || $this->calcularAptitudGrupo($this->tablero[$indice]) > $this->calcularAptitudGrupo($this->tablero[$mejor])) {
                    $mejor = $indice;
                }
            }
            $seleccionados[] = $this->tablero[$mejor];
        }
        return $seleccionados;
    }

    // Selección por ruleta
    private function seleccionRuleta() {
        $aptitudes = array();
        foreach ($this->tablero as $individuo) {
            $aptitudes[] = $this->calcularAptitudGrupo($individuo);
        }
        $totalAptitudes = array_sum($aptitudes);
        $probabilidades = array_map(function ($aptitud) use ($totalAptitudes) {
            return $aptitud / $totalAptitudes;
        }, $aptitudes);
        $seleccionados = array();
        for ($i = 0; $i < $this->poblacion; $i++) {
            $r = mt_rand() / mt_getrandmax();
            $suma = 0;
            foreach ($probabilidades as $indice => $probabilidad) {
                $suma += $probabilidad;
                if ($suma >= $r) {
                    $seleccionados[] = $this->tablero[$indice];
                    break;
                }
            }
        }
        return $seleccionados;
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sudoku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="css/sudoku.css">
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

    
    <form action="" method="post">
        <label for="mutaciones">Cantidad de mutaciones:</label>
        <input type="number" id="mutaciones" name="mutaciones" required>

        <label for="poblacion">Tamaño de la población:</label>
        <input type="number" id="poblacion" name="poblacion" required>

        <label for="cruzamiento">Método de cruzamiento:</label>
        <select id="cruzamiento" name="cruzamiento" required>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
        </select>

        <div class="d-grid gap-2 d-md-block topcss">
            <button class="btn btn-primary" name="Jugar" type="submit">Comenzar juego</button>
        </div>
    </form>

    <table>
    <?php if ($tablero !== null): ?>
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
    <?php endif; ?>
</table>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</body>
</html>

