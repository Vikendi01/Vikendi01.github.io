class Toast
{
    constructor(params)
    {
        this._text = params['text'];
        this._interval =  5000; //интервал в мс., после которого закрывается окно 
        this._create();
        this._el.addEventListener('click', (e) => 
        {
            if (e.target.classList.contains('toast__close'))
            {
                this._hide();
            }
        });
        this._show();
    }
    
    _show()
    {
        this._el.classList.add('toast_show');
            setTimeout(() =>
            {
                this._hide();
            },
                this._interval);
    }

    _hide()
    {
        this._el.classList.remove('toast_show');
        const event = new CustomEvent('hide.toast', { detail: { target: this._el } });
        document.dispatchEvent(event);
    }

    _create()
    {
        const el = document.createElement('div');
        el.classList.add('_toast');
        el.classList.add(`toast_${'danger'}`);
        let html = `<div class="toast__body"></div><button class="toast__close" type="button"></button>`;
        
        el.innerHTML = html;
       
        el.classList.add('toast_message');
       
        el.querySelector('.toast__body').textContent = this._text;
        this._el = el;

        if (!document.querySelector('.toast__container'))
        {
            const container = document.createElement('div');
            container.classList.add('toast__container');
            document.body.append(container);
        }
        document.querySelector('.toast__container').append(this._el);
    }
}