<?php session_start(); ?>

<!DOCTYPE html>

<html>
    <head>
        <meta charset="utf-8" />
        <title>Reservas</title>   
    </head>
    <body><?php
        require './comunes/auxiliar.php';
        
        $dias = array('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes');
        $meses = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                       'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
        conectar();
        
        if(!isset($_SESSION['usuario_id'])):
            header("Location: usuarios/login.php");
            return;
        else:
            $usuario_id = $_SESSION['usuario_id'];
            if(isset($_POST['pista_id'], $_POST['anyo'], $_POST['mes'], $_POST['dia'], $_POST['hora'])):
                $pista_id = trim($_POST['pista_id']);
                $fecha = trim($_POST['anyo']) . "-" . trim($_POST['mes']) . "-" . trim($_POST['dia']);
                $hora = trim($_POST['hora']);
                
                if(reservada($pista_id, $fecha, $hora)):
                    reservarPista($pista_id, $usuario_id, $fecha, $hora);
                else:
                    anularReserva($pista_id, $fecha, $hora);
                endif;
            endif;
            
            $anyo = (isset($_SESSION['anyo'])) ? $_SESSION['anyo']: date("Y");
            $mes = (isset($_SESSION['mes'])) ? $_SESSION['mes']: date("m");
            $lunes = (isset($_SESSION['lunes'])) ? $_SESSION['lunes']: ponerLunes();
            $longitud_mes = (int) date_format(date_create("$anyo-$mes-1"), "t");
            
            ponerFecha($anyo, $mes, $lunes, $longitud_mes);
            $pista_id = isset($_GET['pista_id']) ? $_GET['pista_id'] : "1";
            
            // Se empiza a mostrar
            mostrarUsuario($usuario_id);

            mostrarPistas($pista_id);
            
            pintarTabla($meses, $dias, $longitud_mes, $pista_id, $anyo, $mes, $lunes, $usuario_id);
        endif; ?>
    </body>
</html>