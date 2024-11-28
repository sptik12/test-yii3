VueComponent('#vue-approve-dealer', {
    data() {
        return {
            searchDealerAjaxUrl: '',
            successMessage: null,
            cancelMessage: null
        }
    },

    mounted() {
    },

    methods: {
        async doApproveDealer($event, dealerId) {
            const formData = new FormData()
            const target = $event.target
            formData.append('dealerId', dealerId)
            const data = await ajax('POST', target.href, formData)
            if (data.result) {
                new Noty({
                    type: 'success',
                    text: this.successMessage,
                    timeout: 2000
                }).show()
                setTimeout(() =>  location.href = this.searchDealerAjaxUrl, 2000)
            }
        },

        async cancelApproveDealer($event, dealerId) {
            new Noty({
                type: 'error',
                text: this.cancelMessage,
                timeout: 2000
            }).show()
            setTimeout(() =>  location.href = this.searchDealerAjaxUrl, 2000)
        },
    }
})
