VueComponent('#vue-auth', {
    data() {
        return {
            activeItem: this.getStartActiveItem(),
            isCodeSent: false,
            email: null,
            buttonText: null,
        }
    },

    mounted() {
        this.buttonText = this.$refs.btnSendOrSubmitCode.dataset.textSendCode
    },

    methods: {
        isActive(tabItem) {
            return this.activeItem === tabItem
        },
        setActive(tabItem) {
            this.activeItem = tabItem
            window.history.pushState({}, "", (tabItem === 'sign-in-code') ? 'sign-in#by-code' : 'sign-in')

            if (tabItem === 'sign-in-password') {
                this.isCodeSent = false
                this.buttonText = this.$refs.btnSendOrSubmitCode.dataset.textSendCode
            }
        },
        getStartActiveItem() {
            return window.location.href.includes('by-code') ? 'sign-in-code' : 'sign-in-password'
        },
        async sendOrSubmitCode() {
            const formAuthByCode = this.$refs.formAuthByCode
            const btnSendOrSubmitCode = this.$refs.btnSendOrSubmitCode

            if (!this.isCodeSent) {
                if (formAuthByCode.reportValidity()) {
                    btnSendOrSubmitCode.disabled = true
                    const formData = new FormData(formAuthByCode)
                    const data = await ajax('POST', formAuthByCode.action, formData)

                    if (data) {
                        this.isCodeSent = data.isCodeSent
                        this.buttonText = this.$refs.btnSendOrSubmitCode.dataset.textSignIn
                        this.$nextTick(() => {
                            if (this.$refs.code) {
                                this.$refs.code.focus()
                            }
                        })
                    }
                    btnSendOrSubmitCode.disabled = false
                }
            }
            else {
                if (formAuthByCode.reportValidity()) {
                    btnSendOrSubmitCode.disabled = true
                    const formData = new FormData(formAuthByCode)
                    const data = await ajax('POST', formAuthByCode.action, formData)

                    if (data) {
                        location.href = data.redirectUrl
                    }

                    btnSendOrSubmitCode.disabled = false
                }
            }
        },

    }
})
