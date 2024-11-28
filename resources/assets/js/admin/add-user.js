VueComponent('#vue-add-user', {

    data() {
        return {
            isDealerRole: false,
            isAccountManagerRole: false
        }
    },

    mounted() {
        this.checkRole()
    },

    methods: {
        checkRole() {
            this.isDealerRole = this.$refs.role.value.startsWith('dealer')
            this.isAccountManagerRole = this.$refs.role.value == 'admin.accountManager'
        }
    }

})
