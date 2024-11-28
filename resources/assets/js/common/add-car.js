VueComponent('#vue-add-car', {
    data() {
        return {
            car: {},
        }
    },

    mounted() {
    },

    methods: {
        submitForm($event) {
            const addCarForm = this.$refs.addCarForm
            if (addCarForm.reportValidity()) {
                $event.target.disabled = true
                addCarForm.submit()
            }
        }
    }
})


window.addEventListener('load', () => {
})
