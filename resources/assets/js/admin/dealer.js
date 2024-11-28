VueComponent('#vue-dealers', {
    mixins: [dataTable],

    data() {
        return {
            assignAccountManagersUrlAjax: null,
            hasCheckboxColumn: false,
            labelConfirmApproveDealer: null,
            labelConfirmSuspendDealer: null,
            labelConfirmUnsuspendDealer: null,
            labelYes: null,
            labelNo: null,
            labelConfirm: null,
            labelApprove: null,
            labelCancel: null,
            messageDealerApproved: null,
            messageDealerSuspended: null,
            messageDealerUnsuspended: null,
        }
    },

    mounted() {
        this.createDataTable({
            order: {
                name: 'created',
                dir: 'desc'
            },
            columnDefs: this.hasCheckboxColumn ? [
                {
                    targets: '_all',
                    orderSequence: ['asc', 'desc']
                },
                {
                    targets: 0,
                    searchable: false,
                    orderable: false,
                    className: 'dt-body-center',
                    render: function (data, type, full, meta){
                        return `<input type="checkbox" name="id[]" class="app-checkbox-column" value="${data}">`
                    }
                }
            ] : [],
        })

        this.$nextTick(() => {
            this.initHandlers()
        })
    },

    methods: {
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

        loadFiltersFromState() {
            if (this.dataTableFilters) {
                this.dataTableFilters.forEach(filter => {
                    switch (filter.name) {
                        case 'status':
                            this.$refs.statusFilter.value = filter.value
                            break

                        case 'registered':
                            this.$refs.dateFilterFrom.value = filter.value[0]
                            this.$refs.dateFilterTo.value = filter.value[1]
                            break

                        case 'accountManager':
                            this.$refs.accountManagerFilter.value = filter.value
                            break
                    }
                })
            }
        },

        applyRegisteredFilter() {
            this.applyTableFilter('registered', [this.$refs.dateFilterFrom.value, this.$refs.dateFilterTo.value])
        },

        initHandlers() {
            /**
             *  Date of registration filter
             */
            flatpickr(this.$refs.dateFilterFromFlatpickr, {
                dateFormat: 'Y-m-d',
                wrap: true,
                onChange: this.applyRegisteredFilter

            })

            flatpickr(this.$refs.dateFilterToFlatpickr, {
                dateFormat: 'Y-m-d',
                wrap: true,
                onChange: this.applyRegisteredFilter
            })

            /**
             * Status filter
             */
            this.$refs.statusFilter.addEventListener('change', () => {
                this.applyTableFilter('status', this.$refs.statusFilter.value)
            })

            /**
             * Account Manager filter
             */
            if (this.$refs.accountManagerFilter) {
                this.$refs.accountManagerFilter.addEventListener('change', () => {
                    this.applyTableFilter('accountManager', this.$refs.accountManagerFilter.value)
                })
            }

            document.addEventListener('click', async e => {

                /* Header checkbox */
                const selectAllCheckboxes = e.target.closest('.app-select-all-checkboxes')
                if (selectAllCheckboxes) {
                    document.querySelectorAll('.app-checkbox-column').forEach(el => {
                        el.checked = selectAllCheckboxes.checked
                    })
                    document.querySelector('.app-assign-account-manager-btn').disabled = !selectAllCheckboxes.checked
                    document.querySelectorAll('.app-assign-account-manager option').forEach(opt => {
                        if (opt.value != '') {
                            opt.disabled = !selectAllCheckboxes.checked
                        }
                    })
                }

                /* Column checkboxes */
                const checkboxColumn = e.target.closest('.app-checkbox-column')
                if (checkboxColumn) {
                    const hasChecked = document.querySelectorAll('.app-checkbox-column:checked').length > 0
                    document.querySelector('.app-assign-account-manager-btn').disabled = !hasChecked
                    document.querySelectorAll('.app-assign-account-manager option').forEach(opt => {
                        if (opt.value != '') {
                            opt.disabled = !hasChecked
                        }
                    })
                }


                /* login as Dealer */
                const loginAsDealerButton = e.target.closest('.app-login-as-dealer-button')

                if (loginAsDealerButton) {
                    e.preventDefault()
                    const data = await ajax('GET', loginAsDealerButton.href)
                    if (data) {
                        location.href = data.redirectUrl
                    }
                }

                /* Approve dealer button handler */
                const approveDealerButton = e.target.closest('.app-approve-button')

                if (approveDealerButton) {
                    e.preventDefault()
                    showConfirmNoty(
                        'confirm',
                        `<div>${this.labelConfirmApproveDealer.replace('{dealerName}',approveDealerButton.dataset.dealerName)}</div>`,
                        async () => {
                            const formData = new FormData()
                            formData.append('dealerId', approveDealerButton.dataset.dealerId)
                            approveDealerButton.classList.add('loading')
                            const data = await ajax('POST', approveDealerButton.href, formData)
                            approveDealerButton.classList.remove('loading')
                            if (data) {
                                new Noty({
                                    type: 'success',
                                    killer: true,
                                    text: this.messageDealerApproved.replace('{dealerName}',approveDealerButton.dataset.dealerName),
                                    timeout: 2000
                                }).show()
                                this.dataTable.ajax.reload(null, false)
                            }
                        },
                        this.labelApprove,
                        this.labelCancel
                    )
                }


                /* Suspend dealer button handler */
                const suspendDealerButton = e.target.closest('.app-suspend-button')

                if (suspendDealerButton) {
                    e.preventDefault()
                    showConfirmNoty(
                        'confirm',
                        `<div>${this.labelConfirmSuspendDealer.replace('{dealerName}',suspendDealerButton.dataset.dealerName)}</div>`,
                        async () => {
                            const formData = new FormData()
                            formData.append('dealerId', suspendDealerButton.dataset.dealerId)
                            suspendDealerButton.classList.add('loading')
                            const data = await ajax('POST', suspendDealerButton.href, formData)
                            suspendDealerButton.classList.remove('loading')
                            if (data) {
                                new Noty({
                                    type: 'success',
                                    killer: true,
                                    text: this.messageDealerSuspended.replace('{dealerName}',suspendDealerButton.dataset.dealerName),
                                    timeout: 2000
                                }).show()
                                this.dataTable.ajax.reload(null, false)
                            }
                        },
                        this.labelConfirm,
                        this.labelCancel
                    )
                }

                /* Unsuspend dealer button handler */
                const unsuspendDealerButton = e.target.closest('.app-unsuspend-button')

                if (unsuspendDealerButton) {
                    e.preventDefault()
                    showConfirmNoty(
                        'confirm',
                        `<div>${this.labelConfirmUnsuspendDealer.replace('{dealerName}',unsuspendDealerButton.dataset.dealerName)}</div>`,
                        async () => {
                            const formData = new FormData()
                            formData.append('dealerId', unsuspendDealerButton.dataset.dealerId)
                            unsuspendDealerButton.classList.add('loading')
                            const data = await ajax('POST', unsuspendDealerButton.href, formData)
                            unsuspendDealerButton.classList.remove('loading')
                            if (data) {
                                new Noty({
                                    type: 'success',
                                    killer: true,
                                    text: this.messageDealerUnsuspended.replace('{dealerName}',unsuspendDealerButton.dataset.dealerName),
                                    timeout: 2000
                                }).show()
                                this.dataTable.ajax.reload(null, false)
                            }
                        },
                        this.labelConfirm,
                        this.labelCancel
                    )
                }
            })

        },

        async assignAccountManager() {
            if (this.$refs.accountManagerAssign.value) {
                const formData = new FormData()
                formData.append('accountManagerId', this.$refs.accountManagerAssign.value)
                document.querySelectorAll('.app-checkbox-column:checked').forEach(el => {
                    formData.append('dealersIds[]', el.value)
                })
                this.$refs.accountManagerAssignButton.classList.add('loading')
                const data = await ajax('POST', this.assignAccountManagersUrlAjax, formData)
                this.$refs.accountManagerAssignButton.classList.remove('loading')
                if (data) {
                    this.dataTable.ajax.reload(null, false)
                    this.$refs.accountManagerAssignButton.classList.remove('loading')
                    document.querySelector('.app-assign-account-manager-btn').disabled = true
                    document.querySelectorAll('.app-assign-account-manager option').forEach(opt => {
                        if (opt.value != '') {
                            opt.disabled = true
                        }
                    })
                    document.querySelector('.app-assign-account-manager').value = ''
                }
            }
        }
    }
})
