VueComponent('#vue-view-car', {
    data() {
        return {
            car: {},
            preview: null,
            mediaMain: {},
            activeCarouselSlide: 0,
            lgMedias: [],
            showFullDescriptionLink: true,
            publishCarAjaxUrl: null,
            saveDraftCarAjaxUrl: null,
            addCarToWishlistAjaxUrl: null,
            removeCarFromWishlistAjaxUrl: null,
            searchCarsUrl: null,
            messageCarSavedToWishlist: null,
            messageCarRemovedFromWishlist: null
        }
    },

    mounted() {
        this.mediaMain = this.car.mediaMain
        if (this.car.hasMedias) {
            this.car.mediasActive.forEach((media, index) => {
                const lgObject = {}

                if (!media.isVideo) {
                    lgObject.src = media.baseUrl
                    lgObject.thumb = media.galleryUrl
                }
                else {
                    lgObject.video = {
                        source: [
                        ],
                        attributes: {
                            "src": media.baseUrl,
                            "controls": true
                        }
                    }
                    lgObject.poster = media.videoPreviewUrl
                    lgObject.thumb = media.videoPreviewUrl
                }

                this.lgMedias.push(lgObject)
            })


            let bsCarouselContainer = this.$refs.bsCarouselContainer
            const carousel = new bootstrap.Carousel(bsCarouselContainer, {
                interval: 2000,
                wrap: true,
            })

            bsCarouselContainer.addEventListener('slid.bs.carousel',  e => {
                this.activeCarouselSlide = e.to
            })

            this.$nextTick(() => {
                this.applyLg()
            })
        }
    },

    methods: {

        applyLg() {
            const lgContainer = this.$refs.lgContainer
            const lgGallery = lightGallery(lgContainer, {
                plugins: [lgThumbnail, lgVideo],
                selector: '.lg-item',
                thumbnail: true,
                dynamic: true,
                dynamicEl: this.lgMedias
            })

            document.querySelectorAll('.open-lg-item').forEach(el => {
                el.addEventListener('click', (e) => {
                    lgGallery.openGallery(parseInt(e.currentTarget.dataset.slide))
                })
            })
        },

        showFullDescription() {
            this.$refs.description.innerHTML = this.car.description
            this.showFullDescriptionLink = false
        },

        hideFullDescription() {
            this.$refs.description.innerHTML = this.car.descriptionShort
            this.showFullDescriptionLink = true
        },

        async publishCar() {
            if ( this.car['clientId'] ||
                (this.car['dealerId'] && this.car['dealerInfo'].status == 'active')
            ) {
                const formData = new FormData()
                formData.append('publicId', this.car.publicId)
                const data = await ajax('POST', this.publishCarAjaxUrl, formData)

                if (data) {
                    location.href = this.searchCarsUrl
                }
            }
        },

        async saveDraft() {
            const formData = new FormData()
            formData.append('publicId', this.car.publicId)
            const data = await ajax('POST', this.saveDraftCarAjaxUrl, formData)

            if (data) {
                location.href = this.searchCarsUrl
            }
        },

        async toggleSavedCar(carId) {
            const formData = new FormData()
            formData.append('carId', carId)
            const url = this.car.isCarSaved ? this.removeCarFromWishlistAjaxUrl : this.addCarToWishlistAjaxUrl
            const data = await ajax('POST', url, formData)
            if (data) {
                this.car.isCarSaved = data.isCarSaved
                new Noty({
                    type: 'success',
                    text: data.isCarSaved ? this.messageCarSavedToWishlist : this.messageCarRemovedFromWishlist,
                    timeout: 2000
                }).show()
            }
        }

    }

})

window.addEventListener('load', () => {
    const flyButton = document.querySelector('.app-fly-button')
    const requestForm = document.querySelector('.app-request-form')
    const backRequestForm = document.querySelector('.app-request-title')
    const bodyWrapper = document.querySelector('body')
    const htmlWrapper = document.querySelector('html')

    if (flyButton) { // add event listener only of button exists
        flyButton.addEventListener('click', function() {
            requestForm.classList.toggle('request-form-show')
            bodyWrapper.classList.toggle('overflow-hidden')
            htmlWrapper.classList.toggle('overflow-hidden')
        })
    }
    if (backRequestForm) { // add event listener only of button exists
        backRequestForm.addEventListener('click', function() {
            requestForm.classList.toggle('request-form-show')
            bodyWrapper.classList.toggle('overflow-hidden')
            htmlWrapper.classList.toggle('overflow-hidden')
        })
    }
})
