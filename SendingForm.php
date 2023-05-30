
<?php require_once('connect.php');

    /*------------------------------

        Если ввведены все поля (до выбора места ремонта)

    ------------------------------*/

    if (isset($_POST["name"]) && isset($_POST["email"]) && isset($_POST["tel"])&& isset($_POST["technik"])&& isset($_POST["brand"])&& isset($_POST["model"])&& isset($_POST["desc"]) && isset($_POST["place"]))
    {
        /*------------------------------

            Подключение к БД

        ------------------------------*/

        $conn = mysqli_connect($host, $user, $password, $db_name) or die("Ошибка " . mysqli_error($link));

        /*------------------------------

            Все основные переменные

        ------------------------------*/
        
        $name = $conn->real_escape_string($_POST["name"]);
        $email = $conn->real_escape_string($_POST["email"]);
        $tel = $conn->real_escape_string($_POST["tel"]);
        $technik = $conn->real_escape_string($_POST["technik"]);
        $brand = $conn->real_escape_string($_POST["brand"]);
        $model = $conn->real_escape_string($_POST["model"]);
        $desc = $conn->real_escape_string($_POST["desc"]);
        $place = $conn->real_escape_string($_POST["place"]);
        
        /*------------------------------

            Eсли в БД уже есть информация о клиенте (ранее были заявки), получаем его id
        1. Запрос из БД на ID по номеру телефона, почте и имени
        2. Если пользователь найден, сохраняем ID в переменную
        3. Иначе добавляем в БД 

        ------------------------------*/
        
        $ID_Client; //тут хранится id клиента

        $selectClient ="SELECT IDClient FROM client WHERE Name='".$name."' and Email='".$email."' and phone_number='".$tel."'";
        $selectClient2 = mysqli_query($conn, $selectClient) or die("Ошибка " . mysqli_error($conn)); 
        $result_idkl = mysqli_fetch_row($selectClient2);

        if ($result_idkl!=null) //если есть id в бд
        {
            $ID_Client = $result_idkl[0];
        }
        else //если клиента с таким именем, номером и почтой нет - добавляем в бд
        {
            $sql_client="INSERT INTO client (Email, phone_number, Name) VALUES ('$email','$tel','$name')" ;

            if($conn->query($sql_client))
            {
                $ID_Client=mysqli_insert_id($conn);//последний вписанный i
            }
            else
            {
                echo "Ошибка: " . $conn->error;
            }
        }

        /*------------------------------

            Переменные для сохранения даты и времени отправки заявки (Барнаульское)

        ------------------------------*/

        date_default_timezone_set('Europe/Moscow'); //часовой пояс
        $curdate=time();//текущая дата
        $date = date('Y-m-d H:i', $curdate+ 4*60*60); //к текущей дате прибавляем 4 часа (Барнаульское время)
        
        /*------------------------------

            Проверка адреса
        1. Если адрес был введен в текстовое поле и выбрана кнопка "у меня", записываем его в переменную
        2. Если была выбрана "в мастерской", то адрес = null
        3. Ввод адреса в БД

        ------------------------------*/

        $adres; //тут хранится адрес
        
        if (isset($_POST["address"]) && ($_POST["place"]=="client"))
        {
            $adres = $conn->real_escape_string($_POST["address"]); //адрес будет равен ввёдённому адресу пользователя
        }
        elseif($_POST["place"]=="master")
        {
            $adres = null;
        }
        
        $sql_address="INSERT INTO address (Address) VALUES ('$adres')" ; //запрос на ввод адреса

        if($conn->query($sql_address))
        {
            $ID_adres=mysqli_insert_id($conn);  //последний вписанный id
        }
        else
        {
            echo "Ошибка: " . $conn->error;
        }

        /*------------------------------

            Создание запроса на заявку, если заполнен адрес и клиент
        
        ------------------------------*/

        if($ID_Client!=0 && $ID_adres!=0)
        {    
            $sql_ques="INSERT INTO request (NameEquipment,Model,DTdispatch,description,Brand,ID_Status,ID_Address,ID_Client,ID_SendMail) VALUES ('$technik','$model','$date','$desc','$brand',1,$ID_adres,$ID_Client,1)";

            if($conn->query($sql_ques))
            {
                $ID_ques=mysqli_insert_id($conn); //id последней вписанной анкеты
                //echo $ID_ques;

                if(isset($_POST['submit']))
                {   
                    /*------------------------------

                        Если есть загруженные изображения
                    1. Проверка количества
                    2. Проверка размера
                    3. Сохранение в папку и БД
                                
                    ------------------------------*/
                   
                    if($_FILES['fileUpload']['tmp_name'][0])
                    {
                        $myFile = $_FILES['fileUpload'];
                        $fileCount = count($myFile["name"]);

                        if ($fileCount<=3)
                        {
                            for ($i = 0; $i < $fileCount; $i++) //цикл по всем
                            {
                                if($myFile['size'][$i] > (2 * 1024 * 1024)) die('Размер файла не должен превышать 2Мб');

                                $imageinfo = getimagesize($myFile['tmp_name'][$i]);
                                $upload_dir = 'PhotoFromTheRequest/'; //имя папки с картинками
                                $name = $upload_dir.date('YmdHis').basename($myFile['name'][$i]);
                                $mov = move_uploaded_file($myFile['tmp_name'][$i],$name);

                                if($mov)
                                {
                                    $name = htmlentities(stripslashes(strip_tags(trim($name))),ENT_QUOTES,'UTF-8');
                                    $sqlimg = "INSERT INTO images(ID_Request, Way) VALUES($ID_ques,'$name')";
        
                                    if($conn->query($sqlimg))
                                    {
                                       /*  session_start();
                                        header("Location: http://localhost/SendingForm.php"); //редирект на эту же страницу 
                                        exit; */
                                    }
                                    else
                                    {
                                        echo "Ошибка: " . $conn->error;
                                    }
                                }
                            }
                        }
                    }
                    else
                    {   
                        session_start();
                        header("Location: http://localhost/SendingForm.php"); //редирект на эту же страницу 
                        exit;
                    }
                }  
            }
            else
            {
                echo "Ошибка: " . $conn->error;
            }
        }
        $conn->close();
    }   
?>
<!doctype html>
<html lang="ru">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Отправка заявок</title>

    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    
    <!-- Шрифт Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;500;900&display=swap" rel="stylesheet">
    
    <!-- Стили -->
    <link href="./CSS/styleSend.css" rel="stylesheet" type="text/css">
    <link href="./CSS/toast.css" rel="stylesheet">
    <link href="/Images/favicon.png" rel="shortcut icon" type="image/x-icon" >
    <link href="/Images/favicon.png" rel="icon" type="Image/x-icon" >

</head>

<body>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">

                <div class="modal-header">
                    <button
                     type="button" class="btn-close btn-close-white " data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <br><br><br><br><br> <p class="modal_p1">Ваша заявка отправлена!</p><br>
                </div>

            </div>
        </div>
    </div>

    <header class="sticky-top">

        <a href="https://radiorem22.ru/">
            <img class="logoImg" src="./Images/logo.png">
            <h2>Ради</h2><img class="LogoGif" src="./Images/2_.gif"><h2>Рем</h2>
        </a>

    </header>
    
    <div class="circle2"></div>

    <div class="container">
        <div class="stage">
        <div class="circle"></div>
        </div>
        <div class="text-box">
            <h1>Оставьте заявку на ремонт вашей техники - <br><br> с вами свяжется специалист</h1>
        </div>
    </div> 
       
    <div class="circle3"></div>

        <div class="form_request">
    
            <div id="wave">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                    <path fill="#0099ff" fill-opacity="1" d="M0,128L48,112C96,96,192,64,288,74.7C384,85,480,139,576,149.3C672,160,768,128,864,122.7C960,117,1056,139,1152,133.3C1248,128,1344,96,1392,80L1440,64L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path>
                </svg>
            </div>

            <div class="container">
              
                <form method="POST" enctype="multipart/form-data">

                    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
                        <symbol id="image-fill" viewBox="0 0 20 20">
                            <path d="M.002 3a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-12a2 2 0 0 1-2-2V3zm1 9v1a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V9.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12zm5-6.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0z"/>
                        </symbol>
                    </svg>
                    
                    <div class="circle4"></div>
                    
                    <nav class="d-grid gap-2 col-sm">
                        
                        <a href="#" class="btn btn-hover-light d-flex align-items-center gap-3 py-2 px-3 lh-sm">
                            <label for="image_uploads" class="label-box">
                                <svg class="bi" width="55" height="55"><use xlink:href="#image-fill"/></svg>

                                <div class='label-button'>
                                    <strong class="d-block">Прикрепить фото</strong>
                                    <p>До 3-х изображений, размером не более 20 МБ</p>
                                </div>

                            </label>
                        </a>

                    </nav> 
                   
                    <input id="image_uploads" name="fileUpload[]" type="file" accept=".jpg, .jpeg, .png" multiple>

                    <div class="preview"></div>

                        <div class="application-box row">

                            <div class=" col-sm">
                                <div class="form-floating label-box">
                                    <input class="name form-control" id="name" name="name" type="text" maxlength="100" required placeholder="Имя">
                                    <label for="name">Ваше имя</label>
                                </div>
                            </div>

                            <div class="col-sm">
                                <div class="form-floating label-box">
                                <input class="email form-control"  id="email" name="email" type="email" maxlength="100"  placeholder="name@example.com required">
                                <label for="email">E-mail</label>
                                </div>
                            </div>

                            <div class=" col-sm">
                                <div class="form-floating label-box">
                                    <input class="tel form-control" id="tel" name="tel" type="tel" maxlength="20" placeholder="+7(999) 999 99-99" required>
                                    <label for="tel">Номер телефона</label>
                                </div>
                            </div>

                        </div>

                        <div class="application-box row">

                            <div class=" col-sm">
                                <div class="form-floating label-box">
                                    <input class="technik form-control" id="technik" name="technik" type="text" maxlength="100" placeholder="Техника" required>
                                    <label for="technik">Техника</label>
                                </div>
                            </div>

                            <div class=" col-sm">
                                <div class="form-floating  label-box">
                                    <input class="brand form-control" id="brand" name="brand" type="text" maxlength="100" placeholder="Бренд" required>
                                    <label for="brand">Марка</label>
                                </div>
                            </div>

                            <div class=" col-sm">
                                <div class="form-floating label-box">
                                    <input class="model form-control" id="model" name="model" type="text" maxlength="100" placeholder="Модель" required>
                                    <label for="model">Модель</label>
                                </div>
                            </div>
                             
                        </div>

                        <div class="application-box box3 row">

                            <div class="col-xl">
                                <br>
                                <p>Опишите вашу проблему как можно подробнее:</p>
                                <div class="form-floating">
                                    <textarea class="desc form-control" id="desc" name="desc"  type="text" maxlength="500" required placeholder="Опишите вашу проблему" ></textarea>
                                    <label for="desc">Описание неисправности</label>
                                </div>
                            </div>

                            <div class="col-xl-4">
                                <div class="radio">
                                    <br>
                                    <p>Где оказать услугу?</p>
                                    <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                                        <input type="radio" class="btn-check" name="place" id="btnradio1" value="client" autocomplete="off" onchange="check();">
                                        <label class="btn btn-light p-3 " for="btnradio1" >У меня</label>
                                        <input type="radio" class="btn-check" name="place" id="btnradio2" value="master"  onchange="check();" autocomplete="off" checked>
                                        <label class="btn btn-light p-3 " for="btnradio2">У мастера</label>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div id="div_address" class="application-box row">

                            <div class="col">
                                <div class="form-floating label-box">
                                    <input class="address form-control" id="address" name="address" type="text" maxlength="200" placeholder="Введите адрес" required>
                                    <label for="address">Адрес</label>
                                </div>
                            </div>

                        </div>

                        <div class="col-sm">

                            <div id="map">
                                <p>Мы находимся здесь:</p>
                                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d708.7887974972436!2d83.61280652306249!3d53.30615973355901!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x42dd9f4d4cf00001%3A0x5744031f00f1a515!2z0KDQsNC00LjQviDQoNC10LzQvtC90YI!5e0!3m2!1sru!2sru!4v1649337008731!5m2!1sru!2sru" width="100%" height="250" style="border:0; border-radius:10px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>

                        </div>     

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end submit">
                            <input class="btn btn-light p-3 mt-4" id="submit" name="submit" type="submit" onclick="checkAddress();" value="Отправить заявку">
                        </div>
                </form>
            </div>

            <div class="wave2">
                <div  id="wave2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="#0099ff" fill-opacity="1" d="M0,320L30,293.3C60,267,120,213,180,208C240,203,300,245,360,218.7C420,192,480,96,540,96C600,96,660,192,720,245.3C780,299,840,309,900,309.3C960,309,1020,299,1080,277.3C1140,256,1200,224,1260,224C1320,224,1380,256,1410,272L1440,288L1440,320L1410,320C1380,320,1320,320,1260,320C1200,320,1140,320,1080,320C1020,320,960,320,900,320C840,320,780,320,720,320C660,320,600,320,540,320C480,320,420,320,360,320C300,320,240,320,180,320C120,320,60,320,30,320L0,320Z"></path></svg>
                </div>
            </div>
            
        </div>

</body>

    <!-- bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    
    <!-- Всплывающие сообщения -->
    <script src="./JS/toast.js"></script>

    <!-- Подключение библиотеки jQuery -->
    <script src="./JS/jquery.js"></script>

    <!-- Подключение jQuery плагина Masked Input -->
    <script src="./JS/jquery.maskedinput.min.js"></script>
    <script src="./JS/SendForm.js"></script> 
    <script src="./JS/CheckValueSendForm.js"></script>
</html>