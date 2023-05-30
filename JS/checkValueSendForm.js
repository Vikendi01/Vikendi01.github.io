/* ----------------------------

	Проверка ввода полей

- Отслеживает списка сообщений о неправильности ввода
- Отслеживает, какие проверки необходимо выполнить для этого ввода
- Выполняет проверку ввода и отправляет обратную связь во внешний интерфейс
	
---------------------------- */

function CustomValidation()
{
    
    this.invalidities = []; //массив с ошибками
     
    this.validityChecks = []; //проверка правильности ввода ( приравнивается ниже, к каждому полю индивидуально)
}

CustomValidation.prototype = // прототип
{
    addInvalidity: function (message) //добавить ошибку
    {
        this.invalidities.push(message); //добавляет в массив ошибку (к концу массива), массив находится в функции выше
    },
    
    getInvalidities: function () //получить ошибку
    {
       
        return this.invalidities.join(". \n"); //склеивает массив с ошибками в одну строку 
    },
    
    checkValidity: function (input) //проверка правильности ввода
    {
        var countInvalid = 0; //суммарное количество ошибок

        for (var i = 0; i < this.validityChecks.length; i++)  //цикл, двигается по длинне массива, в котором происходит проверка полей
        {   
            var isInvalid = this.validityChecks[i].isInvalid(input); //переменная функции проверки на ошибку     

            if (isInvalid) //если ошибка
            {
                this.addInvalidity(this.validityChecks[i].invalidityMessage); //добавляем к массиву с ошибками сообщение из функции
               
            }
           
            var Elem2 = this.validityChecks[i].element2;

            if (Elem2)
            {
                if (isInvalid)
                {

                    Elem2.style.border = "solid 2px";
                    Elem2.style.borderColor = "#dc3545" ;
                    countInvalid +=1;
                }
            } 
        }
        if  (countInvalid == 0) //если ошибок в полях нет/ они исправлены - убираем красный
        {
            Elem2.style.borderColor = "none";
        }
    }
};
       
/* ----------------------------

    Массивы проверок для каждого ввода
1. is Invalid() - функция для определения того, соответствует ли ввод определенному требованию
2. сообщение об ошибке, отображаемое, если поле недействительно
3. элемент, в котором указано требование
	
---------------------------- */

var nameValidityChecks = // переменная сообщений об ошибках 
    [
        {
            isInvalid: function (input) //возвращает значение <3
            {
                return input.value.length == 0;
            },
            invalidityMessage: 'Пожалуйста, укажите Ваше имя',
            element2: document.querySelector('.name')
        },
        {
            isInvalid: function (input) //возвращает значение <3
            {
                let len = input.value.length;
                return  len < 3 && len > 0;
            },
            invalidityMessage: 'Имя должно содержать не менее 3 символов',
            element2: document.querySelector('.name')
        },
        {
            isInvalid: function (input)
            {
                let illegalCharacters = input.value.match(/[^\s*A-Za-zА-Яа-яЁё]/g);
                return illegalCharacters ? true : false;
            },
            invalidityMessage: 'Допускаются только буквы',
            element2: document.querySelector('.name')
        }
    ];

var emailValidityChecks =
[
    {
        isInvalid: function (input) 
        {
            return input.value.length < 1;
        },
        invalidityMessage: 'Пожалуйста, укажите Ваш e-mail',
        element2: document.querySelector('.email')
    },
    {
        isInvalid: function (input)
        {
            let typeValidationCheck = input.validity.typeMismatch;
           
            return typeValidationCheck ? true : false;
        }, 
        invalidityMessage: 'Укажите, пожалуйста, корректный email',
        element2: document.querySelector('.email')
    }
];

var telValidityChecks =
    [
        {
            isInvalid: function (input)
            {
                return input.value.length < 1;
            },
            invalidityMessage: 'Пожалуйста, укажите номер телефона',
            element2: document.querySelector('.tel')
        },
        {
            isInvalid: function (input)
            {
                let len = input.value.length;
                return  len > 1 && len < 10;
            },
            invalidityMessage: 'Укажите, пожалуйста, корректный номер телефона',
            element2: document.querySelector('.tel')
        }
    ];

var technikValidityChecks =
    [
        {
            isInvalid: function (input)
            {
                return input.value.length < 1;
            },
            invalidityMessage: 'Пожалуйста, укажите название техники',
            element2: document.querySelector('.technik')
        }
    ];

var brandValidityChecks =
    [
        {
            isInvalid: function (input) {
                return input.value.length < 1;
            },
            invalidityMessage: 'Пожалуйста, укажите название марки',
            element2: document.querySelector('.brand')
        }
    ];

var modelValidityChecks =
    [
        {
            isInvalid: function (input) {
                return input.value.length < 1;
            },
            invalidityMessage: 'Пожалуйста, укажите название модели',
            element2: document.querySelector('.model')
        }
    ];

var descValidityChecks =
    [
        {
            isInvalid: function (input) {
                return input.value.length < 1;
            },
            invalidityMessage: 'Пожалуйста, укажите описание неисправности',
            element2: document.querySelector('.desc')
        }
    ];

var addressValidityChecks =
    [
        {
            isInvalid: function (input) {
                return input.value.length < 1;
            },
            invalidityMessage: 'Пожалуйста, укажите Ваш адрес',
            element2: document.querySelector('.address')
        }
    ];

/* ----------------------------

    Проверка ввода поля
Если ввод неверен, setCustomValidity() передает сообщение для отображения

---------------------------- */

function checkInput(input)
{
    input.CustomValidation.invalidities = []; //массив с ошибками
    input.CustomValidation.checkValidity(input); //вызываем функцию с проверкой полей

    if (input.CustomValidation.invalidities.length == 0 && input.value != '') //если ошибок нет при проверке
    {
        input.setCustomValidity(''); //выводим пустую строку
    }
    else //если ошибки есть
    {   
        var message = input.CustomValidation.getInvalidities(); // присваиваем к переменной метод, который "склеивает" все ошибки из массива в 1 и возвращает
        
        input.setCustomValidity(message); //выводим ошибки

            new Toast
            ({
                text: message
            });
        
            return true;
    }
}

/* ----------------------------

    Проверка на адрес

Проверяет, необходимо ли проверять поле "адрес" в зависимости от нажатой кнопки (у мастера/ у клиента)

---------------------------- */

let ids;
let inputs = [];
function checkAddress()
{
    inputs=[];
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
    
    if (v == "client")
    {
        ids = ['name', 'email', 'tel', 'technik', 'brand', 'model', 'desc', 'address'];
    }
    else //уберем обязательное заполнение поля, если выбран мастер (оно скрыто), чтобы форма отправляла данные
    {
        ids = ['name', 'email', 'tel', 'technik', 'brand', 'model', 'desc'];
    }

    for (let i = 0; i < ids.length; i++)
    {
        inputs[i] = document.getElementById(ids[i]);
        inputs[i].CustomValidation = new CustomValidation();

        switch (i)
        {
            case 0:
                inputs[i].CustomValidation.validityChecks = nameValidityChecks;
                break;
            case 1:
                inputs[i].CustomValidation.validityChecks = emailValidityChecks;
                break;
            case 2:
                inputs[i].CustomValidation.validityChecks = telValidityChecks;
                break;
            case 3:
                inputs[i].CustomValidation.validityChecks = technikValidityChecks;
                break;
            case 4:
                inputs[i].CustomValidation.validityChecks = brandValidityChecks;
                break;
            case 5:
                inputs[i].CustomValidation.validityChecks = modelValidityChecks;
                break;
            case 6:
                inputs[i].CustomValidation.validityChecks = descValidityChecks;
                break;
            case 7:
                inputs[i].CustomValidation.validityChecks = addressValidityChecks;
                break;
        }
    }
}

/* ----------------------------

    Активация

При нажатии на кнопку происходит проверка полей и вывод ошибок, если имеются

break, если нужно, чтобы как только в 1 поле найдены ошибки прекратить вывод (Чтобы не было много окошек)

---------------------------- */

/* var inputs = document.querySelectorAll('input:not([type="submit"])');
var submit = document.querySelector('input[type="submit"');

for (var i = 0; i < inputs.length; i++) {
inputs[i].addEventListener('keyup', function () {
    checkInput(this);
});
} */

function modal() {
    var myModal = new bootstrap.Modal(document.getElementById('exampleModal'), { keyboard: false })
    myModal.show();
}
var submit = document.getElementById('submit');
submit.addEventListener('click', function ()
{
   
    var errorForm = 0; //счетчик ошибок заполнения в полях формы
    for (var i = 0; i < inputs.length; i++)
    {
        var resultСheck = checkInput(inputs[i]);
        if (resultСheck)
        {
            errorForm += 1; 
            //break;
        }
    }
    if (errorForm == 0) //Если в форме 0 ошибок, значит она заполнена верно - вывод модального окна
    {
        modal();
    }
});


