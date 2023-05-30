<?php

    $host = '127.0.0.1'; //имя хоста //"localhost"
    $user = 'root'; //пользователь
    $password = 'root'; //пароль
    $db_name = 'formrequest'; //имя бд
    $db_server = mysqli_connect($host, $user, $password, $db_name); //подключение к бд    
    if (!$db_server) die("Невозможно подключиться к MySQL: " . mysql_error()); 
    
?>