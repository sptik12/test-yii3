VueComponent('#vue-users', {
    mixins: [dataTable],

    data() {
        return {
            labelConfirmSuspendUser: null,
            labelNotifyUser: null,
            labelYes: null,
            labelNo: null,
            labelConfirm: null,
            labelCancel: null,
            labelAreYouSureDelete: null,
            labelAreYouSureSendCode: null,
            labelAreYouSureRefuseDeletion: null,
            messageUserSuspended: null,
            messageUserUnsuspended: null,
            messageSuccessSendCode: null,
        }
    },

    mounted() {
        this.createDataTable({
            order: {
                name: 'created',
                dir: 'desc'
            },
        })
        this.initHandlers()
    },

    methods: {
        applyRegisteredFilter() {
            this.applyTableFilter('registered', [this.$refs.dateFilterFrom.value, this.$refs.dateFilterTo.value])
        },

        afterClearTableFilters() {
            this.$refs.deletionDate.checked = false
        },

        loadFiltersFromState() {
            if (this.dataTableFilters) {
                this.dataTableFilters.forEach(filter => {
                    switch (filter.name) {
                        case "status":
                            this.$refs.statusFilter.value = filter.value
                            break
                        case "role":
                            this.$refs.roleFilter.value = filter.value
                            break
                        case "deletionDate":
                            if (filter.value == 1) {
                                this.$refs.deletionDate.checked = true
                            }
                            break
                        case "registered":
                            document.querySelector('.app-date-from-filter').value = filter.value[0]
                            document.querySelector('.app-date-to-filter').value = filter.value[1]
                            break
                    }
                })
            }
        },

        initHandlers() {

            /**
             *  Date of registration filter
             */
            flatpickr(this.$refs.dateFilterFromFlatpickr, {
                dateFormat: 'Y-m-d',
                wrap: true,
                onChange:  this.applyRegisteredFilter
            })

            flatpickr(this.$refs.dateFilterToFlatpickr, {
                dateFormat: 'Y-m-d',
                wrap: true,
                onChange:  this.applyRegisteredFilter
            })

            /**
             * Roles filter
             */
            if (this.$refs.roleFilter) {
                this.$refs.roleFilter.addEventListener('change', () => {
                    this.applyTableFilter('role', this.$refs.roleFilter.value)
                })
            }

            /**
             * Status filter
             */
            if (this.$refs.statusFilter) {
                this.$refs.statusFilter.addEventListener('change', () => {
                    this.applyTableFilter('status', this.$refs.statusFilter.value)
                })
            }

            if (this.$refs.deletionDate) {
                this.$refs.deletionDate.addEventListener('change', () => {
                    this.applyTableFilter('deletionDate', this.$refs.deletionDate.checked ? 1 : 0)
                })
            }


            /* Delete user button handler */
            document.addEventListener('click', async e => {

                const deleteUserButton = e.target.closest('.app-delete-button')

                if (deleteUserButton) {
                    e.preventDefault()
                    showConfirmNoty(
                        'confirm',
                        `<div>${this.labelAreYouSureDelete}</div>`,
                        async () => {
                            const formData = new FormData()
                            formData.append('userId', deleteUserButton.dataset.userId)
                            deleteUserButton.classList.add('loading')
                            const data = await ajax('POST', deleteUserButton.href, formData)
                            deleteUserButton.classList.remove('loading')
                            this.dataTable.ajax.reload(null, false)
                        },
                        this.labelYes,
                        this.labelNo
                    )
                }


                const refuseDeletionUserButton = e.target.closest('.app-refuse-deletion-user-button')

                if (refuseDeletionUserButton) {
                    e.preventDefault()
                    showConfirmNoty(
                        'confirm',
                        `<div>${this.labelAreYouSureRefuseDeletion}</div>`,
                        async () => {
                            const formData = new FormData()
                            formData.append('userId', refuseDeletionUserButton.dataset.userId)
                            refuseDeletionUserButton.classList.add('loading')
                            const data = await ajax('POST', refuseDeletionUserButton.href, formData)
                            refuseDeletionUserButton.classList.remove('loading')
                            this.dataTable.ajax.reload(null, false)
                        },
                        this.labelYes,
                        this.labelNo
                    )
                }


                /* Send one time code user button handler */
                const sendCodeButton = e.target.closest('.app-send-code-button')

                if (sendCodeButton) {
                    e.preventDefault()
                    showConfirmNoty(
                        'confirm',
                        `<div>${this.labelAreYouSureSendCode}</div>`,
                        async () => {
                            const formData = new FormData()
                            formData.append('email', sendCodeButton.dataset.email)
                            sendCodeButton.classList.add('loading')
                            const data = await ajax('POST', sendCodeButton.href, formData)
                            sendCodeButton.classList.remove('loading')
                            if (data) {
                                new Noty({
                                    type: 'success',
                                    killer: true,
                                    text: this.messageSuccessSendCode,
                                    timeout: 2000
                                }).show()
                            }
                        },
                        this.labelYes,
                        this.labelNo
                    )
                }

                /* Suspend user button handler */
                const suspendUserButton = e.target.closest('.app-suspend-button')

                if (suspendUserButton) {
                    e.preventDefault()
                    showConfirmNoty(
                        'confirm',
                        `<div>${this.labelConfirmSuspendUser.replace('{userName}',suspendUserButton.dataset.userName)}</div>` +
                        '<div class="mt-2"><input class="form-check-input" type="checkbox" value="1" id="notifyUser" checked>&nbsp;' +
                        `<label class="form-check-label" for="notifyUser">${this.labelNotifyUser}</label></div>`,
                        async () => {
                            const formData = new FormData()
                            formData.append('userId', suspendUserButton.dataset.userId)
                            formData.append('notifyUser', document.querySelector('#notifyUser').checked ? 1 : 0)
                            suspendUserButton.classList.add('loading')
                            const data = await ajax('POST', suspendUserButton.href, formData)
                            suspendUserButton.classList.remove('loading')
                            if (data) {
                                new Noty({
                                    type: 'success',
                                    killer: true,
                                    text: this.messageUserSuspended.replace('{userName}',suspendUserButton.dataset.userName),
                                    timeout: 2000
                                }).show()
                                this.dataTable.ajax.reload(null, false)
                            }
                        },
                        this.labelConfirm,
                        this.labelCancel
                    )
                }

                /* Unsuspend user button handler */
                const unsuspendUserButton = e.target.closest('.app-unsuspend-button')

                if (unsuspendUserButton) {
                    e.preventDefault()
                    unsuspendUserButton.classList.add('loading')
                    const formData = new FormData()
                    formData.append('userId', unsuspendUserButton.dataset.userId)
                    const data = await ajax('POST', unsuspendUserButton.href, formData)
                    unsuspendUserButton.classList.remove('loading')
                    if (data) {
                        new Noty({
                            type: 'success',
                            text: this.messageUserUnsuspended.replace('{userName}',unsuspendUserButton.dataset.userName),
                            timeout: 2000
                        }).show()
                        this.dataTable.ajax.reload(null, false)
                    }
                }

            })

        },

        clearFilters() {
            document.querySelectorAll('.app-table-filter').forEach(tableFilter => {
                if (tableFilter.tomselect) {
                    tableFilter.tomselect.setValue('', true)
                } else {
                    tableFilter.value = ''
                }
            })
            this.clearTableFilters()
        },

    }
})
