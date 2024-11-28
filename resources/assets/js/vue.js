function VueComponent(mount, component) {
    const vueComponent = Vue.createApp(component)

    vueComponent.directive('init', {
        created(el, binding, vnode) {
            const camelize = s => s.replace(/-./g, x => x[1].toUpperCase())
            const isEmpty = s => ((typeof s === 'string' || s instanceof String) && (s === '' || !s.length)) || (Array.isArray(s) && !s.length) || (typeof s === 'object' && !Object.keys(s).length)

            if (!isEmpty(binding.value)) {
                vnode.ctx.data[camelize(binding.arg)] = binding.value
            }
        },
    })

    // source: https://gist.github.com/lbgm/9dd4e2473ca171d5216b53600925a47f
    vueComponent.directive('typing', {
        beforeMount(el, binding, vnode) {
            let inputTimer
            const inputInterval = binding.value.timing || 500

            for (const evt of ["keydown", "keyup"]) {
                el.addEventListener(evt, event => {
                    if (event.type === "keyup") {
                        if (
                            event.key !== "Backspace" &&
                            typeof binding.value.run === "function"
                        ) {
                            binding.value.run(event)
                        }
                        // is typing running callback
                        else if (binding.value.allowBackspace === true) {
                            binding.value.run(event) // is typing running callback
                        }

                        clearTimeout(inputTimer)
                        inputTimer = setTimeout(() => {
                            if (typeof binding.value.finish === "function")
                                binding.value.finish(event)
                        }, inputInterval) // when typing finished
                    } else if (event.type === "keydown") {
                        clearTimeout(inputTimer)
                    }
                    return null
                })
            }
        }
    })

    /* Add '@clear' for vue-select */
    vueComponent.mixin({
        beforeMount() {
            if (window['vue-select']) {
                window['vue-select'].emits.push('clear')
                window['vue-select'].methods.clearSelection = function () {
                    this.updateValue(this.multiple ? [] : null)
                    this.$emit('clear')
                }
            }
        }
    })

    vueComponent.mount(mount)
}


/**
 * Autofill initial value for elements with v-model attribute
 */
const elementsWithVueModel = document.querySelectorAll('[v-model]')

if (elementsWithVueModel.length) {
    const processedAttributes = []

    elementsWithVueModel.forEach(el => {
        const modelAttributeName = el.getAttribute('v-model')

        if (processedAttributes.indexOf(modelAttributeName) !== -1) {
            return
        }

        const elTagName = el.tagName.toLowerCase()
        let initialValue = null

        switch (elTagName) {
            case 'input':
                const inputType = el.getAttribute('type')
                const isValueAttributeExists = el.getAttribute('value') && el.getAttribute('value').length

                switch (inputType) {
                    case 'file':
                    case 'button':
                    case 'submit':
                    case 'image':
                    case 'reset':
                        // skip this input types
                        break

                    case 'checkbox':
                    case 'radio':
                        if (el.checked) {
                            initialValue = true
                        }
                        break

                    default:
                        if (isValueAttributeExists) {
                            initialValue = el.getAttribute('value')
                        }
                        break
                }
                break

            case 'textarea':
                const value = el.innerHTML.trim()

                if (value && value.length) {
                    initialValue = value
                }
                break
        }

        if (initialValue) {
            el.setAttribute('v-init:' + modelAttributeName, "'" + initialValue.replace(/'/i, "\\'") + "'")
        }

        processedAttributes.push(modelAttributeName)
    })
}
