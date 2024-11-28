const table = {
    mixins: [general],

    data() {
        return {
            dataAjax: {},
            items: [],
            totalCount: 0,
            filters: {},
            filtersItemsWithCounts: {},
            title: '',

            sort: null,
            sortOrder: null,
            sortValue: null,

            currentPage: null,
            perPage: null,

            withoutHistoryPushState: false,
            lastQueryString: '',
            isFetching: false,

            // specific for page, can be redefined in component
            urlSearch: null,
            urlPush: null,
            initialFilters: {},
            pinnedFilters: {},
            redefinedFilters: {},
        }
    },

    mounted() {
        this.caclulateSortValue()
        this.initEmptyFilters()
        this.afterMounted()
    },

    computed: {
        totalPages: function() {
            return this.totalCount ? Math.ceil(this.totalCount/this.perPage) : null
        },

        showPrevPage: function() {
            return this.currentPage > 1
        },

        showNextPage: function() {
            return this.currentPage < this.totalPages
        },
    },

    watch: {
        sortValue: function() {
            if (this.$refs.sort.tomselect) {
                this.$refs.sort.tomselect.setValue(this.sortValue, true)
            }
        }
    },

    methods: {
        async search (checkLastQueryString = true) {
            if (!this.isFetching) {
                this.isFetching = true
                let queryString = this.prepareQueryString(this.buildQueryObject())

                if (!checkLastQueryString || this.lastQueryString != queryString) {
                    const urlSearch = `${this.urlSearch}?${queryString}`
                    const data = await ajax('GET', urlSearch)

                    if (data) {
                        this.dataAjax = data
                        this.items = data.items
                        this.totalCount = data.totalCount
                        this.filtersItemsWithCounts = data.filtersItemsWithCounts ?? {}

                        Object.keys(this.initialFilters).forEach(key => {
                            if (!data.filters.hasOwnProperty(key)) {
                                data.filters[key] = this.initialFilters[key]
                            }
                        })

                        const keys = Object.keys(data.filters)
                        keys.forEach(key => {
                            if (this.redefinedFilters.hasOwnProperty(key)) {
                                this.filters[key] = data.filters[key]
                            }
                        })

                        this.initEmptyFilters()
                        this.title = data.title ?? {}
                        this.lastQueryString = this.pushState()
                    }
                }

                this.isFetching = false
            } else {
                setTimeout(() => {
                    this.search(checkLastQueryString)
                }, 500)
            }
        },

        prepareQueryString (query) {
            let queryString = []

            for (let key in query) {
                if (Array.isArray(query[key])) {
                    for (let index in query[key]) {
                        queryString.push(encodeURIComponent(key + '[]') + '=' + encodeURIComponent(query[key][index]))
                    }
                } else {
                    if (!this.isEmpty(query[key])) {
                        queryString.push(encodeURIComponent(key) + '=' + encodeURIComponent(query[key]))
                    }
                }
            }

            queryString = queryString.join('&')

            return queryString
        },

        buildQueryObject () {
            return Object.assign(
                {},
                {
                    sort: this.sort,
                    sortOrder: this.sortOrder,
                    page: this.currentPage,
                    perPage: this.perPage,
                },
                this.filters,
            )
        },

        pushState: function() {
            const queryString = this.prepareQueryString(this.buildQueryObject())

            if (!this.withoutHistoryPushState) {
                const urlPush = `${this.urlPush}?${queryString}`
                history.pushState(null, document.title, urlPush)
            }

            return queryString
        },

        caclulateSortValue: function() {
            this.sortValue = (this.sortOrder == 'desc' && this.sort.length) ? ('-' + this.sort) : this.sort
        },

        async changeSort($event, dataAttribute = null) {
            // possible selectSortValue ex: "mileage", "-mileage"
            const selectSortValue = dataAttribute ? $event.target.dataset[dataAttribute]: $event.target.value
            const firstSymbol = selectSortValue.substring(0, 1)
            this.sort = (firstSymbol == '-') ? selectSortValue.substring(1) : selectSortValue
            this.sortOrder = (firstSymbol == '-') ? "desc" : "asc"
            this.currentPage = 1
            await this.search(true)
        },

        async goPrevPage() {
            if (this.currentPage > 1) {
                this.currentPage--
                await this.search(true)
            }
        },

        async goNextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++
                await this.search(true)
            }
        },

         validateFilter($event) {
            const target = $event.target
            return target.reportValidity()
        },

        async applyFiltersWithValidate($event) {
            if (this.validateFilter($event)) {
                this.applyFilters()
            }
        },

        async applyFilters() {
            this.filters = this.removeBlankFilters(this.filters)
            this.initEmptyFilters()

            if (this.validateFilters()) {
                this.beforeApplyFilters()
                this.currentPage = 1
                await this.search(true)
                this.afterApplyFilters()
            }
        },

        initEmptyFilters() {
            Object.keys(this.initialFilters).forEach(key => {
                if (!this.filters.hasOwnProperty(key)) {
                    this.filters[key] = this.initialFilters[key]
                }
            })
        },

        async clearFilter (keys, $event = null) {
            if ($event) {
                $event.stopImmediatePropagation()
            }

            if (Array.isArray(keys)) {
                keys.forEach(key =>{
                    delete this.filters[key]
                })
                this.afterClearFilter(keys)
            } else {
                delete this.filters[keys]
                this.afterClearFilter(keys)
            }

            await this.applyFilters()
        },

        async clearFilterValue (key, value) {
            if (this.filters[key]) {
                this.filters[key] = this.filters[key].filter(filterVal => filterVal != value)
                await this.applyFilters()
            }
        },

        async clearFilterItem (filterItem) {
            switch (filterItem.type) {
                case 'keys':
                    this.clearFilter(filterItem.keys)
                    break
                case 'value':
                    this.clearFilterValue(filterItem.key, filterItem.value)
                    break
                case 'makeModelPair':
                    this.deleteMakeModelPairSelect(filterItem.index)
                    break
            }
        },

        async clearAllFilters () {
            const keys = Object.keys(this.filters)
            keys.forEach(key => {
                if (!this.pinnedFilters.hasOwnProperty(key) || this.pinnedFilters[key] == false) {
                    delete this.filters[key]
                }
            })
            this.afterClearAllFilters()
            await this.applyFilters()
        },

        clearPinnedFilters () {
            const keys = Object.keys(this.pinnedFilters)
            keys.forEach(key => {
                this.pinnedFilters[key] = false
            })
        },

        removeBlankFilters (obj) {
            const result = {}

            for (const key in obj) {
                if (!this.isEmpty(obj[key])) {
                    result[key] = obj[key]
                }
            }

            return result
        },

        displayCount(count, value, key) {
            const sign = (count == 0 || this.isEmpty(this.filters[key]) || this.filters[key].includes(value)) ? '' : '+'
            return `(${sign}${count})`
        },

        // methods below can be redefined in component
        beforeApplyFilters() {

        },

        afterApplyFilters() {

        },

        validateFilters() {
            return true
        },

        afterClearAllFilters() {

        },

        afterClearFilter(key) {

        },

        afterMounted() {

        },
    }
}
