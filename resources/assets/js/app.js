window.addEventListener('load', () => {

    /*Menu toggle*/
    const toggleButton = document.querySelector('.toggle-button')
    const topMenuButton = document.querySelector('.app-mobile-top-menu-button')
    const topMenuCloseButton = document.querySelector('.app-mobile-top-menu-close-button')
    const navMenu = document.querySelector('.menu-block')
    const topMenu = document.querySelector('.top-header')
    const bodyWrapper = document.querySelector('body')

    if (toggleButton) { // add event listener only of button exists
        toggleButton.addEventListener('click', function () {
            if (!this.classList.contains('button-open')) {
                this.classList.add('button-open')
                navMenu.classList.add('show-menu')
                bodyWrapper.classList.add('overflow-hidden')
            } else {
                this.classList.remove('button-open')
                navMenu.classList.remove('show-menu')
                bodyWrapper.classList.remove('overflow-hidden')
                topMenu.classList.remove('show-top-menu')
            }
        })
    }

    if (topMenuButton) { // add event listener only of button exists
        topMenuButton.addEventListener('click', function () {
            topMenu.classList.toggle('show-top-menu')
            bodyWrapper.classList.toggle('overflow-hidden')
        })
    }

    if (topMenuCloseButton) { // add event listener only of button exists
        topMenuCloseButton.addEventListener('click', function () {
            topMenu.classList.toggle('show-top-menu')
            bodyWrapper.classList.toggle('overflow-hidden')
        })
    }


    /* Sub menu */
    const menuItems = document.querySelectorAll('.item-with-children')
    menuItems.forEach(item => {
        const itemLink = item.querySelector('a')
        const itemMenu = item.querySelector('.sub-menu')
        itemLink.addEventListener('click', function () {
            itemMenu.classList.toggle('active')
        })

        document.addEventListener("click", (e) => {
            let target = e.target

            if (!item.contains(target) && itemMenu.classList.contains('active')) {
                itemMenu.classList.remove('active')
            }
        })
    })


    /* Tooltips */
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    /* Tom Select Default */
    document.querySelectorAll('.default-tom-select').forEach(el => {
        new TomSelect(el,{
            allowEmptyOption: true,
            create: false,
            plugins: ['no_backspace_delete'],
            controlInput: null
        })
    })

    document.querySelectorAll('.default-tom-select-no-empty').forEach(el => {
        new TomSelect(el,{
            create: false,
            plugins: ['no_backspace_delete'],
            controlInput: null
        })
    })

    /* Tom Select Default with search*/
    document.querySelectorAll('.default-tom-select-search').forEach(el => {
        new TomSelect(el,{
            create: false
        })
    })

})


showConfirmNoty = function(
    type,
    text,
    callbackYes,
    labelYes = 'Yes',
    labelNo = 'No',
    callbackNo = null,
) {
    const noty = new Noty({
        text: text,
        killer: true,
        type: type,
        buttons: [
            Noty.button(
                labelYes,
                'btn btn-primary m-1',
                async () => {
                    await callbackYes()
                    noty.close()
                }
            ),
            Noty.button(
                labelNo,
                'btn btn-outline m-1',
                async () => {
                    if (callbackNo) {
                        await callbackNo()
                    }
                    noty.close()
                }
            )

        ],
        closeWith: ['button']
    })
    noty.show()
}

updateCarUsersCount  = function(total) {
    document.querySelectorAll('.app-car-users-count').forEach(el => {
        el.innerHTML = total ? total : ''
    })
}

updateCarSearchUrlsCount  = function(total) {
    document.querySelectorAll('.app-car-search-urls-count').forEach(el => {
        el.innerHTML = total ? total : ''
    })
}
