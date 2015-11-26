<?php
    function conectar() {
        return pg_connect("host=localhost dbname=datos user=usuario password=usuario");
    }
    
    function mostrarFecha($anyo, $mes) { ?>
        <p><strong>Año: <?= $anyo ?>, Mes: <?= $mes ?></strong></p><?php
    }
    
    function selected($value, $col) {
        return ($value == $col) ? 'selected="on"' : '';
    }

    //------------------NOW IN DEVELOPMENT--------------------------
    // Funciona, pero falta ponerlo bonito y totalmente eficiente
    function ponerLunes() {
        $dia_mes = date("d");
        $dia_semana = date("w");
        return ($dia_mes - $dia_semana + 1);
    }
    
    function ponerFecha(&$anyo, &$mes, &$lunes, $longitud_mes) {
        if(isset($_GET['mover'])):
            $mover = trim($_GET['mover']);
            
            if($mover == "<"):
                $lunes -= 7;
            elseif($mover == ">"):
                $lunes += 7;
            endif;

            if($lunes < 1):
                $mes -= 1;
                $longitud_mes = (int) date_format(date_create("$anyo-$mes-1"), "t");
                $lunes += $longitud_mes;
            elseif($lunes > $longitud_mes):
                $mes += 1;
                $lunes -= $longitud_mes;
            endif;
            
            if($mes < 1):
                $mes = 12;
                $anyo -= 1;
            elseif($mes > 12):
                $mes = 1;
                $anyo += 1;
            endif;
            $_SESSION['lunes'] = $lunes;
            $_SESSION['mes'] = $mes;
            $_SESSION['anyo'] = $anyo;
        else:
            $mover = "";
        endif;
    }
    //------------------NOW IN DEVELOPMENT--------------------------
    
    // Muestra la informacion del usuario
    function mostrarUsuario($usuario_id) {
        $res = pg_query_params("select * from usuarios where id = $1", array($usuario_id));
        $fila = pg_fetch_assoc($res, 0); ?>
        
        <h3 align="right">
            Usuario: <?= $fila['nick'] ?>
            <a href="usuarios/logout.php">
                <input type="button" value="Salir" />
            </a>
        </h3>
        <hr /><?php
    }
    
    // Muestra la eleccion de tipo de pista
    function mostrarPistas($pista_id) {
        $res = pg_query("select * from pistas"); ?>
        
        <form action="index.php" method="get">
            <select name="pista_id"><?php
                for($i = 0; $i < pg_num_rows($res); $i++) { 
                    $fila = pg_fetch_assoc($res, $i); ?>
                   <option value="<?= $fila['id'] ?>" <?= selected($fila['id'], $pista_id) ?>>
                        <?= $fila['nombre'] ?>
                   </option><?php
                } ?>
            </select>
            <input type="submit" value="Mostrar Reservas" />
        </form><?php
    }
    
    // Pinta la tabla
    function pintarTabla($meses, $dias, $longitud_mes, $pista_id, $anyo, $mes, $lunes, $usuario_id) { ?>
        <table border="1" style="margin: auto; text-align: center;">
            <caption>
                <?= mostrarFecha($anyo, $meses[(int) $mes - 1]) ?>
                <?= moverTiempo("<", $pista_id); ?>
                <?= moverTiempo(">", $pista_id); ?>
            </caption>
            <thead>
                <th>Hora</th><?php
                    
                    for($i = 0; $i < count($dias); $i++) {
                        $dia = $lunes+$i;  
                        if ($dia > $longitud_mes) $dia -= $longitud_mes; ?>
                        <th><?= $dias[$i] ?>(<?= $dia ?>)</th><?php
                    } ?>
            </thead>
            <tbody><?php
                for($i = 10; $i < 20; $i++) { 
                    $hora = $i . ":00"; ?>
                    <tr>
                        <td><?= $hora ?></td><?php
                            for($j = 0; $j < count($dias); $j++) { 
                                $dia = $lunes+$j;
                                $mesNuevo = $mes;
                                $anyoNuevo = $anyo;
                                if ($dia > $longitud_mes) {
                                    $dia -= $longitud_mes;
                                    $mesNuevo += 1;
                                }
                                if($mesNuevo > 12) {
                                    $mesNuevo = 1;
                                    $anyoNuevo += 1;
                                } ?>
                                <td>
                                    <?= botonPista($pista_id, $anyoNuevo, $mesNuevo, $dia, $i, $usuario_id) ?>
                                </td><?php
                            } ?>
                    </tr><?php
                } ?>
            </tbody>
        </table><?php
    }
    
    // Enseña los iconos para desplazarte en el tiempo
    function moverTiempo($movimiento, $pista_id) { ?>
        <form action="index.php" method="get" style="display: inline;" >
            <input type="hidden" name="pista_id" value="<?= $pista_id ?>" />
            <input type="hidden" name="mover" value="<?= $movimiento ?>" />
            <input type="submit" value="<?= $movimiento ?>" />
        </form><?php
    }
    
    // Indica con cada pista si esta Reservada o no
    function botonPista($pista_id, $anyo, $mes, $dia, $hora, $usuario_id) {
        $fecha = $anyo . '-' . $mes . '-' . $dia;
        $valor = (reservada($pista_id, $fecha, $hora)) ? "Reservar" : "Anular"; ?>
        
        <form action="index.php?pista_id=<?= $pista_id ?>" method="post">
            <input type="hidden" name="pista_id" value="<?= $pista_id ?>" />
            <input type="hidden" name="anyo" value="<?= $anyo ?>" />
            <input type="hidden" name="mes" value="<?= $mes ?>" />
            <input type="hidden" name="dia" value="<?= $dia ?>" />
            <input type="hidden" name="hora" value="<?= $hora ?>" /><?php
                if(!esReservador($pista_id, $fecha, $hora, $usuario_id)): ?>
                    <strong>Esta Reservada</strong><?php
                else: ?>
                    <input type="submit" value="<?= $valor ?>" /><?php
                endif; ?>
        </form><?php
    }
    
    // Devuelve un booleano que indica si la pista esta reservada o no
    function reservada($pista_id, $fecha, $hora) {
        $res = pg_query_params("select * from reservas 
                                where pistas_id = $1 and fecha = $2 and hora = $3", 
                                array($pista_id, $fecha, $hora));
        return pg_num_rows($res) == 0;
    }
    
    // Devuelve un booleano que indica si es el mismo usuario que alquilo la pista
    function esReservador($pista_id, $fecha, $hora, $usuario_id) {
        $res = pg_query_params("select * from reservas 
                                where pistas_id = $1 and fecha = $2 and hora = $3 and usuarios_id != $4", 
                                array($pista_id, $fecha, $hora, $usuario_id));
        return pg_num_rows($res) == 0;
    }
    
    // Funciones SQL
    function reservarPista($pista_id, $usuario_id, $fecha, $hora) {
        $res = pg_query_params("insert into reservas (pistas_id, usuarios_id, fecha, hora)
                                values ($1, $2, $3, $4)", array($pista_id, $usuario_id, $fecha, $hora));
    }
    
    function anularReserva($pista_id, $fecha, $hora) {
        $res = pg_query_params("delete from reservas 
                                where pistas_id = $1 and fecha = $2 and hora = $3", 
                                array($pista_id, $fecha, $hora));
    }