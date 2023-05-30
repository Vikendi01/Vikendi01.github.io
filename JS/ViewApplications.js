/* ----------------------------

    Функция отправки запроса на отправление сообщения по эл. почте

---------------------------- */

function send(event, php)
{
    console.log("Отправка запроса");
    event.preventDefault ? event.preventDefault() : event.returnValue = false;
    var req = new XMLHttpRequest();
    req.open('POST', php, true);

    req.onload = function ()
    {
        if (req.status >= 200 && req.status < 400)
        {
            json = JSON.parse(this.response);
            console.log(json);


            if (json.result == "success")
            {
                // Если сообщение отправлено
                alert("Сообщение отправлено");
            }
            else
            {
                // Если произошла ошибка
                alert("Ошибка. Сообщение не отправлено");
            }
            
        }
        // Если не удалось связаться с php файлом
        else
        {
            alert("Ошибка сервера. Номер: " + req.status);
        }
    };

    // Если не удалось отправить запрос. Стоит блок на хостинге
    req.onerror = function ()
    {
        alert("Ошибка отправки запроса");
    };
    req.send(new FormData(event.target));
}



/* ----------------------------

    Скрыть кнопку "отправить письмо" после нажатия на неё 1 раз, чтобы случайно не отправить несколько писем подряд

---------------------------- */

function hide_sendbutton(obj)
{
    document.getElementById(obj.id).hidden = true;
}

/* ----------------------------

    При выборе статуса отображать кнопку "изменить статус"

---------------------------- */

function ChangeStatus_show(id)
{
    document.getElementById(id).style.visibility = "visible"; 
}

function ChangeStatus_hide(id) {
    document.getElementById(id).style.visibility = "hidden";
}

/* ----------------------------

    Смена темы
1. Переменные с элементами, которым нужно сменить класс
2. Смена значка и класса html
3. Вызов ф-ций со сменой классов оставшихся элементов
4. сохранение текущей темы - передеча в сессию

---------------------------- */

var image = document.getElementById("imageSub");
var html = document.getElementById("html");
var navbar = document.getElementById("navbar");
var modal = document.getElementById("modal");
var butt_modal = document.getElementById("butt_modal");
var close_but = document.getElementById("butt_close");

function imgsrc()
{
    
    var theme = "";
    

    if (html.classList.contains("dark-mode"))
    {
        themeLight();
        theme = "light";
    }
    else
    {  
        themeDark();
        theme = "dark";
    }
    Save(theme);
    
} 

function Save(theme)
{   
    var Request = new XMLHttpRequest();
    Request.open("GET", "/themes.php?theme=" + theme, true); 
    Request.send();
    
}

function themeDark()
{
    image.src='./Images/starss.svg';
    html.classList.add("dark-mode");

    navbar.classList.remove("navbar-light");
    navbar.classList.remove("bg-light");

    navbar.classList.add("bg-dark");
    navbar.classList.add("navbar-dark");

    if (modal != null)
    {
        modal.classList.add("bg-dark");
    }

    if (butt_modal != null)
    {
        butt_modal.classList.remove("btn-outline-dark");
        butt_modal.classList.add("btn-light");
    }

    if (close_but != null)
    {
        close_but.classList.add("btn-close-white");
    }
}

function themeLight()
{
    image.src='./Images/sun.svg';
    html.classList.remove("dark-mode");

    navbar.classList.remove("bg-dark");
    navbar.classList.remove("navbar-dark");

    navbar.classList.add("navbar-light");
    navbar.classList.add("bg-light");

    if (modal != null)
    {
        modal.classList.remove("bg-dark");
    }

    if (butt_modal != null)
    {
        butt_modal.classList.remove("btn-dark");
        butt_modal.classList.add("btn-outline-dark");
    }

    if (close_but != null)
    {
        close_but.classList.remove("btn-close-white");
    }

}