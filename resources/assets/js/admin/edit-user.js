VueComponent('#vue-edit-user', {
    mixins: [dataTable],

    data() {
        return {
            user: {},

            isDealerRole: false,
            isAccountManagerRole: false,

            searchUsersUrl: '',
            deleteUserAjaxUrl: '',
            validateDeleteUserAjaxUrl: '',
            setUserDeletionDateAjaxUrl: '',
            areYouSureDeleteMessage: null,
            areYouSureUnassignMessage: null
        }
    },

    mounted() {
        this.initHandlers()

        /* Datatable */
        this.createDataTable({
            searching: false,
            order: {
                name: 'role',
                'dir': 'desc'
            },
            columnDefs: [
                {
                    targets:'_all',
                    orderable: false
                }
            ],
        })

        document.documentElement.style.scrollBehavior = 'auto'
    },

    methods: {
        async updateUser() {
            const form = this.$refs.updateUserForm
            if (form.reportValidity()) {
                const formData = new FormData(form)
                const data = await ajax('POST', form.action, formData)
                if (data) {
                    location.href = this.searchUsersUrl
                }
            }
        },

        async deleteUser(userId) {
            const formData = new FormData()
            formData.append('userId', userId)
            this.$refs.deleteUserButton.classList.add('loading')
            const data = await ajax('POST', this.validateDeleteUserAjaxUrl, formData)
            this.$refs.deleteUserButton.classList.remove('loading')
            if (data.messages.length == 0) {
                showConfirmNoty(
                    'confirm',
                    `<div>${this.areYouSureDeleteMessage}</div>`,
                    async () => {
                        const formData = new FormData()
                        formData.append('userId', userId)
                        this.$refs.deleteUserButton.classList.add('loading')
                        const data = await ajax('POST', this.setUserDeletionDateAjaxUrl, formData)
                        this.$refs.deleteUserButton.classList.remove('loading')
                        if (data) {
                            location.href = this.searchUsersUrl
                        }
                    },
                    this.labelYes,
                    this.labelNo
                )
            }
            else {
                new Noty({
                    type: 'warning',
                    text: data.messages.join('<br><br>'),
                }).show()
            }
        },




        openUserRoleModal() {
            const userRole = document.querySelector('.app-user-role')
            userRole.tomselect.setValue('')
            const userDealer = document.querySelector('.app-user-dealer')
            userDealer.tomselect.setValue('')
            bootstrap.Modal.getOrCreateInstance(document.querySelector('.app-add-user-role-modal')).show()
        },

        closeUserRoleModal() {
            bootstrap.Modal.getInstance(document.querySelector('.app-add-user-role-modal')).hide()
        },

        checkRole() {
            this.isDealerRole = this.$refs.role.value.startsWith('dealer')
            this.isAccountManagerRole = this.$refs.role.value == 'admin.accountManager'
        },

        async addRole() {
            const form = this.$refs.roleForm
            if (form.reportValidity()) {
                const formData = new FormData(form)
                const data = await ajax('POST', form.action, formData)
                if (data) {
                    this.closeUserRoleModal()
                    this.user = data.user
                    const userProvince = document.querySelector('.app-user-province')
                    userProvince.tomselect.setValue(this.user.province)
                    this.dataTable.ajax.reload(null, false)
                }
            }
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
                            formData.append('role', unassignUserButton.dataset.role)
                            const data = await ajax('POST', unassignUserButton.href, formData)
                            if (data) {
                                this.user = data.user
                                this.dataTable.ajax.reload(null, false)
                            }
                        },
                        this.labelYes,
                        this.labelNo
                    )
                }
            })

        },

    }

})
