/* ----------------------------

   Скрытие/отображение - поля для ввода адреса/карта. В зависимости от выбора радиокнопки
	
---------------------------- */

function check() 
{
    var items = document.getElementsByName('place');
    var v = null;
    for (var i = 0; i < items.length; i++)
    {
        if (items[i].checked)
        {
            v = items[i].value;
            break;
        }
    }
    var required = (v == "client");
    document.getElementById("div_address").style.display = required ? "block" : "none";
    document.getElementById("map").style.display = required ? "none" : "block";

    if (required)
    {
        document.getElementById("address").setAttribute("required", true);
    }
    else //уберем обязательное заполнение поля, если выбран мастер (оно скрыто), чтобы форма отправляла данные
    { 
        document.getElementById("address").removeAttribute("required");
    }
}

check();

/* ----------------------------

  Проверка загружаемых изображений

1. Проверка на количество изображений
2. Если проверка пройдена -> проверка на тип файла
3. проверка на размер файла
4. создание превью (мини изображения) для удобства

---------------------------- */

const button = document.getElementById('submit'); //получаем кнопку, чтобы её блокировать при косяках с файлами
const input = document.querySelector('input'); //получаю ссылку на ввод формы
const preview = document.querySelector('.preview'); //получаю ссылку на элемент div .preview

input.style.opacity = 0; //Cкрываем элемент <input> (выглядит некрасиво)

/* Затем мы добавляем прослушиватель событий к входным данным для прослушивания изменений в его выбранных значениях (в данном случае, при выборе файлов)
прослушиватель событий вызывает нашу пользовательскую функцию updateImageDisplay(). */

input.addEventListener('change', updateImageDisplay);

function updateImageDisplay()
{   
    // очищение предыдущего содержимого предварительного просмотра <div>
    while (preview.firstChild)
    {
        preview.removeChild(preview.firstChild);
    }

    const curFiles = input.files; //сохраняем объект списка файлов (выбранных) в переменную
   
    
    if (curFiles.length === 0) //если ни одного фото не выбрано
    {
        //разблок. кнопку
        button.disabled = false;
        button.removeAttribute("disabled");

        const para = document.createElement('p');
        para.textContent = 'В настоящее время для загрузки не выбрано ни одного изображения';
        preview.appendChild(para);
    }
    else if (curFiles.length > 3)
    {
        const para = document.createElement('p');
        para.textContent = 'Можно прикрепить не более 3-х изображений';
        preview.appendChild(para);
    }
    else // фото выбран(о/ы), просматриваем каждое из них, отображая информацию в div
    {
        const list = document.createElement('ol');
        preview.appendChild(list);

        for (const file of curFiles)
        {
            const listItem = document.createElement('li');
            const para = document.createElement('p');

            // проверяем,нужных ли типов загруженные файлы
            if (validFileType(file))
            {
                //разблок. кнопку
                button.disabled = false;
                button.removeAttribute("disabled");

                para.textContent = `${file.name}`; //отображаем имя файла

                //Создаём предварительный просмотр миниатюр изображения, вызвав window.URL.createObjectURL(curfiles[i]), затем вставляем это изображение в элемент списка.
                const image = document.createElement('img');
                image.src = URL.createObjectURL(file);

                listItem.appendChild(image);
                listItem.appendChild(para);
            }

            if (!validSizeFile(file)) //если размер больше 2мб
            {
                para.textContent = `${file.name}: Размер файла больше 2 МБ. Обновите свой выбор.`;
                listItem.appendChild(para);
                button.disabled = true; //блокируем кнопку
            }

            if (!validFileType(file)) //если тип файла не фото
            {
                para.textContent = `${file.name}: Недопустимый тип файла. Обновите свой выбор.`;
                listItem.appendChild(para);
                button.disabled = true; //блокируем кнопку
            }

            list.appendChild(listItem);
        }
    }
}

const fileTypes = ['image/jpeg', 'image/jpg', 'image/png']; //разрешенные типы файлов

//просматривает список разрешенных типов файлов, проверяя, соответствует ли какой-либо из них свойству типа файла. Если найдено совпадение,  - true. 
function validFileType(file)
{
    return fileTypes.includes(file.type);
}

//проверка на размер файла (не больше 2мб)
function validSizeFile(file)
{
    if (file.size > 2 * 1024 * 1024)
    {
        return false;
    }
    else
    {
        return true;
    }
}

/* ----------------------------

  Маска для номера телефона

---------------------------- */

$(function () {
    $("#tel").mask("+7(999) 999-9999", { placeholder: "_" });
});