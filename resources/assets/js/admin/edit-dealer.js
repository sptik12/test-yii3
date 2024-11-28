VueComponent('#vue-edit-dealer', {
    mixins: [general, dataTable],

    data() {
        return {
            dealer: {},

            user: {},
            initialUserValues: {
                id: '',
                username: '',
                licenseNumber: '',
                role: '',
                phone: '',
                email: '',
                address: '',
                postalCode: '',
                province: '',
                receiveEmails: false
            },

            mode: 'new',
            uploadLogoAjaxUrl: '',
            deleteLogoAjaxUrl: '',
            searchDealersUrl: '',
            addUserToDealerAjaxUrl: '',
            updateUserToDealerAjaxUrl: '',
            areYouSureUnassignMessage: null
        }
    },

    mounted() {
        this.initDefaults()
        this.initHandlers()

        /* Datatable */
        this.createDataTable({
            searching: false,
            paging: false,
            order: {
                name: 'registered',
                'dir': 'desc'
            },
            columnDefs: [
            ],
        })

        document.documentElement.style.scrollBehavior = 'auto'

    },

    methods: {
        initDefaults () {
            Object.keys(this.initialUserValues).forEach(key => {
                if (!this.user.hasOwnProperty(key) || this.user[key] == null) {
                    this.user[key] = this.initialUserValues[key]
                }
            })
        },

        async updateDealer() {
            const form = this.$refs.updateDealerForm
            if (form.reportValidity()) {
                const formData = new FormData(form)
                const data = await ajax('POST', form.action, formData)
                if (data) {
                    location.href = this.searchDealersUrl
                }
            }
        },

        async addUser() {
            const form = this.$refs.userForm
            if (form.reportValidity()) {
                const formData = new FormData(form)
                const data = await ajax('POST', this.addUserToDealerAjaxUrl, formData)
                if (data) {
                    this.closeUserModal()
                    this.dataTable.ajax.reload(null, false)
                }
            }
        },

        async updateUser() {
            const form = this.$refs.userForm
            if (form.reportValidity()) {
                const formData = new FormData(form)
                const data = await ajax('POST', this.updateUserToDealerAjaxUrl, formData)
                if (data) {
                    this.closeUserModal()
                    this.dataTable.ajax.reload(null, false)
                }
            }
        },

        openUserModal() {
            this.mode = 'new'
            this.user = {}
            this.initDefaults()
            const userRole = document.querySelector('.app-user-role')
            userRole.tomselect.setValue(this.user.role ?? '')
            const userProvince = document.querySelector('.app-user-province')
            userProvince.tomselect.setValue(this.user.province)
            bootstrap.Modal.getOrCreateInstance(document.querySelector('.app-add-user-modal')).show()
        },

        closeUserModal() {
            bootstrap.Modal.getInstance(document.querySelector('.app-add-user-modal')).hide()
        },

        initHandlers() {
            document.addEventListener('click', async e => {
                const unassignUserButton = e.target.closest('.app-unassign-user-button')
                if (unassignUserButton) {
                    e.preventDefault()
                    showConfirmNoty(
                        'confirm',
                        `<div>${this.areYouSureUnassignMessage}</div>`,
                        async () => {
                            const formData = new FormData()
                            formData.append('userId', unassignUserButton.dataset.userId)
                            formData.append('dealerId', unassignUserButton.dataset.dealerId)
                            await ajax('POST', unassignUserButton.href, formData)
                            this.dataTable.ajax.reload(null, false)
                        },
                        this.labelYes,
                        this.labelNo
                    )
                }

                const editUserButton = e.target.closest('.app-edit-user-button')
                if (editUserButton) {
                    e.preventDefault()

                    const data = await ajax('GET', editUserButton.href)
                    if (data) {
                        this.user = data.user
                        this.mode = 'edit'
                        const userRole = document.querySelector('.app-user-role')
                        userRole.tomselect.setValue(data.user.role ?? '')
                        const userProvince = document.querySelector('.app-user-province')
                        userProvince.tomselect.setValue(data.user.province)
                        bootstrap.Modal.getOrCreateInstance(document.querySelector('.app-add-user-modal')).show()
                    }
                }

                const setAsPrimaryButton = e.target.closest('.app-set-as-primary-button')
                if (setAsPrimaryButton) {
                    e.preventDefault()

                    const formData = new FormData()
                    formData.append('userId', setAsPrimaryButton.dataset.userId)
                    formData.append('dealerId', setAsPrimaryButton.dataset.dealerId)
                    await ajax('POST', setAsPrimaryButton.href, formData)

                    this.dataTable.ajax.reload(null, false)
                }

            })
        },

        async handleUploadLogo($event) {
            const files = $event.target.files
            if (files.length) {
                let isValid = this.validateUploadingFiles(files)

                if (isValid) {
                    const formData = new FormData()
                    const keys = Object.keys(files)
                    keys.forEach(key => formData.append('files[]', files[key]))
                    formData.append('dealerId', this.dealer.id)
                    this.$refs.uploadLink.classList.add('loading')
                    const data = await ajax('POST', this.uploadLogoAjaxUrl, formData)
                    this.$refs.uploadLink.classList.remove('loading')
                    if (data) {
                        this.dealer = data.dealer
                    }
                }
            }
        },

        async deleteDealerLogo() {
            this.$refs.uploadLink.classList.add('loading')
            const data = await ajax('GET', this.deleteLogoAjaxUrl)
            this.$refs.uploadLink.classList.remove('loading')
            if (data) {
                this.dealer = data.dealer
            }
        }

    }

})
