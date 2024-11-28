/**
 * Show/hide form errors
 */
const FormErrors = {
    showAll: function(errors) {
        errors.forEach(error => this.show(error))
    },

    show: function(error) {
        const errorContainers = document.querySelectorAll('.app-error-container[for="' + error.source + '"]')
        let isErrorShowed = false

        if (errorContainers.length) {
            errorContainers.forEach(errorContainer => {
                const form = errorContainer.closest('form')

                if (this.isVisible(form)) {
                    const field = form.querySelector('[name="' + error.source + '"]')

                    if (this.isVisible(field)) {
                        field.classList.add('is-invalid')
                        errorContainer.innerText = error.message
                        errorContainer.style.display = 'block'
                        isErrorShowed = true
                    }
                }
            })
        }

        if (!isErrorShowed) {
            new Noty({
                type: 'error',
                text: error.message,
                timeout: 5000
            }).show()
        }
    },

    hideAll: function() {
        const invalidFields = document.querySelectorAll('.is-invalid[name]')

        if (invalidFields.length) {
            invalidFields.forEach(field => this.hide(field))
        }
    },

    hide: function(field) {
        const form = field.closest('form')
        const errorContainer = form ? form.querySelector('.app-error-container[for="' + field.getAttribute('name') + '"]') : null

        if (field && errorContainer) {
            field.classList.remove('is-invalid')
            errorContainer.innerText = ''
            errorContainer.style.display = 'none'
        }
    },


    isVisible: function(el) {
        return el && el.offsetParent !== null && window.getComputedStyle(el).getPropertyValue('display') !== 'none'
    }
}

const hideError = function(e) {
    const form = e.target.closest('form')
    const name = e.target.getAttribute('name')

    if (!form || !name) {
        return
    }

    const errorContainer = form.querySelector('.app-error-container[for="' + name + '"]')

    if (errorContainer) {
        const form = errorContainer.closest('form')
        const field = form ? form.querySelector('[name="' + name + '"]') : null
        FormErrors.hide(field)
    }
}

document.addEventListener('keyup', hideError)
document.addEventListener('change', hideError)
