<?php
/**
 * Script que genera llama una URL para generar un mapa de sombras de la
 * ciudad de Jaén a una fecha y hora indicadas por formulario.
 * 
 * {@internal Se puede usar un web service para obtener la latitud y longitud 
 *  de la ciudad, de esta manera se puede incluir un select en el formulario
 *  con un listado de ciudades.}
 * 
 * @package DAW05
 * @author David Vivo
 * @version 1.0
 * 
 */
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mapa de sombras de Jaén</title>
    </head>
    <body>
        <style>
            label { display: block;}
        </style>
        <h1>Mapa de sombras de Jaén. Indica la fecha y hora</h1>
        <h2>Modificación para Git</h2>
        <form action="" method="post">
            <label for="fecha_entrada">
                Fecha (dd/mm/aaaa):
                <input type="text" name="fecha_entrada" id="fecha_entrada" value="<?php if (isset($_POST['fecha_entrada'])) echo $_POST['fecha_entrada']; ?>">
            </label>
            <label for="hora_entrada">
                Hora (hh:mm):
                <input type="text" name="hora_entrada" id="hora_entrada" value="<?php if (isset($_POST['hora_entrada'])) echo $_POST['hora_entrada']; ?>">
            </label>
            <button type="submit">¡Enviar!</button>
        </form>
        <?php
        // Array para almacenar errores
        $errores = array();
        // Array con los valores recibidos
        $valorRecibido = [];
        // Patrones para la fecha y la hora
        $patronHora = '/^\d{1,2}:\d{2}$/';
        $patronFecha = '/^\d{1,2}\/\d{1,2}\/\d{4}$/';
        
        if (!empty($_POST)) { 

            // Filtro para el valor de entrada de la fecha
            $valorRecibido['fecha_entrada'] = filter_input(INPUT_POST, 'fecha_entrada');

            // Si el valor recibido es nulo o cero, guardamos error indicándolo. 
            // Si no es así, validamos la entrada contra una expresión regular 
            // almacenada en la variable $patronFecha.
            // Si pasamos la validación de la expresión regular, usamos la función 
            // validaFecha() pasando la entrada, si el formato de fecha no es correcto 
            // devolverá false. Si se pasa esta validación, guardamos la entrada 
            // en el array de datos
            if ($valorRecibido['fecha_entrada'] === null || strlen($valorRecibido['fecha_entrada']) == 0) {
                $errores[] = "La fecha no se ha informado.";
            } elseif (!$valorRecibido['fecha_entrada'] = filter_input(INPUT_POST, 'fecha_entrada', FILTER_VALIDATE_REGEXP,
                    ['options' =>
                        ['regexp' => $patronFecha]
                    ]
                    )) {
                $errores[] = "El formato de la fecha no es correcto.";
            } elseif (!validaFecha($valorRecibido['fecha_entrada'])) {
                $errores[] = "La fecha introducida no es válida.";
            } else {
                $fecha = limpiaEntrada($valorRecibido['fecha_entrada']);
            }

            // Filtro para el valor de entrada de la hora
            $valorRecibido['hora_entrada'] = filter_input(INPUT_POST, 'hora_entrada');
            
            // Si el valor recibido es nulo o cero, guardamos error indicándolo. 
            // Si no es así, validamos la entrada contra una expresión regular 
            // almacenada en la variable $patronHora.
            // Si pasamos la validación de la expresión regular, usamos la función 
            // convertirHoraAMinutos() pasando la entrada, si no se puede realizar 
            // la conversión devolverá false. Si se pasa esta conversión, guardamos 
            // la entrada en el array de datos 
            if ($valorRecibido['hora_entrada'] === null || strlen($valorRecibido['hora_entrada']) == 0) {
                $errores[] = "La hora de inicio actual no se ha informado.";
            } elseif (!$valorRecibido['hora_entrada'] = filter_input(INPUT_POST, 'hora_entrada', FILTER_VALIDATE_REGEXP,
                    ['options' =>
                        ['regexp' => $patronHora]
                    ]
                    )) {
                $errores[] = "El formato de la hora de inicio actual no es correcto.";
            } elseif (!$horaInicioActual = convertirHoraAMinutos($valorRecibido['hora_entrada'])) {
                $errores[] = "La hora de inicio actual debe estar dentro del rango 00:00 como mínimo y 23:59 como máximo.";
            } else {
                $hora = limpiaEntrada($valorRecibido['hora_entrada']);
            }
            // Si hay errores, los mostramos 
            if (!empty($errores)) {
                echo "<p>ERRORES ENCONTRADOS:</p>";
                // Con un bucle recorremos el array de errores para mostrarlos 
                echo "<ul>";
                foreach ($errores as $error) {
                    echo "<li>" . $error . "</li><br/>";
                }
                echo "</ul>";
                echo "<br/><hr>";
            } else {
                // Dividimos la fecha en varios string que continene el día, mes y año 
                $dma = explode("/", $fecha);
                // Dividimos la hora en varios string que contienen las horas y minutos
                $hhmm = explode(":",$hora);
                echo '<br/>';
                // Llamamos a la función marcaTiempo para convertir la fecha y hora 
                $time = marcaTiempo($hhmm[0], $hhmm[1], 0, $dma[1], $dma[0], $dma[2]);
                // Montamos la URL del servicio de mapas de sombra pasando por URL los 
                // datos de latitud, longitus, zoom, timpo de mapa, y marca de tiempo 
                $web = 'https://app.shadowmap.org/?lat=37.78002&lng=-3.78869&zoom=15&basemap=map&time='.$time.'926&vq=2';
                // Mostramos mensaje con la URL del mapa 
                echo "Se ha generado mapa, pulsa <a href='$web' target='_blank'>AQUÍ</a>";

            }

        }

/**
 * Función validaFecha: valida una fecha dada con formato dd/mm/aaaa.
 * @param string $fecha
 * @return boolean
 */
function validaFecha($fecha) {

    /* Con explode dividimos la cadena en tres strig que contienen los datos de la fecha */
    $dma = explode("/", $fecha);
    /* Retormanos la validación de la fecha */
    return checkdate($dma[1], $dma[0], $dma[2]);
}
/**
 * Función limpiaEntrada: recibe un dato por parámetro y lo limpia eliminando los 
 * espacios en blanco del inicio y del final y quita las barras de con comillas escapadas.
 * @param string $dato
 * @return string
 */
function limpiaEntrada($dato) {
    // Elimina espacio en blanco (u otro tipo de caracteres) del inicio y el final de la cadena
    $dato = trim($dato);
    // Quita las barras de un string con comillas escapadas
    $dato = stripslashes($dato);

    return $dato;
}
/**
 * Función convertirHoraAMinutos: Convierte una cadena que contiene una hora a un 
 * entero que representa los minutos que han pasado desde el comienzo del día (las 00.00). 
 * Si no se puede realizar la conversión se devuelve false.
 * 
 * @param string $hora
 * @return boolean
 */
function convertirHoraAMinutos($hora) {
    // Inicializamos los minutos 
    $minutos = 0;
    // Con explode dividimos la cadena en dos string que contendrán las horas 
    $horas = explode(":", $hora);
    // Comprobamos si podemos pasar a entero los valores de $horas .. 
    if (settype($horas[0], "integer") && settype($horas[1], "integer")) {
        // Comprobamos que las horas y los minutos estén dentro del rango de 
        // formato de 24 horas. 
        if (($horas[0] >= 0 && $horas[0] <= 23) && ($horas[1] >= 0 && $horas[1] <= 59)) {
            // Pasamos las horas ($horas[0]) a minutos multiplicando por 60 y 
            // le sumamos los minutos ($horas[1]) 
            $minutos = ($horas[0] * 60) + $horas[1];
            // Devolvemos los minutos
            return $minutos;
        } else {
            return false; // Si las horas no están entre las 00:00 y las 23:59 
        }
    } else {
        return false; // Si los valores de horas del parámetro de entrada no son enteros 
    }
}
/**
 * Función marcaTiempo: recibe por parámetro la hora y fecha y devuelve un entero
 * con la marca de tiempo Unix.
 * @param int $horas
 * @param int $minutos
 * @param int $segundos
 * @param int $mes
 * @param int $dia
 * @param int $anio
 * @return int
 */
function marcaTiempo($horas, $minutos, $segundos, $mes, $dia, $anio) {
    return mktime($horas, $minutos, $segundos, $mes, $dia, $anio);
}

        ?>
        
    </body>
</html>