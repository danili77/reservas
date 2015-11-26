<?php session_start(); ?>

<!DOCTYPE html>

<html>
    <head>
        <meta charset="utf-8" />
        <title>Login</title>   
    </head>
    <body><?php
        require '../comunes/auxiliar.php';
        
        if(isset($_SESSION['usuario_id'])):
            header("Location: ../index.php");
            return;
        endif;
        
        conectar();
        
        if(isset($_POST['nick'], $_POST['password'])):
            $nick = trim($_POST['nick']);
            $password = trim($_POST['password']); 
            $res = pg_query_params("select id from usuarios where nick = $1 and password = md5($2)", array($nick, $password));

            if(pg_num_rows($res) > 0):
    
                $fila = pg_fetch_assoc($res, 0);
                $id = $fila['id'];
                $_SESSION['usuario_id'] = $id;
                //$_SESSION['socio_numero'] = $numero;
                //comprobar_socio_admin();
                
                header("Location: ../index.php");
                return;
            else: ?>
                <h3>Error: usuario incorrecto.</h3><?php
            endif;
         endif; ?>
        <form action="login.php" method="POST">
            <label for="nick">Nick:</label>
            <input type="text" name="nick" /><br />
            <label for="password">Contraseña:</label>
            <input type="password" name="password" /><br />
            <input type="submit" value="Login" />
        </form>
    </body>
</html>