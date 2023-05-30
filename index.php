
<!doctype html>

<html lang="ru" id="html" name="html">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ВКР</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../CSS/styleView.css">
    <link rel="stylesheet" type="text/css" href="../CSS/styleSend.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;500;900&display=swap" rel="stylesheet">
    <link href="/Images/favicon.png" rel="shortcut icon" type="image/x-icon" >
    <link href="/Images/favicon.png" rel="icon" type="Image/x-icon" >

    <style>
        body
        {
            font-size:150px;
           /*  position: relative; */
            background:white;
            font-weight: 900;
        }

        .div1
        {
            /* position:absolute; */
            background:white;
            width:50%;
            float:left;
            padding-top:10%;
            padding-left:40px;
            height: 100vh;
        }

        .div2
        {
            width:50%;
           /*  height: inherit; */
            float:right;
            height: 100vh;
            padding-top:10%;
            
            padding-left:40px;
            background: linear-gradient(
                #f98888,
                #ff987d,
                #ffab73,
                #ffbf6c,
                #ffd56c
            )
        }
        .a2,.a2:hover
        {
            color:white;
           
        }
        .circle3
        {
            margin-left: 700px;
            margin-top: -70px;
        }
        .circle4
        {
            margin-right: 200px;
            margin-top: 200px;
        }
    </style>

</head>


<body> 
    <div class="div1">

    <div class="circle3"></div>
        <a href="/SendingForm.php" class="a1">
            Заявка
        </a>
        <div class="circle"></div>
        
    </div>

    <div class="div2">
        
        <a href="/ViewingApplications.php" class="a2">
            Просмотр заявок
        </a>
    <div class="circle4"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js" integrity="sha384-W8fXfP3gkOKtndU4JGtKDvXbO53Wy8SZCQHczT5FMiiqmQfUpWbYdTil/SxwZgAN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.min.js" integrity="sha384-skAcpIdS7UcVUC05LJ9Dxay8AXcDYfBJqt1CJ85S/CFujBsIzCIv+l9liuYLaMQ/" crossorigin="anonymous"></script>
</body>

</html>