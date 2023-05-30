<?php

/* ----------------------------

    Подключение к БД

---------------------------- */

require_once('connect.php');
$link = mysqli_connect($host, $user, $password, $db_name) or die("Ошибка " . mysqli_error($link));

/* ----------------------------

    Файлы phpmailer

---------------------------- */
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';


$id_request=$_POST['id_request']; // id пользователя 

/* ----------------------------

    При отправки письма статус отправки письма меняется на 1 (отправленный)
*для того, чтобы в дальнейшем скрывать кнопку отправки после первого клика на неё

---------------------------- */

$UpdateSendMail = "UPDATE request SET ID_SendMail=1 WHERE IDRequest = ".$id_request."";
if(!mysqli_query($link, $UpdateSendMail)) //если запрос не прошел выводим ошибку
{
    echo ("Возникла ошибка при изменении статуса отправки письма: ");
    echo(mysqli_error($link) . "\n");
}

/* ----------------------------

    Запрос на получение данных для отправки письма
- имени
- почты 
- названия статуса и его описания

Присвоение полученных данных переменным

---------------------------- */

$Select = "SELECT IDRequest, Name, Email, NameStatus, StatusDescription
FROM formrequest.request 
JOIN formrequest.status on request.ID_Status = status.IDStatus
JOIN formrequest.client on request.ID_Client = client.IDClient 
WHERE IDRequest = ".$id_request;

$Sel = mysqli_query($link, $Select) or die("Ошибка " . mysqli_error($link)); 
$RequestRows= mysqli_num_rows($Sel); //количество строк в массиве

for($i = 0; $i < $RequestRows; $i++)
{
    $row = mysqli_fetch_row($Sel);
    $name = $row[1];
    $email = $row[2];
    $status = $row[3];
    $statusDesc = $row[4];
}

?>

<!doctype html>
<html lang="ru">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    
    <!-- Шрифт Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;500;900&display=swap" rel="stylesheet">
    <link href="./CSS/StyleMail.css" rel="stylesheet">

</head>

<?php

/* ----------------------------

    Формирование вида письма

---------------------------- */

$title = 'Статус Вашей заявки: '.$status;

$body='
<div style=" margin: 0; font-family: \'Montserrat\', sans-serif;font-size: 17px; max-width: 576px; margin-left: auto; margin-right: auto;>

    <div class="container" style="max-width: 576px;>

        <header style=" box-shadow: 0 .125rem .25rem rgba(0, 0, 0, 0.075);>
            <a href="https://radiorem22.ru/" style="text-decoration: none; display: block;">
                <img class="logoImg" src="cid:logo_img" style="vertical-align: middle; height: 100px;order: 1;width: 65px;height: 65px;">
            </a>
        </header>

        <p class="greeting" style=" display: block;"><strong>Здравствуйте, '.$name.'!</strong></p>
        <br><p>Статус Вашей заявки изменен на: <strong>'.$status.'</strong></p>
        <p>'.$statusDesc.'</p> 
        <p>С уважением,<br> Мастерская по ремонту бытовой техники</p>

    </div>

</div>';     

/* ----------------------------

    Настройки PHPMailer

---------------------------- */

$mail = new PHPMailer\PHPMailer\PHPMailer();
try {
    $mail->isSMTP();   
    $mail->CharSet = "UTF-8";
    $mail->SMTPAuth   = true;
    //$mail->SMTPDebug = 2;
    $mail->Debugoutput = function($str, $level) {$GLOBALS['status'][] = $str;};

    /* ----------------------------

        Настройки почты

    ---------------------------- */

    $mail->Host       = 'ssl://smtp.mail.ru'; // SMTP сервера почты
    $mail->Username   = 'e-xux@mail.ru'; // Логин на почте
    $mail->Password   = 'LhfqwieSzqBD6KBAbUuS'; // Специальный внешний пароль
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;
    $mail->setFrom('e-xux@mail.ru', 'РадиоРемонт'); // Адрес самой почты и имя отправителя

    
    $mail->addAddress($email); // Получатель письма. Можно добавить еще, если нужно
   
    //mail->addAttachment('images/logo.png'); //прикрепить фото к сообщению

    //фото в самом сообщении
    // ('пусть_к_файлу', 'id_прикрепляемого_файла', 'название_прикрепляемого_файла','кодировка', 'тип_файла')
    
    $mail->AddEmbeddedImage ('Images/mail.png', 'logo_img', 'mail.png','base64', 'image/png');

    /* ----------------------------

        Отправка сообщения

    ---------------------------- */
    
    $mail->Subject = $title;
    $mail->Body = $body; 
    $mail->isHTML(true);   

    /* ----------------------------

        Проверяем отправку сообщения

    ---------------------------- */
    
    if ($mail->send())
    {
        $result = "success";
    } 
    else
    {
        $result = "error";
    }
}
catch (Exception $e)
{
    $result = "error";
    $status = "Сообщение не было отправлено. Причина ошибки: {$mail->ErrorInfo}";
}

echo json_encode(["result" => $result, "resultfile" => $rfile, "status" => $status]); // Отображение результата
