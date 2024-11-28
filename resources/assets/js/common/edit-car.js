VueComponent('#vue-edit-car', {
    mixins: [general],

    data() {
        return {
            getModelsAjaxUrl: null,
            getVinCodeDataAjaxUrl: null,
            deleteMediaAjaxUrl: null,
            setMediaMainAjaxUrl: null,
            sortMediaAjaxUrl: null,
            uploadFilesAjaxUrl: null,
            publishCarAjaxUrl: null,
            updatePreviewCarSessionAjaxUrl: null,
            restorePreviewCarSessionAjaxUrl: null,
            previewCarUrl: null,

            saveDraftCarUrl: null,
            searchCarsUrl: null,

            maxNumberOfAssignedFiles: null,
            messageMaxNumberOfAssignedFiles: null,

            car: {},
            models: [],

            initialValues: {
                vinCode: '',
                condition: '',
                mileage: '',
                makeId: '',
                modelId: '',
                year: '',
                engineType: '',
                engine: '',
                vehicleType: '',
                evBatteryRange: '',
                evBatteryTime: '',
                fuelType: '',
                fuelEconomy: '',
                co2: '',
                drivetrain: '',
                transmission: '',
                safetyRating: 'norating',
                certifiedPreOwned: 0,
                bodyType: '',
                extColor: 'unknown',
                doors: '',
                cabinSize: 'unknown',
                intColor: 'unknown',
                bedSize: 'unknown',
                seats: '',
                features: [],
                description: '',
                price: 0,
                contactName: '',
                phone: '',
                address: '',
                province: '',
                postalCode: '',
                latitude: '',
                longitude: '',
                carfaxUrl: '',
                keepLocationPrivate: 1
            },

            quillDescription: null,
            quillDescriptionInputTimer: null,
            isMediaProcessed: false,
            unsavedChangesExists: false,
            messageDragMainMedia: null,
            messageDontLeavePage: null,
            messageLeavePageConfirmMediaProcessed: null,
            messageLeavePageConfirmUnsavedChanges: null,
            messageVinCodeDataUpdated: null,
            labelCarSetDraftFromPublished: null,

            allowedMimeTypesImages: null,
            allowedMimeTypesVideos: null,
            messageAllowedMimeTypesImages: null,
            messageAllowedMimeTypesVideos: null,

            isLocationFormDisplayed: false,

            mobileStepsClient: [
                'vin',
                'specifications',
                'exterior',
                'interior',
                'features',
                'description',
                'carfax',
                'medias',
                'contact-info',
                'location',
                'price'
            ],

            mobileStepsDealer: [
                'vin',
                'specifications',
                'exterior',
                'interior',
                'features',
                'description',
                'carfax',
                'medias',
                'price'
            ],

            mobileSteps: [],
            currentMobileStep: '',
            window: {
                width: 0,
                height: 0
            }
        }
    },

    created() {
        window.addEventListener('resize', this.handleResize)
        this.handleResize()
    },

    mounted() {
        this.initDefaults()
        this.mobileSteps = this.isEmpty(this.car.clientId) ? this.mobileStepsDealer : this.mobileStepsClient
        this.$nextTick(() => {
            this.quillDescription = QuillHelper.initQuill('#quillDescription')
            this.quillDescription.root.innerHTML = this.car.description
            this.quillDescription.on('text-change', (delta, oldDelta, source) => {
                if (this.quillDescriptionInputTimer) {
                    clearTimeout(this.quillDescriptionInputTimer)
                }
                this.quillDescriptionInputTimer = setTimeout(async () => {
                    this.getDescription()
                }, 500)
            })
            this.quillDescription.root.addEventListener("focus", () => {
                this.setCurrentMobileStep('description')
            })

            this.setCurrentMobileStep('vin')
        })

        document.documentElement.style.scrollBehavior = 'auto'
        window.addEventListener("beforeunload", (e) => {
            if (this.isMediaProcessed) {
                new Noty({
                    type: 'warning',
                    text: this.messageLeavePageConfirmMediaProcessed,
                    timeout: 3000
                }).show()
            }

            if (this.unsavedChangesExists) {
                e.returnValue = this.messageLeavePageConfirmUnsavedChanges
            }
        })

        //console.log(this.car)
    },

    computed: {
        acceptAllowedMimeTypesImages() {
            return this.allowedMimeTypesImages ? this.allowedMimeTypesImages.join() : ''
        },

        acceptAllowedMimeTypesVideos() {
            return this.allowedMimeTypesVideos ? this.allowedMimeTypesVideos.join() : ''
        },

        currentMobileStepTitle() {
            if (this.currentMobileStep) {
                const title = document.querySelector(`.app-step-${this.currentMobileStep}-title`)
                if (title) {
                    return title.innerHTML
                }
            }
            return ''
        }
    },


    methods: {

        handleResize() {
            this.window.width = window.innerWidth
            this.window.height = window.innerHeight
        },

        isMobileVersion() {
          return this.window.width <= 1199
        },

        isStepDisplayed(step) {
            return !this.isMobileVersion() || (this.isMobileVersion() && (this.currentMobileStep == step || this.currentMobileStep == 'all'))
        },

        setCurrentMobileStep(step) {
            this.currentMobileStep = step
        },

        openMobileStepFromMenu(step) {
            this.setCurrentMobileStep(step)
            if (this.isMobileVersion()) {
                this.toggleMobileMenu()
            }
        },

        setCurrentMobileStepOnFocus($event) {
            const stepWrapper = $event.target.closest('.app-step')
            if (stepWrapper) {
                this.setCurrentMobileStep(stepWrapper.dataset.step)
            }
        },

        isCurrentMobileStepFirst() {
          return this.currentMobileStep == this.mobileSteps[0]
        },

        isCurrentMobileStepLast() {
            return this.currentMobileStep == this.mobileSteps[this.mobileSteps.length-1]
        },

        setPreviousCurrentMobileStep() {
            if (!this.isCurrentMobileStepFirst()) {
                const stepIndex = this.mobileSteps.findIndex((el) => el === this.currentMobileStep)
                this.currentMobileStep = this.mobileSteps[stepIndex-1]
            }
        },

        setNextCurrentMobileStep() {
            if (!this.isCurrentMobileStepLast()) {
                const stepIndex = this.mobileSteps.findIndex((el) => el === this.currentMobileStep)
                this.currentMobileStep = this.mobileSteps[stepIndex+1]
            }
        },

        toggleMobileMenu() {
            const bodyWrapper = document.querySelector('body')
            this.$refs.leftMenu.classList.toggle('edit-items-list-show')
            bodyWrapper.classList.toggle('overflow-hidden')
        },

        initDefaults () {
            Object.keys(this.initialValues).forEach(key => {
                if (!this.car.hasOwnProperty(key) || this.car[key] == null) {
                    this.car[key] = this.initialValues[key]
                }
            })
        },

        async sendVinCode() {
            const btnSendVinCode = this.$refs.btnSendVinCode
            const vinCode = this.$refs.vinCode

            if (vinCode.reportValidity()) {
                btnSendVinCode.disabled = true
                const formData = new FormData()
                formData.append('vinCode', vinCode.value)
                formData.append('publicId', this.car.publicId)
                const data = await ajax('POST', this.getVinCodeDataAjaxUrl, formData)
                if (data) {
                    this.car = Object.assign(this.car, data.carData)
                    this.initDefaults()
                    new Noty({
                        type: 'success',
                        text: this.messageVinCodeDataUpdated,
                        timeout: 3000
                    }).show()
                    const makeId = this.car.makeId
                    if (makeId) {
                        const formData = new FormData()
                        formData.append('makeId', makeId)
                        const data = await ajax('POST', this.getModelsAjaxUrl, formData)
                        this.models = (data) ? data.models : []
                    }

                }

                this.$nextTick(() => {
                    this.restorePreviewSession()
                })

                btnSendVinCode.disabled = false
            }
        },

        async getModels ($event) {
            const makeId = $event.target.value
            if (makeId) {
                const formData = new FormData()
                formData.append('makeId', makeId)
                const data = await ajax('POST', this.getModelsAjaxUrl, formData)
                this.models = (data) ? data.models : []
            }
            else  {
                this.models = [];
            }

            this.car.modelId = ''
            this.updatePreviewSession($event, 'modelId')
        },

        async markMediaAsProcessed(mediaId, value) {
            const media = this.car.mediasAll.find(media => media.id === mediaId)
            media.isProcessed = value
        },

        async deleteMedia($event, mediaId, carId) {
            const formData = new FormData()
            formData.append('carId', carId)
            formData.append('mediaId', mediaId)
            this.markMediaAsProcessed(mediaId, true)
            $event.target.closest('.app-medias-container').classList.add('loading')
            const data = await ajax('POST', this.deleteMediaAjaxUrl, formData)
            if (data) {
                this.car = Object.assign(this.car, data.car)
            }
            else {
                this.markMediaAsProcessed(mediaId, false)
            }
            $event.target.closest('.app-medias-container').classList.remove('loading')
        },

        async setMediaMain($event, mediaId, carId) {
            const formData = new FormData()
            formData.append('carId', carId)
            formData.append('mediaId', mediaId)
            this.markMediaAsProcessed(mediaId, true)
            $event.target.closest('.app-medias-container').classList.add('loading')
            const data = await ajax('POST', this.setMediaMainAjaxUrl, formData)
            if (data) {
                this.car = Object.assign(this.car, data.car)
            }
            else {
                this.markMediaAsProcessed(mediaId, false)
            }
            $event.target.closest('.app-medias-container').classList.remove('loading')
        },

        startDragMedia($event, sourceMedia, sourceIndex, zoneId) {
            $event.dataTransfer.dropEffect = 'move'
            $event.dataTransfer.effectAllowed = 'move'
            $event.dataTransfer.setData('sourceIndex', sourceIndex)
            $event.dataTransfer.setData('sourceZoneId', zoneId)
        },

        async onDropMedia($event, destinationMedia, destinationIndex, zoneId) {
            const sourceIndex = $event.dataTransfer.getData('sourceIndex')
            const sourceZoneId = $event.dataTransfer.getData('sourceZoneId')
            if (zoneId == sourceZoneId) {
                const ids = []
                if (zoneId == 'image') {
                    this.car.images.forEach(carMedia => ids.push(carMedia.id))
                }
                else {
                    this.car.videosAll.forEach(carMedia => ids.push(carMedia.id))
                }
                this.move(ids, sourceIndex, destinationIndex)
                const formData = new FormData()
                formData.append('carId', destinationMedia.carId)
                formData.append('ids', ids)
                $event.target.closest('.app-medias-container').classList.add('loading')
                const data = await ajax('POST', this.sortMediaAjaxUrl, formData)
                if (data) {
                    this.car = Object.assign(this.car, data.car)
                } else {
                }
                $event.target.closest('.app-medias-container').classList.remove('loading')
            }
        },

        getDescription () {
            let content = this.quillDescription.getSemanticHTML()
            content = QuillHelper.trimContent(content)
            const oldDescription = this.car.description
            this.car.description = content.length ? content : null
            if (oldDescription !== this.car.description) {
                this.$refs.description.dispatchEvent(new Event('change'))
            }
        },

        clearAll() {
            Object.keys(this.initialValues).forEach(key => {
                this.car[key] = this.initialValues[key]
            })

            this.$nextTick(() => {
                this.restorePreviewSession()
            })
        },

        isFilledStep(step) {
            switch (step) {
                case 'vin':
                    return !this.isEmpty(this.car.vinCode) && !this.isEmpty(this.car.condition)
                case 'specifications':
                    return !this.isEmpty(this.car.makeId) && !this.isEmpty(this.car.modelId) && !this.isEmpty(this.car.year) &&
                        !this.isEmpty(this.car.engineType) && !this.isEmpty(this.car.vehicleType) && !this.isEmpty(this.car.fuelType) &&
                        !this.isEmpty(this.car.drivetrain) && !this.isEmpty(this.car.transmission)
                case 'exterior':
                    return !this.isEmpty(this.car.bodyType) && !this.isEmpty(this.car.extColor) && !this.isEmpty(this.car.doors) &&
                        !this.isEmpty(this.car.cabinSize)
                case 'interior':
                    return !this.isEmpty(this.car.intColor) && !this.isEmpty(this.car.seats)
                case 'features':
                    return !this.isEmpty(this.car.features)
                case 'description':
                    return !this.isEmpty(this.car.description)
                case 'carfax':
                    return !this.isEmpty(this.car.carfaxUrl)
                case 'medias':
                    return this.car.hasMedias
                case 'contact-info':
                    return !this.isEmpty(this.car.contactName)
                case 'location':
                    return !this.isEmpty(this.car.province) && !this.isEmpty(this.car.postalCode)
                case 'price':
                    return !this.isEmpty(this.car.price)
            }
        },

        saveDraft() {
            if (!this.isMediaProcessed) {
                if (this.car.status == 'published') {
                    showConfirmNoty(
                        'confirm',
                        `<div>${this.labelCarSetDraftFromPublished}</div>`,
                        async () => {
                            const form = this.$refs.editCarForm
                            form.action = this.saveDraftCarUrl
                            this.getDescription()
                            this.$nextTick(() => {
                                this.unsavedChangesExists = false
                                form.submit()
                            })
                        },
                        this.labelConfirm,
                        this.labelCancel
                    )
                }
                else {
                    const form = this.$refs.editCarForm
                    form.action = this.saveDraftCarUrl
                    this.getDescription()
                    this.$nextTick(() => {
                        this.unsavedChangesExists = false
                        form.submit()
                    })
                }
            }
        },

        async publishCar() {
            if (!this.isMediaProcessed) {
                if ( this.car['clientId'] ||
                    (this.car['dealerId'] && this.car['dealerInfo'].status == 'active')
                ) {
                    const form = this.$refs.editCarForm
                    form.action = this.publishCarAjaxUrl
                    if (form.checkValidity()) {
                        this.getDescription()
                        this.$nextTick(async () => {
                            const formData = new FormData(form)
                            const data = await ajax('POST', this.publishCarAjaxUrl, formData)
                            if (data) {
                                this.unsavedChangesExists = false
                                location.replace(this.searchCarsUrl)
                            }
                        })
                    }
                    else {
                        if (this.isMobileVersion()) {
                            this.currentMobileStep = 'all'
                            this.$nextTick( () => {
                                form.reportValidity()
                            })
                        }
                        else {
                            form.reportValidity()
                        }
                    }
                }
            }
        },

        async handleUploadFiles($event, allowedMimeTypes = null, messageAllowedMimeTypes = null) {
            const files = $event.target.files
            if (files.length) {
                let isValid = this.validateUploadingFiles(files, allowedMimeTypes, messageAllowedMimeTypes)

                if (isValid) {
                    if (this.car['mediasAll'].length + files.length > this.maxNumberOfAssignedFiles) {
                        isValid = false
                        new Noty({
                            type: 'error',
                            text: this.messageMaxNumberOfAssignedFiles,
                            timeout: 3000
                        }).show()
                    }
                }

                if (isValid) {
                    const formData = new FormData()
                    const keys = Object.keys(files)
                    keys.forEach(key => formData.append('files[]', files[key]))
                    formData.append('carId', this.car.id)
                    $event.target.closest('.app-upload-link').classList.add('loading')
                    this.isMediaProcessed = true
                    const data = await ajax('POST', this.uploadFilesAjaxUrl, formData)
                    if (data) {
                        this.car = Object.assign(this.car, data.car)
                    }
                    this.isMediaProcessed = false
                    $event.target.closest('.app-upload-link').classList.remove('loading')
                }
            }
        },

        redirectToUrl($event) {
            if (!this.isMediaProcessed) {
                location.href = $event.target.href
            }
            else {
                new Noty({
                    type: 'warning',
                    text: this.messageDontLeavePage,
                    timeout: 3000
                }).show()
            }
        },

        openPreview() {
            if (!this.isMediaProcessed) {
                const form = this.$refs.editCarForm
                const allowPublish = form.checkValidity() ? 1 : 0
                this.unsavedChangesExists = false
                location.href = `${this.previewCarUrl}?allowPublish=${allowPublish}`
            }
            else {
                new Noty({
                    type: 'warning',
                    text: this.messageDontLeavePage,
                    timeout: 3000
                }).show()
            }
        },

        async updatePreviewSession($event, key) {
            const formData = new FormData()
            formData.append('publicId', this.car.publicId)
            formData.append('key', key)
            formData.append('value', this.car[key])
            await ajax('POST', this.updatePreviewCarSessionAjaxUrl, formData)
            this.unsavedChangesExists = true
        },

        async restorePreviewSession() {
            const form = this.$refs.editCarForm
            const formData = new FormData(form)
            await ajax('POST', this.restorePreviewCarSessionAjaxUrl, formData)
            this.unsavedChangesExists = true
        }

    }

})

window.addEventListener('load', () => {

})
