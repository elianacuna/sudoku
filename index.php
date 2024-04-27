<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sudoku</title>
    <link rel="stylesheet" href="./styles/styles.css">
</head>

<body>
    <div id="sudokuContainer">
    </div>
    <div>
        <label for="poblacion">Población:</label>
        <input type="number" class="inputCell" id="poblacion"><br>
        <label for="generaciones">Generaciones:</label>
        <input type="number" class="inputCell" id="generaciones"><br>
        <label for="mutacion">Mutación:</label>
        <input type="number" class="inputCell" id="mutacion"><br>
        <label for="seleccion">Selección:</label>
        <input type="number" class="inputCell" id="seleccion"><br>
        <label for="cruce">Cruce:</label>
        <input type="number" class="inputCell" id="cruce" maxlength="1"><br>
        <button class="buttonBtn" id="btnSolucionar" onclick="solucionarSudoku()">Solucionar</button>
    </div>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script>
    $(document).ready(async function () {
        try {
            const sudoku = await obtenerSudoku(1);
            if (sudoku !== null) {
                mostrarSudoku(sudoku);
            } else {
                alert('Error al obtener el Sudoku.');
            }
        } catch (error) {
            console.error('Error al obtener el Sudoku: ' + error);
            alert('Error al obtener el Sudoku.');
        }
    });

    // Mandamos a traer un sudoku, por defecto el 1 es el facil
    async function obtenerSudoku(opcion) {
        try {
            const response = await $.ajax({
                url: 'obtener_sudoku.php',
                type: 'GET',
                dataType: 'json',
                data: {
                    opcion: opcion
                }
            });

            if (response.success) {
                return response.sudoku;
            } else {
                console.error('Error al obtener el Sudoku.');
                return null;
            }
        } catch (error) {
            console.error('Error al realizar la petición AJAX: ' + error);
            return null;
        }
    }

    function solucionarSudoku() {
        let opcion = 1;
        let poblacion = $('#poblacion').val();
        let generaciones = $('#generaciones').val();
        let mutacion = $('#mutacion').val();
        let seleccion = $('#seleccion').val();
        let cruce = $('#cruce').val();

        $.ajax({
            url: 'solucion.php',
            type: 'GET',
            dataType: 'json',
            data: {
                opcion: opcion,
                poblacion: poblacion,
                generaciones: generaciones,
                mutacion: mutacion,
                seleccion: seleccion,
                cruce: cruce
            },
            success: function (response) {
                // Manejar la respuesta del servidor
                if (response.success) {
                    // Mostrar el Sudoku en la página
                    mostrarSudoku(response.sudoku);
                } else {
                    alert('Error al solucionar el Sudoku.');
                }
            },
            error: function (xhr, status, error) {
                console.error(error, xhr, status);
            }
        });
    }

    // Función para mostrar el Sudoku en la página
    function mostrarSudoku(sudoku) {
        var sudokuHTML = '<table>';
        sudokuHTML += '<caption>SUDOKU</caption>';

        for (var i = 0; i < sudoku.length; i++) {
            sudokuHTML += '<tbody>';
            if (i % 3 === 0) {
                sudokuHTML += '<tr>';
            }
            for (var j = 0; j < sudoku[i].length; j++) {
                var valor = sudoku[i][j] !== '' ? sudoku[i][j] : '&nbsp;';
                var claseCelda = sudoku[i][j] !== 0 ? 'filled-cell' : ''; // Agrega la clase 'filled-cell' si hay un número en la celda
                sudokuHTML += '<td class="' + claseCelda + '">' + (valor == 0 ? "" : valor) + '</td>';
                if (valor !== '&nbsp;') {
                    $('#cell' + (i * 9 + j + 1)).val(valor); // Actualiza el valor del input correspondiente
                }
            }
            sudokuHTML += '</tr>';
            if (i % 3 === 2) {
                sudokuHTML += '</tbody>';
            }
        }
        sudokuHTML += '</table>';

        $('#sudokuContainer').html(sudokuHTML);
    }
</script>

</html>