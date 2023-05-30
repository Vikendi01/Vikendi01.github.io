<?php

    /* ----------------------------

        Хранение цветовой темы в сессии

    Если в сессии ничего не сохранено, то тема = светлая (по умолчанию)
    Иначе проверка и включение соотвтств. стилей
        
    ---------------------------- */

    session_start();
    $Theme = "";
    $ThemeAdd ="";

    if(!isset($_SESSION["theme"]))
    {
        $_SESSION["theme"] = "light";
        $themefunc = "themeLight()";
    }
    else
    {
        if($_SESSION["theme"]=="dark")
        {
            $Theme = "dark";
            $themefunc = "themeDark()";
            $ThemeImg='./Images/starss.svg';               
        }

        elseif (($_SESSION["theme"])=="light")
        {
            $Theme = "light";
            $themefunc = "themeLight()";
            $ThemeImg='./Images/sun.svg';
        }
    }

    /* ----------------------------

        Подключение к БД
        
    ---------------------------- */

    require_once('connect.php');
    $link = mysqli_connect($host, $user, $password, $db_name) or die("Ошибка " . mysqli_error($link)); 

    /* ----------------------------

        Получение ID и имени всех статусов
        
    ---------------------------- */

    $SelectStatus ="SELECT IDStatus, NameStatus FROM status";
    $SelStatus = mysqli_query($link, $SelectStatus) or die("Ошибка " . mysqli_error($link)); 
    $StatusRows= mysqli_num_rows($SelStatus); //количество строк в массиве

    /* ----------------------------

        Сортировка заявок по статусам

    В зависимости от нажатой кнопки меняем условие в запросе
    Каждая кнопка содержит value с ключом к нужному условию для запроса
        
    ---------------------------- */

    $sorting_requests=""; //сюда присваивается условие при нажатии на кнопки

    $sorting_array =  //ассоциативный массив с условиями для запроса 
    [
        "все" => "",
        "новые" => "WHERE NameStatus LIKE 'Новая'", //новые
        "принятые" => "WHERE NameStatus LIKE 'Принятая%'",//принятые
        "в пути" => "WHERE NameStatus LIKE 'Мастер в пути%'", //в пути
        "завершенные" => "WHERE NameStatus LIKE 'Завершенная%'", //завершенные
        "отмененные" => "WHERE NameStatus LIKE 'Отмененная%'"//отмененные

        //LIKE - устанавливает соответствие символьной строки с шаблоном
        //% - сравнивает строку содержащую ноль или более символов
    ];

    if (isset($_POST["button_array"])) //если нажата кнопка из верхней панели (выбор заявок)
    {
        foreach($sorting_array as $value) //проходим по ассоциативному массиву с условиями для запроса и ключами
        {
            $key=array_search($value,$sorting_array); //достаем ключ из нужного массива с помощью значения  

            if($key==$_POST["button_array"]) //если ключ = значению кнопки, то сортируем заявки
            {
                $sorting_requests = $value; //переменной, которая вставлена в запрос присваиваем условие для сортировки
            }
        }
    }

    /* ----------------------------

        Запрос на получение данных

    1. По заявкам, клиентам, адресам и текущим статусам
    2. По изображениям
        
    ---------------------------- */ 
    $SelectRequest = "SELECT IDRequest, Name, phone_number, Address, NameEquipment, Brand, Model, description, LEFT(DTdispatch,16), IDStatus, NameStatus, ID_Address, Email, ID_SendMail
    FROM formrequest.request 
    JOIN formrequest.client on request.ID_Client = client.IDClient
    JOIN formrequest.address on request.ID_Address = address.IDAddress
    JOIN formrequest.SendMail on request.ID_SendMail = SendMail.IDSendMail
    JOIN formrequest.status on request.ID_Status = status.IDStatus ".$sorting_requests;

    $SelRequest = mysqli_query($link, $SelectRequest) or die("Ошибка " . mysqli_error($link)); 
    $RequestRows= mysqli_num_rows($SelRequest); //количество строк в массиве

    $selectImages = "SELECT IDImages,ID_Request, Way FROM images"; //получаем все избражения
    $SelImages = mysqli_query($link, $selectImages) or die("Ошибка " . mysqli_error($link)); 
    $ImagesRows= mysqli_num_rows($SelImages); 

    $ArrayImages=[]; //массив с изображениями

    for($i = 0; $i < $ImagesRows; $i++)//добавляем в массив все изображения
    {
        $ArrayImages[$i]=mysqli_fetch_row($SelImages);
    }

    $IDRequest;//id заявки

    /* ----------------------------

        Удаление заявок
    1. Удаление изображения из папки
    2. Удаление изображения из бд
    3. Удаление адреса
    4. Удаление заявки
    5. редирект

    ---------------------------- */

    for($i = 0; $i < $RequestRows; $i++)// запрос на удаление заявки, и всех данных о ней (кроме клиента)
    {
        $row = mysqli_fetch_row($SelRequest);

        if (isset($_POST['DeleteRequest'.$i])) //если нажата кнопка удалить
        {
            for($j=0; $j<count($ArrayImages); $j++)//есть ли в данной заявке фото? Проверка наличия
            {
                if($ArrayImages[$j][1]==$row[0])
                {
                    unlink($ArrayImages[$j][2]); //удаление изображений из папки на сервере
                    $DelImages="DELETE FROM Images WHERE ID_Request=".$row[0];  //удалить все картинки из бд, если совпадает ID заявки     

                    if(!mysqli_query($link, $DelImages))
                    {
                        echo ("Возникла ошибка при удалении фото: ");
                        echo(mysqli_error($link) . "\n");
                    }
                }
            }

            $DelAddress ="DELETE FROM address WHERE IDAddress = ".$row[11]; //удаление адреса из БД

            if(!mysqli_query($link, $DelAddress))
            {
                echo ("Возникла ошибка при удалении адреса: ");
                echo(mysqli_error($link) . "\n");
            }
                    
            $DelRequest="DELETE FROM request WHERE IDRequest = ".$row[0]; //удаление заявки из БД

            if(!mysqli_query($link, $DelRequest))
            {
                echo ("Возникла ошибка при удалении заявки: ");
                echo(mysqli_error($link) . "\n");
            }
            header("Location: http://localhost/ViewingApplications.php"); //редирект на эту же страницу 
            exit;
        }
    }

    /* ----------------------------

       Обнуление переменных для будущего цикла
        
    ---------------------------- */

    $SelRequest = mysqli_query($link, $SelectRequest) or die("Ошибка " . mysqli_error($link)); //сбрасываем переменную с заявками
    $RequestRows= mysqli_num_rows($SelRequest); //количество строк в массиве

    /* ----------------------------

        Изменение статуса

    1. Проверка на изменение статуса (!= текущему)
    2. При изменении статуса заявки, статус отправки письма = 0 (не отправлено)
    *кроме "новая"
        
    ---------------------------- */

    for($i = 0; $i < $RequestRows; $i++)
    {
        $row = mysqli_fetch_row($SelRequest);

        if (isset($_POST["SubmitStatus".$i])) //если нажата кнопка "изменить статус"
        {
            $IDStatus_selected=($_POST["status_select".$i]); //id выбранного статуса

            if ($IDStatus_selected!=$row[9]) //если выбранный статус отличается от статуса в бд, меняем на новый
            {   
                $StatSend = 0;

                if($IDStatus_selected==1)//если заявка меняется на "новая", то ставим статус отправлено, чтобы не отображалась кнопка для отправки письма клиенту
                {
                    $StatSend = 1;
                }

                $UpdateStatus = "UPDATE request SET ID_Status = ".$IDStatus_selected.", ID_SendMail = ".$StatSend." WHERE IDRequest = ".$row[0]."";

                if(!mysqli_query($link, $UpdateStatus)) //если запрос не прошел выводим ошибку
                {
                    echo ("Возникла ошибка при изменении статуса заявки: ");
                    echo(mysqli_error($link) . "\n");
                }
            }
        } 
    }

    /* ----------------------------

       Обнуление переменных для будущего цикла
        
    ---------------------------- */

    $SelRequest = mysqli_query($link, $SelectRequest) or die("Ошибка " . mysqli_error($link)); //"сбрасываем переменную с заявками"
    $RequestRows= mysqli_num_rows($SelRequest); //количество строк в массиве

    /* ----------------------------

       Создание массива со статусами 
        
    ---------------------------- */

    $ArrayStatus=[]; //массив со статусами

    for($i = 0; $i < $StatusRows; $i++) //добавляем в массив все статусы и их id
    {
        $ArrayStatus[$i]=mysqli_fetch_row($SelStatus);
    }
    
?>

<!doctype html>

<?php 
    echo('<html lang="ru" id="html" name="html" class="'.$Theme.'-mode">');
?>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Просмотр заявок</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../CSS/styleView.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;500;900&display=swap" rel="stylesheet">
    <link href="/Images/favicon.png" rel="shortcut icon" type="image/x-icon" >
    <link href="/Images/favicon.png" rel="icon" type="Image/x-icon" >

</head>

<body onLoad="<?php echo($themefunc);?>"> <!-- при загрузке страницы вкл. нужная цветовая тема-->

    <!----------------------------

       Шапка страницы
    1. Ссылка - логотип
    2. Кнопки для сортировки заявок по статусу
    3. Кнопка смены цветовой темы
        
    ---------------------------->

    <header class="sticky-top">

        <?php echo("<nav class='navbar navbar-expand-lg navbar-".$Theme." bg-".$Theme."' id='navbar'>");?>

            <div class="container-fluid">

                <a class="navbar-brand" href="https://radiorem22.ru/">
                    <img class="logoImg" src="./Images/logo.png">
                    <h2>Ради</h2><img class="LogoGif" src="./Images/2_.gif"><h2>Рем</h2>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            
                <div class="collapse navbar-collapse" id="navbarNav">

                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item"></li>
                    </ul>

                    <form method="POST" class="align-items-start">

                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <input type="submit" class="nav_button" href="#" name="button_array" value="все">
                            </li>

                            <li class="nav-item">
                                <input type="submit" class="nav_button" href="#" name="button_array" value="новые">
                            </li>
                            
                            <li class="nav-item">
                                <input type="submit" class="nav_button" href="#" name="button_array" value="принятые">
                            </li>

                            <li class="nav-item">
                                <input type="submit" class="nav_button" href="#" name="button_array" value="в пути">
                            </li>

                            <li class="nav-item">
                                <input type="submit" class="nav_button" href="#" name="button_array" value="завершенные">
                            </li>

                            <li class="nav-item">
                                <input type="submit" class="nav_button" href="#" name="button_array" value="отмененные">
                            </li>
                        </ul>

                    </form>

                    <form method="POST">

                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <?php echo('<img id="imageSub" type="submit" class="images imageSub" onclick="imgsrc()" name="imageSub" src="'.$ThemeImg.'">');?>
                            </li>
                        </ul>
                   </form>

                </div>
            </div>
        </nav>

    </header> 

<div class='container-xl'> 

<?php

    /* ----------------------------

        Вывод всех заявок /или надписи об их отсутствии
        
    ---------------------------- */

    if ($RequestRows == 0 )
    {
        echo("<div class='NoApplications'>
            <p><img src='./Images/4_.gif'></p>
            <br>
            <h2> На данный момент заявок нет</h2>
            </div>");
    }
    else
    {
        /*----------------------------

            Вывод заявок
        Проверка адреса. Если в БД адрес = null, значит выводим "в мастерской", иначе адрес из БД
         
        ----------------------------*/

        for ($i = 0; $i < $RequestRows; $i++)
        {
            echo("<div class='request'>");
            $row = mysqli_fetch_row($SelRequest);
            $IDRequest=$row[0];

            if ($row[3]==null) 
            {
                $AddressOutput = "В мастерской";
            }
            else 
            {
                $AddressOutput = "$row[3]";
            }
?>
            <!----------------------------

               Содержимое заявок
            1. Кнопка "троеточие"
            2. Информация о клиенте
            3. Информация о технике
                    
            ---------------------------->

            <button class="btn three-dots" data-bs-toggle="collapse" data-bs-target="#collapseExample<?php echo($i)?>" aria-expanded="false" aria-controls="collapseExample">

                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-three-dots-vertical images" viewBox="0 0 16 16">
                    <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
                </svg>

            </button>
                
            <p class='outputInfoApplication'>

                <span class='outputTime'>
                    <?php echo($row[8])?>
                </span>

                <?php echo($row[1].", ".$row[2]) ?>
                <span class='space'></span>
                <?php echo($row[12])?>
                <br>Статус заявки: <?php echo($row[10]) ?><br>
                
                <svg xmlns="http://www.w3.org/2000/svg"  fill="currentColor" class="bi bi-geo-alt-fill images geo" viewBox="0 0 16 16">
                    <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                </svg>

                <?php echo("".$AddressOutput);?>

            </p>

            <p class='outputTechInfo'>

                <span class='outputTechInfoHeader'>
                    <?php echo($row[4]." ".$row[5]." ".$row[6]); ?>
                </span>
                <br><?php echo($row[7]); ?>

            </p>

            <!----------------------------

                Вывод списка выбора статуса заявки
            1. Цикл выводит все заявки из БД
            2. Визуальное выделение текущ. статуса

            ------------------------------>
                                
            <div class="collapse" id="collapseExample<?php echo($i) ?>">
                <form method="POST">
                    <select class='form-select' size=7 name='status_select<?php echo($i) ?>' id='status_select<?php echo($i) ?>' onchange="ChangeStatus_show('SubmitStatus<?php echo($i)?>')" >
                    
                        <?php  
                            for ($j = 0; $j <  $StatusRows; $j++) //Выводит все заявки из БД
                            { 
                                if ($row[9]==$ArrayStatus[$j][0]) //если статус заявки == статусу вып. списка - выбрать его
                                {
                                    $sel= " selected";
                                    $OptionSelected = 'OptionSelected';
                                }
                                else
                                {
                                    $sel = ""; 
                                    $OptionSelected = 'OptionSelectedNone';
                                }
                                echo("<option class=".$OptionSelected." value='".$ArrayStatus[$j][0]."'" . $sel . ">" . $ArrayStatus[$j][1] . " </option>");
                            }
                        ?>

                    </select>

                    <!----------------------------

                        После смены статуса заявки передаем в POST текущее значение сортировки (кнопки)
                    Для того, чтобы после перезагрузки оставаться на той же "вкладке" и ничего не ломать в циклах
                        
                    ------------------------------>

                    <?php if(isset($_POST['button_array'])) 
                    {
                        echo('<input hidden name="button_array" value="'.$_POST['button_array'].'">');
                    }?>

                    <!----------------------------

                        Кнопка сохранения статуса
                        
                    ------------------------------>
                    <button type='submit' class='btn SubmitStatus' name ='SubmitStatus<?php echo($i) ?>' id ='SubmitStatus<?php echo($i) ?>'>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-check-lg images text-success" viewBox="0 0 16 16">
                            <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                        </svg>
                    </button>
                </form>

            </div>

                <!----------------------------

                    Кнопка удаления заявки
                    
                ------------------------------>

                <button type="button" class="btn trash" data-bs-toggle="modal" data-bs-target="#staticBackdrop<?php echo($i) ?>">

                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-trash3 images" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M6.5 1a.5.5 0 0 0-.5.5v1h4v-1a.5.5 0 0 0-.5-.5h-3ZM11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H2.506a.58.58 0 0 0-.01 0H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1h-.995a.59.59 0 0 0-.01 0H11Zm1.958 1H3.042l.846 10.58a1 1 0 0 0 .997.92h6.23a1 1 0 0 0 .997-.92l.846-10.58Zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47ZM8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5Z"/>
                    </svg>
                    
                </button>

                <div class="trashh"></div>

                <!----------------------------

                    если письмо не было отправлено - отображаем кнопку отправки

                передаем id заявки, по которой нужно отправить письмо
                            
                ------------------------------>
               
                <?php
                    if ($row[13]==0) 
                    {
                        echo("
                            <form method='POST' onsubmit=\"send(event, 'sendmail.php')\">
                                <input type ='text' name='id_request' value='".$row[0]."' hidden />

                                <button class='btn envelope' type='submit' name='sendEmail".$i."' id='sendEmail".$i."' onclick='hide_sendbutton(this);'> 
                                    <svg xmlns='http://www.w3.org/2000/svg' fill='currentColor' class='bi bi-envelope images' viewBox='0 0 16 16'>
                                        <path d='M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z'/>
                                    </svg>
                                </button>
                            </form>
                        ");
                    }
                ?>

                <form method="POST">
                
                <!----------------------------

                    Вывод окошка подтверждения удаления заявки  

                ------------------------------>

                     <div class="modal fade" id="staticBackdrop<?php echo($i) ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog  modal-dialog-centered">
                            <div class="modal-content" id="modal">

                                <div class="modal-header">
                                    <h5 class="modal-title" id="staticBackdropLabel"> Удаление заявки клиента: <?php echo($row[1]) ?> </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="butt_close" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    Вы действительно уверены, что хотите удалить заявку?
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-danger" name="DeleteRequest<?php echo($i) ?>">Удалить</button>
                                    <button type="button" class="btn btn-dark"  data-bs-dismiss="modal" id="butt_modal">Отмена</button>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!------------------------------

                        После смены статуса заявки передаем в POST текущее значение сортировки (кнопки)
                    Для того, чтобы после перезагрузки оставаться на той же "вкладке" и ничего не ломать в циклах

                    ------------------------------>

                    <?php
                        if(isset($_POST['button_array']))
                        {
                            echo('<input hidden name="button_array" value="'.$_POST['button_array'].'">');
                        }
                    ?>

                </form>

                <!------------------------------

                   Цикл - выводит изображения из заявки
                Вывод иконки и ссылки на прикреп. изображение

                ------------------------------>

                <?php
                    for($iImages=0; $iImages< count($ArrayImages); $iImages++) 
                    {
                        if ($ArrayImages[$iImages][1]==$IDRequest)
                        {
                            $WayImage = ($ArrayImages[$iImages][2]);
                            echo('
                                <button type="button" class="btn imagesIcon">
                                    <a href="'.$WayImage.'" target="_blank">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-image images" viewBox="0 0 16 16">
                                            <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
                                            <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z"/>
                                        </svg>
                                    </a>   
                                </button>
                            ');
                        }
                    }
                    echo("<div></div>");
                echo("</div>");
        }
    }
                ?>
</div>    
    <script src="../JS/ViewApplications.js"></script>
    <script src="./JS/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js" integrity="sha384-W8fXfP3gkOKtndU4JGtKDvXbO53Wy8SZCQHczT5FMiiqmQfUpWbYdTil/SxwZgAN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.min.js" integrity="sha384-skAcpIdS7UcVUC05LJ9Dxay8AXcDYfBJqt1CJ85S/CFujBsIzCIv+l9liuYLaMQ/" crossorigin="anonymous"></script>
</body>

</html>