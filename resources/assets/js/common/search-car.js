VueComponent('#vue-search-car', {

    mixins: [table],

    components: {
        vSelect: window['vue-select']
    },

    data() {
        return {
            routeName: null,
            getModelsAjaxUrl: null,
            getPostalCodeAjaxUrl: null,
            setGeoDataForPostalCodeAjaxUrl: null,
            addCarToWishlistAjaxUrl: null,
            removeCarFromWishlistAjaxUrl: null,
            addCarSearchUrlAjaxUrl: null,
            updateCarSearchUrlAjaxUrl: null,
            removeCarSearchUrlAjaxUrl: null,
            deleteCarSearchUrlAjaxUrl: null,
            restoreCarSearchUrlAjaxUrl: null,
            checkCarSearchUrlAjaxUrl: null,

            // redefine initialFilters from mixin-table.js
            initialFilters: {
                make: '',
                model: '',
                makeModelPairs: [],
                models: [],
                bodyType: [],
                condition: 'any',
                transmission: [],
                drivetrain: [],
                fuelType: [],
                feature: [],
                doors: [],
                seats: [],
                cabinSize: [],
                bedSize: [],
                intColor: [],
                extColor: [],
                safetyRating: [],
                minYear: '',
                maxYear: '',
                distance: '',
                postalCode: null,
                status: []
            },

            // redefine pinnedFilters from mixin
            pinnedFilters: {
                dealer: null
            },

            // redefine redefinedFilters from mixin
            redefinedFilters: {
                minYear: null,
                maxYear: null
            },

            makeModelPairsSelects: [],
            makes: [],
            models: [],

            // current values in input fields
            inputValues: {
                postalCode: ''
            },

            allDisplayedFilters: [],
            labelFrom: null,
            labelTo: null,
            labelCurrency: null,
            labelMileage: null,
            labelEngine: null,
            labelFuelEconomy: null,
            labelCo2: null,
            labelEvBatteryRange: null,
            labelEvBatteryTime: null,
            labelDaysOnMarket: null,
            labelExterior: null,
            labelInterior: null,
            labelDoors: null,
            labelSeats: null,
            labelAway: null,
            labelNew: null,
            labelUsed: null,
            labelProvincial : null,
            labelNational: null,

            messageCarSavedToWishlist: null,
            messageCarRemovedFromWishlist: null,

            carSearchUrl: {
                id: null,
                url: '',
                title: '',
                filters: []
            },
            labelCarSearchUrlTitleOther: null,
            messageCarSearchUrlAdded: null,
            messageCarSearchUrlUpdated: null,
            messageCarSearchUrlRemoved: null,
            messageCarSearchUrlRestored: null,
            labelCarSearchUrlDeleted: null,

            restoreSearchUrlTimers: { }
        }
    },

    computed: {
        filtersCount () {
            const keys = Object.keys(this.filters)
            let count = 0
            keys.forEach(key => {
                switch (key) {
                    case "minPrice":
                    case "maxPrice":
                    case "minYear":
                    case "maxYear":
                    case "minEngine":
                    case "maxEngine":
                    case "minFuelEconomy":
                    case "maxFuelEconomy":
                    case "minCo2":
                    case "maxCo2":
                    case "minEvBatteryRange":
                    case "maxEvBatteryRange":
                    case "minEvBatteryTime":
                    case "maxEvBatteryTime":
                    case "minDaysOnMarket":
                    case "maxDaysOnMarket":
                    case "postalCode":
                    case "make":
                    //case "model":
                        if (!this.isEmpty(this.filters[key], false)) {
                            count++
                        }
                        break;

                    case "condition":
                        if (this.filters.condition !== 'any') {
                            count++
                        }
                        break

                    case "minMileage":
                    case "maxMileage":
                        if (this.filters.condition !== 'new') {
                            count++
                        }
                        break

                    case "bodyType":
                    case "transmission":
                    case "drivetrain":
                    case "fuelType":
                    case "feature":
                    case "doors":
                    case "cabinSize":
                    case "bedSize":
                    case "seats":
                    case "extColor":
                    case "intColor":
                    case "safetyRating":
                    case "status":
                    case "makeModelPairs":
                        count += this.filters[key].length
                        break

                    case "certifiedPreOwned":
                    case "withPhotos":
                    case "priceDrops":
                        if (this.filters[key] === true) {
                            count++
                        }
                        break
                }
            })
            return count
        },

        isPostalCodeChanged() {
            return this.filters.postalCode != this.inputValues.postalCode
        },

    },

    watch: {
    },

    methods: {
        afterMounted() {
            if (this.filters.condition === 'new') {
                delete this.filters['minMileage']
                delete this.filters['maxMileage']
            }
            this.createTextListOfFilters()
            this.inputValues.postalCode = this.filters.postalCode
            if (!this.carSearchUrl) {
                this.clearCarSearchUrl()
            }
        },

        beforeApplyFilters() {
            this.$refs.cardItemsList.classList.add('loading-bg')

            if (this.sort === "distance" && !this.filters.postalCode) {
                this.sort = "car.published"
                this.sortOrder = "desc"
                this.caclulateSortValue()
            }

            if (this.filters.condition === 'new') {
                delete this.filters['minMileage']
                delete this.filters['maxMileage']
            }

            if (!this.filters.postalCode) {
                delete this.filters['distance']
            }

            this.checkDiapason('minYear', 'maxYear')
            this.checkDiapason('minPrice', 'maxPrice')
            this.checkDiapason('minMileage', 'maxMileage')
            this.checkDiapason('minEngine', 'maxEngine')
            this.checkDiapason('minFuelEconomy', 'maxFuelEconomy')
            this.checkDiapason('minCo2', 'maxCo2')
            this.checkDiapason('minEvBatteryRange', 'maxEvBatteryRange')
            this.checkDiapason('minEvBatteryTime', 'maxEvBatteryTime')
            this.checkDiapason('minDaysOnMarket', 'maxDaysOnMarket')

            this.createTextListOfFilters()
        },

        afterApplyFilters() {
            this.$nextTick(() => {
                this.$refs.cardItemsList.classList.remove('loading-bg')
            })
            this.createTextListOfFilters()
            this.carSearchUrl = this.dataAjax.carSearchUrl ?? {}
            if (!this.carSearchUrl) {
                this.clearCarSearchUrl()
            }
        },

        validateFilters() {
            return true
        },

        afterClearAllFilters() {
            this.makeModelPairsSelects = []
            this.models = []
            const keys = Object.keys(this.inputValues)
            keys.forEach(key => {
                this.inputValues[key] = ''
            })

            if (this.sort == "distance") {
                this.sort = "car.published"
                this.sortOrder = "desc"
                this.caclulateSortValue()
            }

            const appDistance = document.querySelector('.app-distance')
            if (appDistance) {
                appDistance.tomselect.setValue('')
            }

        },

        afterClearFilter(keys) {
            if (keys.includes('postalCode')) {
                this.inputValues['postalCode'] = ''

                if (keys.includes('distance') && this.sort == 'distance') {
                    this.sort = 'car.published'
                    this.sortOrder = 'desc'
                    this.caclulateSortValue()
                }
            }

            if (keys.includes('distance')) {
                const appDistance = document.querySelector('.app-distance')
                appDistance.tomselect.setValue('')
            }

        },

        checkDiapason(minKeyName, maxKeyName) {
            if (this.filters[minKeyName] && this.filters[maxKeyName] && this.filters[maxKeyName] < this.filters[minKeyName]) {
                this.filters[maxKeyName] = this.filters[minKeyName] = Math.min(this.filters[maxKeyName], this.filters[minKeyName])
            }
        },

        displayValidationWarning(container, message) {
            const noty = document.querySelector('.noty_body')
            if (!noty) {
                new Noty({
                    type: 'warning',
                    container: container,
                    text: message,
                    timeout: 2000
                }).show()
            }
        },

        addMakeModelPairSelect() {
            if (this.filters.make || this.filters.model) {

                this.makeModelPairsSelects.push({
                    index: this.makeModelPairsSelects ? this.makeModelPairsSelects.length : 0,
                    makeId: this.filters.make,
                    modelId: this.filters.model,
                    models: this.models,
                    makeName: this.getSelectedMake(this.filters.make),
                    modelName: this.getSelectedModel(this.filters.model, this.models)
                })

                this.filters.make = ''
                this.filters.model = ''
                this.models = []

                this.buildMakeModelPairsFilter()
            }
        },

        updateMakeModelPairSelect(selectedModel, index) {
            this.makeModelPairsSelects[index].modelId = selectedModel.id
            this.makeModelPairsSelects[index].modelName = this.getSelectedModel(selectedModel.id, this.makeModelPairsSelects[index].models)
        },

        deleteMakeModelPairSelect(index) {
            this.makeModelPairsSelects.splice(index, 1)
            if (this.makeModelPairsSelects) {
                for (i in this.makeModelPairsSelects) {
                    this.makeModelPairsSelects[i].index = i
                }
            }

            this.buildMakeModelPairsFilterAndAppyFilters()
        },

        buildMakeModelPairsFilter() {
            this.filters.makeModelPairs = []
            this.makeModelPairsSelects.forEach(makeModelPairSelect => {
                if (makeModelPairSelect.makeId) {
                    this.filters.makeModelPairs.push(makeModelPairSelect.makeId + ',' + makeModelPairSelect.modelId)
                }
            })
        },

        buildMakeModelPairsFilterAndAppyFilters() {
            this.buildMakeModelPairsFilter()
            this.applyFilters()
        },

        async getModelsAndAppyFilters(selectedMake, index) {
            this.$refs.cardItemsList.classList.add('loading-bg')
            this.$refs.addMakeModelButton.disabled = true

            const makeId = selectedMake ? selectedMake.id : ''
            if (makeId) {
                const formData = new FormData()
                formData.append('makeId', makeId)
                formData.append('routeName', this.routeName)
                const data = await ajax('POST', this.getModelsAjaxUrl, formData)

                if (index == -1) {
                    this.models = (data) ? data.models : []
                    this.filters.model = ''
                } else {
                    this.makeModelPairsSelects[index].models = (data) ? data.models : []
                    this.makeModelPairsSelects[index].modelId =''
                }
            } else {
                if (index == -1) {
                    this.models = []
                    this.filters.model = ''
                } else {
                    this.makeModelPairsSelects[index].models = []
                    this.makeModelPairsSelects[index].modelId =''
                }
            }

            if (index == -1) {
                this.applyFilters()
            } else {
                this.makeModelPairsSelects[index].makeId = makeId
                this.makeModelPairsSelects[index].makeName = makeId ? this.getSelectedMake(makeId) : ''
                this.makeModelPairsSelects[index].modelName = this.getSelectedModel(this.makeModelPairsSelects[index].modelId, this.makeModelPairsSelects[index].models)
                this.buildMakeModelPairsFilterAndAppyFilters()
            }

            this.$refs.addMakeModelButton.disabled = false
            this.$refs.cardItemsList.classList.remove('loading-bg')
        },

        getSelectedMake(makeId) {
            makeId = makeId ? makeId : this.filters.make

            for (let key in this.makes) {
                if (this.makes[key].id == makeId) {
                    return this.makes[key].name
                }
            }

            return ''
        },

        getSelectedModel(modelId, models) {
            modelId = modelId ? modelId : this.filters.model
            models = models ? models : this.models

            for (let key in models) {
                if (models[key].id == modelId) {
                    return models[key].name
                }
            }

            return ''
        },

        onMinYearChanged: function() {
            if (this.filters.maxYear && this.filters.minYear && this.filters.minYear > this.filters.maxYear) {
                this.filters.maxYear = this.filters.minYear
            }

            this.applyFilters()
        },

        onMaxYearChanged: function() {
            if (this.filters.minYear && this.filters.maxYear && this.filters.maxYear < this.filters.minYear) {
                this.filters.minYear = this.filters.maxYear
            }

            this.applyFilters()
        },

        createTextListOfFilters: function() {
            this.allDisplayedFilters = []
            this.createFiltersListMakeModelObjects()
            this.createFiltersListDiapasonObject('minPrice', 'maxPrice', this.labelCurrency)
            this.createFiltersListDiapasonObject('minMileage', 'maxMileage', this.labelMileage)
            this.createFiltersCustomObject('postalCode')
            this.createFiltersCustomObject('distance')
            this.createFiltersListGroupObjects('bodyType', null)
            this.createFiltersListDiapasonObject('minYear', 'maxYear', null)
            this.createFiltersCustomObject('condition')
            this.createFiltersListGroupObjects('transmission', null)
            this.createFiltersListGroupObjects('drivetrain', null)
            this.createFiltersListGroupObjects('fuelType', null)
            this.createFiltersListDiapasonObject('minEngine', 'maxEngine', this.labelEngine)
            this.createFiltersListDiapasonObject('minFuelEconomy', 'maxFuelEconomy', this.labelFuelEconomy)
            this.createFiltersListDiapasonObject('minCo2', 'maxCo2', this.labelCo2)
            this.createFiltersListDiapasonObject('minEvBatteryRange', 'maxEvBatteryRange', this.labelEvBatteryRange)
            this.createFiltersListDiapasonObject('minEvBatteryTime', 'maxEvBatteryTime', this.labelEvBatteryTime)
            this.createFiltersListDiapasonObject('minDaysOnMarket', 'maxDaysOnMarket', this.labelDaysOnMarket)
            this.createFiltersCheckboxObject('withPhotos')
            this.createFiltersCheckboxObject('priceDrops')
            this.createFiltersListGroupObjects('doors', null)
            this.createFiltersListGroupObjects('cabinSize', null)
            this.createFiltersListGroupObjects('bedSize', null)
            this.createFiltersListGroupObjects('seats', null)
            this.createFiltersListGroupObjects('extColor', this.labelExterior)
            this.createFiltersListGroupObjects('intColor', this.labelInterior)
            this.createFiltersCheckboxObject('certifiedPreOwned')
            this.createFiltersListGroupObjects('safetyRating', null)
            this.createFiltersListGroupObjects('feature', null)
            this.createFiltersListGroupObjects('status', null)
        },

        createFiltersListDiapasonObject: function(minKey, maxKey, label) {
            if (this.filters[minKey] && this.filters[maxKey]) {
                this.allDisplayedFilters.push({
                    type: 'keys',
                    text: `${this.filters[minKey]}-${this.filters[maxKey]}${label ?? ''}`,
                    keys: [minKey, maxKey],
                })
            } else {
                if (this.filters[minKey]) {
                    this.allDisplayedFilters.push({
                        type: 'keys',
                        text: `${this.labelFrom} ${this.filters[minKey]}${label ?? ''}`,
                        keys: [minKey]
                    })
                }

                if (this.filters[maxKey]) {
                    this.allDisplayedFilters.push({
                        type: 'keys',
                        text: `${this.labelTo} ${this.filters[maxKey]}${label ?? ''}`,
                        keys: [maxKey]
                    })
                }
            }
        },

        createFiltersListGroupObjects: function(key, label) {
            if (this.filters[key]) {

                switch(key) {
                    case 'doors':
                        this.filters[key].forEach(value => {
                            this.allDisplayedFilters.push({
                                type: 'value',
                                text: `${value} ${this.labelDoors}`,
                                key: key,
                                value: value
                            })
                        })
                        break

                    case 'seats':
                        this.filters[key].forEach(value => {
                            this.allDisplayedFilters.push({
                                type: 'value',
                                text: value != 6 ? `${value} ${this.labelSeats}` : `6+ ${this.labelSeats}`,
                                key: key,
                                value: value
                            })
                        })
                        break

                    default:
                        this.filters[key].forEach(value => {
                            const filterItems = this.filtersItemsWithCounts[key]
                            filterItems.forEach (item => {
                                if (value == item.id) {
                                    this.allDisplayedFilters.push({
                                        type: 'value',
                                        text: `${item.name} ${label ?? ''}`,
                                        key: key,
                                        value: value
                                    })
                                }
                            })
                        })
                        break
                }
            }
        },

        createFiltersListMakeModelObjects: function() {
            if (this.filters['make']) {
                const makeName = this.getSelectedMake(this.filters['make'])
                let text = makeName

                if (this.filters['model']) {
                    const modelName = this.getSelectedModel(this.filters['model'])
                    text = `${text} ${modelName}`
                }

                this.allDisplayedFilters.push({
                    type: 'keys',
                    text: text,
                    keys: ['make', 'model']
                })
            }

            this.makeModelPairsSelects.forEach((makeModelPairsSelect, index) => {
                if (makeModelPairsSelect.makeName != '') {
                    let text = makeModelPairsSelect.modelName != ''
                        ? `${makeModelPairsSelect.makeName} ${makeModelPairsSelect.modelName}`
                        : makeModelPairsSelect.makeName

                    this.allDisplayedFilters.push({
                        type: 'makeModelPair',
                        text: text,
                        index: index
                    })
                }
            })
        },

        createFiltersCheckboxObject: function(key) {
            if (this.filters[key]) {
                const ref = `${key}Label`
                this.allDisplayedFilters.push({
                    type: 'keys',
                    text: this.$refs[ref].innerHTML,
                    keys: [key],
                })
            }
        },

        createFiltersCustomObject: function(key) {
            if (this.filters[key]) {
                switch(key) {
                    case 'postalCode':
                        this.allDisplayedFilters.push({
                            type: 'keys',
                            text: this.filters[key],
                            keys: ['postalCode', 'distance'],
                        })
                        break

                    case 'distance':
                        let text
                        if (this.filters[key] == 'national') {
                            text = this.labelNational
                        }
                        else if (this.filters[key] == 'provincial') {
                            text = this.labelProvincial
                        }
                        else {
                            text = `${this.filters[key]} ${this.labelMileage} ${this.labelAway}`
                        }

                        this.allDisplayedFilters.push({
                            type: 'keys',
                            text: text,
                            keys: ['distance'],
                        })
                        break

                    case 'condition':
                        if (this.filters[key] == 'new') {
                            this.allDisplayedFilters.push({
                                type: 'keys',
                                text: this.labelNew,
                                keys: ['condition'],
                            })
                        }
                        if (this.filters[key] == 'used' && !this.filters['minMileage'] && !this.filters['maxMileage']) {
                            this.allDisplayedFilters.push({
                                type: 'keys',
                                text: this.labelUsed,
                                keys: ['condition'],
                            })
                        }
                        break

                }
            }
        },

        async validatePostalCodeAndApplyFilters() {
            if (this.$refs.postalCode.reportValidity()) {
                const checkGeoData = await this.setGeoDataForPostalCode()
                if (!checkGeoData) {
                    return false
                }
                else {
                    this.filters.postalCode = this.$refs.postalCode.value
                    this.sort = "distance"
                    this.sortOrder = "asc"
                    const sortSelect = document.querySelector('.app-sort')
                    sortSelect.tomselect.setValue(this.sort)
                    this.applyFilters()
                }
            }
        },

        async setGeoDataForPostalCode () {
            const postalCode = this.$refs.postalCode
            if (postalCode.value) {
                const formData = new FormData()
                formData.append('postalCode', postalCode.value)
                const data = await ajax('POST', this.setGeoDataForPostalCodeAjaxUrl, formData)
                return (data) ? data.result : false
            }
            return true
        },

        getPosition: function () {
            //const url = `${this.getPostalCodeAjaxUrl}?query=40.7638435,-73.9729691`
            //const result = await ajax("GET", url)

            if (navigator.geolocation) {

                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        //console.log(position)
                        //console.log("Your current position is (" + "Latitude: " + position.coords.latitude + ", " + "Longitude: " + position.coords.longitude + ")")

                        const url = `${this.getPostalCodeAjaxUrl}?latitude=${position.coords.latitude}&longitude=${position.coords.longitude}`
                        const result = await ajax("GET", url)
                        if (result.postalCode) {
                            this.inputValues.postalCode = result.postalCode
                        }
                        else {
                            this.displayValidationWarning(null, "Sorry, we cannot get a postal code of your current location")
                        }
                    },

                    (error) => {
                        let errorMessage
                        if (error.code == 1) {
                            errorMessage = "You've decided not to share your position, but it's OK. We won't ask you again."
                        } else if (error.code == 2) {
                            errorMessage = "The network is down or the positioning service can't be reached."
                        } else if (error.code == 3) {
                            errorMessage = "The attempt timed out before it could get the location data."
                        } else {
                            errorMessage = "Geolocation failed due to unknown error."
                        }
                        this.displayValidationWarning(null, errorMessage)
                    }
                )
            }
        },

        async toggleSavedCar(carId, withDeleteFromPageOnRemove = false) {
            const formData = new FormData()
            formData.append('carId', carId)
            const carIndex = this.items.findIndex((obj) => obj.id === carId)
            const url = this.items[carIndex].isCarSaved ? this.removeCarFromWishlistAjaxUrl : this.addCarToWishlistAjaxUrl

            const imageWrapper = document.querySelector(`#single-card-image-wrapper-${carId}`)
            if (!imageWrapper.classList.contains('app-busy')) {
                imageWrapper.classList.add('app-busy')
                const data = await ajax('POST', url, formData)
                imageWrapper.classList.remove('app-busy')
                if (data) {
                    this.items[carIndex].isCarSaved = data.isCarSaved
                    if (withDeleteFromPageOnRemove) {
                        this.$refs.cardItemsList.classList.add('loading-bg')
                        await this.search(false)
                        this.$nextTick(() => {
                            this.$refs.cardItemsList.classList.remove('loading-bg')
                        })
                    }
                    else {
                        new Noty({
                            type: 'success',
                            container: '#single-card-image-wrapper-' + carId,
                            text: data.isCarSaved ? this.messageCarSavedToWishlist : this.messageCarRemovedFromWishlist,
                            timeout: 2000
                        }).show()
                    }

                    updateCarUsersCount(data.totalCount)
                }
            }
        },

        async openSaveCarSearchUrlModal() {
            if (!this.carSearchUrl.id) {
                this.carSearchUrl.filters = this.allDisplayedFilters
                this.carSearchUrl.url = this.generateSaveCarSearchUrl()
                this.carSearchUrl.title = this.generateSaveCarSearchUrlTitle()
            }
            bootstrap.Modal.getOrCreateInstance(document.querySelector('.app-save-car-search-url-modal')).show()
        },

        closeSaveCarSearchUrlModal() {
            bootstrap.Modal.getInstance(document.querySelector('.app-save-car-search-url-modal')).hide()
        },

        generateSaveCarSearchUrl() {
            const queryObject = Object.assign(
                {},
                {
                    sort: this.sort,
                    sortOrder: this.sortOrder,
                },
                this.filters,
            )

            const queryString = this.prepareQueryString(queryObject)
            let url = `${this.urlPush}?${queryString}`
            return url
        },

        generateSaveCarSearchUrlTitle() {
            let title = ''
            for (let i = 0; i < this.carSearchUrl.filters.length && i < 2; i++) {
                title += " " + this.carSearchUrl.filters[i].text
            }

            if (this.carSearchUrl.filters.length > 2) {
                title += " " + this.labelCarSearchUrlTitleOther
            }

            return title
        },

        clearFilterItemInSaveCarSearchUrlModal(index) {
            this.carSearchUrl.filters.splice(index, 1)
            this.carSearchUrl.url = this.generateSaveCarSearchUrl()
            this.carSearchUrl.title = this.generateSaveCarSearchUrlTitle()
            if (this.carSearchUrl.filters.length == 0) {
                this.closeSaveCarSearchUrlModal()
                this.carSearchUrl.filters = []
            }
        },

        clearCarSearchUrl() {
            this.carSearchUrl.filters = []
            this.carSearchUrl.url = ''
            this.carSearchUrl.title = ''
            this.carSearchUrl.id = ''
        },

        async addCarSearchUrl() {
            const form = this.$refs.carSearchUrlForm
            if (form.reportValidity()) {
                const formData = new FormData(form)
                formData.append('url', this.carSearchUrl.url)
                formData.append('filters', JSON.stringify(this.carSearchUrl.filters))
                const data = await ajax('POST', this.addCarSearchUrlAjaxUrl, formData)
                if (data) {
                    this.carSearchUrl.id = data.id
                    //this.closeSaveCarSearchUrlModal()
                    new Noty({
                        type: 'success',
                        text: this.messageCarSearchUrlAdded,
                        timeout: 2000
                    }).show()
                    updateCarSearchUrlsCount(data.totalCount)
                }
            }
        },

        async updateCarSearchUrl() {
            const form = this.$refs.carSearchUrlForm
            if (form.reportValidity()) {
                const formData = new FormData(form)
                formData.append('id', this.carSearchUrl.id)
                formData.append('url', this.carSearchUrl.url)
                formData.append('filters', JSON.stringify(this.carSearchUrl.filters))
                const data = await ajax('POST', this.updateCarSearchUrlAjaxUrl, formData)
                if (data) {
                    new Noty({
                        type: 'success',
                        text: this.messageCarSearchUrlUpdated,
                        timeout: 2000
                    }).show()
                    //if (this.carSearchUrl.filters.length != this.allDisplayedFilters.length) {
                    //    this.allDisplayedFilters = this.carSearchUrl.filters
                    //    await this.search(true)
                    //}
                }
            }
        },

        async removeCarSearchUrl() {
            const formData = new FormData()
            formData.append('id', this.carSearchUrl.id)
            const data = await ajax('POST', this.removeCarSearchUrlAjaxUrl, formData)
            if (data) {
                this.closeSaveCarSearchUrlModal()
                this.clearCarSearchUrl()
                new Noty({
                    type: 'success',
                    text: this.messageCarSearchUrlRemoved,
                    timeout: 2000
                }).show()
                updateCarSearchUrlsCount(data.totalCount)
            }
        },

        async deleteCarSearchUrl($event, id) {
            const formData = new FormData()
            formData.append('id', id)
            $event.target.closest('.app-search-container').classList.add("loading")
            const data = await ajax('POST', this.deleteCarSearchUrlAjaxUrl, formData)
            $event.target.closest('.app-search-container').classList.remove("loading")
            if (data) {
                document.querySelector(`.app-search-container[data-id="${id}"]`).classList.add('d-none')
                document.querySelector(`.app-deleted-item-block[data-id="${id}"]`).classList.remove('d-none')
                updateCarSearchUrlsCount(data.totalCount)
            }
        },

        async restoreCarSearchUrl($event, id) {
            const formData = new FormData()
            formData.append('id', id)
            $event.target.closest('.app-deleted-item-block').classList.add("loading")
            const data = await ajax('POST', this.restoreCarSearchUrlAjaxUrl, formData)
            $event.target.closest('.app-deleted-item-block').classList.remove("loading")
            if (data) {
                document.querySelector(`.app-search-container[data-id="${id}"]`).classList.remove('d-none')
                document.querySelector(`.app-deleted-item-block[data-id="${id}"]`).classList.add('d-none')
                new Noty({
                    type: 'success',
                    killer: true,
                    text: this.messageCarSearchUrlRestored,
                    timeout: 2000
                }).show()
                updateCarSearchUrlsCount(data.totalCount)
            }
        }

    },
})










window.addEventListener('load', () => {

    const bodyWrapper = document.querySelector('body')
    const htmlWrapper = document.querySelector('html')

    // Search filter button on mobile ver.
    /*const searchButton = document.querySelector('.app-main-filter-button')
    const mainFilters = document.querySelector('.app-main-filters')
    const backSearchButton = document.querySelector('.app-filters-back-icon')

    if (searchButton) { // add event listener only of button exists
        searchButton.addEventListener('click', function() {
            mainFilters.classList.toggle('filters-show')
            bodyWrapper.classList.toggle('overflow-hidden')
            htmlWrapper.classList.toggle('overflow-hidden')
        })
    }
    if (backSearchButton) { // add event listener only of button exists
        backSearchButton.addEventListener('click', function() {
            mainFilters.classList.toggle('filters-show')
            bodyWrapper.classList.toggle('overflow-hidden')
            htmlWrapper.classList.toggle('overflow-hidden')
        })
    }*/

    // Filter button on mobile ver.
    const filterButton = document.querySelector('.app-secondary-filter-button')
    const secondaryFilters = document.querySelector('.app-filter-block')
    const backFilterButton = document.querySelector('.app-filters-back-btn')

    if (filterButton) { // add event listener only of button exists
        filterButton.addEventListener('click', function() {
            secondaryFilters.classList.toggle('filters-show')
            bodyWrapper.classList.toggle('overflow-hidden')
            htmlWrapper.classList.toggle('overflow-hidden')
        })
    }
    if (backFilterButton) { // add event listener only of button exists
        backFilterButton.addEventListener('click', function() {
            secondaryFilters.classList.toggle('filters-show')
            bodyWrapper.classList.toggle('overflow-hidden')
            htmlWrapper.classList.toggle('overflow-hidden')
        })
    }





    // set everything outside the onscroll event (less work per scroll)
    var stickyFilters = document.querySelector('.app-sticky-filters')
    if (stickyFilters) {
        var stop = stickyFilters.offsetTop - 60
        var docBody = document.documentElement || document.body.parentNode || document.body
        var hasOffset = window.pageYOffset !== undefined
        var scrollTop

        window.onscroll = function (e) {
            scrollTop = hasOffset ? window.pageYOffset : docBody.scrollTop
            if (scrollTop >= stop) {
                stickyFilters.classList.add('stick')
            } else {
                stickyFilters.classList.remove('stick')
            }
        }
    }
})

