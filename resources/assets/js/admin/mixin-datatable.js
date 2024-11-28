const dataTable = {
    data() {
        return {
            dataTable: null,
            dataTableFilters: [],
            dataTableAjaxUrl: null,
            isDataTableInitialised: false,
        }
    },

    methods: {
        createDataTable(options) {
            options = Object.assign({
                ajax: {
                    url: this.dataTableAjaxUrl,
                    data: data => {
                        data.filters = this.dataTableFilters
                    }
                },
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [10, 20, 50],
                columnDefs: [
                    {
                        targets: '_all',
                        orderSequence: ['asc', 'desc'],
                        className: "dt-head-left"
                    },
                ],
                stateSave: true,
                stateSaveParams: (settings, data) => {
                    if (this.isDataTableInitialised) {
                        this.saveStateToUrl(settings, data)
                    }
                },
                stateLoadParams: (settings, data) => {
                    return false
                },
            }, options)
            options = this.loadStateFromUrl(options)
            this.dataTable = new DataTable(this.$refs.dataTable, options)
            setTimeout(() => {
                this.isDataTableInitialised = true
            }, 500)
        },

        clearTableFilters() {
            this.dataTable.columns().search(null)
            this.dataTableFilters = []
            this.afterClearTableFilters()
            this.dataTable.ajax.reload()

            /* Clear state in url */
            history.pushState({}, '', location.pathname)
        },

        afterClearTableFilters() {
        },

        applyTableFilter(columnName, value) {
            const filter = this.dataTableFilters.find(filter => filter.name === columnName)

            if (filter) {
                const filterIndex = this.dataTableFilters.indexOf(filter)
                this.dataTableFilters[filterIndex].value = value
            } else {
                this.dataTableFilters.push({
                    name: columnName,
                    value: value
                })
            }

            this.dataTable.ajax.reload()
        },

        applyTableColumnFilter(columnName, value) {
            this.dataTable
                .column(columnName + ':name')
                .search(value)
                .draw()
        },

        saveStateToUrl(settings, data) {
            const url = new URL(location)
            const stateToSaveInUrl = {}

            // Sort
            stateToSaveInUrl.sort = (data.order[0][1] === 'desc' ? '-' : '') + data.order[0][0]

            // Pagination
            if (data.start !== 0 || data.length !== settings.pageLength) {
                stateToSaveInUrl.page = (data.start / data.length) + 1
                stateToSaveInUrl.perPage = data.length
            }

            // Search
            if (data.search.search && data.search.search.length) {
                stateToSaveInUrl.search = data.search.search
            } else {
                url.searchParams.delete('search')
            }

            // Save state to url
            for (let name in stateToSaveInUrl) {
                const value = stateToSaveInUrl[name]
                url.searchParams.set(name, value)
            }

            // Filters
            if (this.dataTableFilters) {
                const filters = JSON.parse(JSON.stringify(this.dataTableFilters))

                for (let index in filters) {
                    const filter = filters[index]

                    url.searchParams.set(filter.name, filter.value)
                }
            }

            history.pushState({}, '', url)
        },

        loadStateFromUrl(options) {
            const stateFromUrl = this.getStateFromCurrentUrl()

            // Sort
            if (stateFromUrl.sort) {
                const sortType = stateFromUrl.sort.includes('-') ? 'desc' : 'asc'
                const sortColumnIndex = parseInt(
                    sortType === 'desc' ? stateFromUrl.sort.substring(1) : stateFromUrl.sort
                )
                options.order = [[sortColumnIndex, sortType]]
            }

            // Pagination
            if (stateFromUrl.perPage >= 0) {
                options.pageLength = stateFromUrl.perPage
            }

            if (stateFromUrl.page >= 0) {
                options.displayStart = (stateFromUrl.page - 1) * stateFromUrl.perPage
            }

            // Search
            if (stateFromUrl.search) {
                options.search = {
                    search: stateFromUrl.search
                }
            }

            // Filters
            if (stateFromUrl.filters) {
                for (let key in stateFromUrl.filters) {
                    const filter = stateFromUrl.filters[key]
                    this.dataTableFilters.push({
                        name: filter.name,
                        value: filter.value
                    })
                }

                this.loadFiltersFromState()
            }

            return options
        },

        getStateFromCurrentUrl() {
            const stateFromUrl = {}
            const uri = new URL(location.href)
            const urlParams = Object.fromEntries(uri.searchParams.entries())

            // Sort
            if (uri.searchParams.get('sort')) {
                stateFromUrl.sort = uri.searchParams.get('sort')
            }

            // Pagination
            if (uri.searchParams.get('page')) {
                stateFromUrl.page = parseInt(uri.searchParams.get('page'))
            }

            if (uri.searchParams.get('perPage')) {
                stateFromUrl.perPage = parseInt(uri.searchParams.get('perPage'))
            }

            // Search
            if (uri.searchParams.get('search')) {
                stateFromUrl.search = uri.searchParams.get('search')
            }

            // Filters
            const filters = []

            for (let name in urlParams) {
                if (['page', 'perPage', 'sort', 'search'].indexOf(name) !== -1) {
                    continue
                }

                filters.push({
                    name: name,
                    value: urlParams[name].includes(',') ? urlParams[name].split(',') : urlParams[name],
                })
            }

            if (filters.length) {
                stateFromUrl.filters = filters
            }

            return stateFromUrl
        },

        loadFiltersFromState() {
        },
    }
}
