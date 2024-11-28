const general = {
    data() {
        return {
            maxNumberOfUploadedFiles: 1,
            messageMaxNumberOfUploadedFiles: null,
            maxUploadFileSize: null,
            messageMaxUploadFileSize: null,
            allowedMimeTypes: null,
            messageAllowedMimeTypes: null,
        }
    },

    mounted() {
    },

    computed:  {
    },

    methods: {

        isEmpty(mixedVar, withZero = true) {
            let undef
            let key
            let i
            let len
            const emptyValuesWithZero = [undef, null, false, 0, '', '0']
            const emptyValuesWithoutZero = [undef, null, false, '']

            let emptyValues = withZero ? emptyValuesWithZero : emptyValuesWithoutZero
            for (i = 0, len = emptyValues.length; i < len; i++) {
                if (mixedVar === emptyValues[i]) {
                    return true
                }
            }

            if (typeof mixedVar === 'object') {
                for (key in mixedVar) {
                    if (mixedVar.hasOwnProperty(key)) {
                        return false
                    }
                }
                return true
            }

            return false
        },


        validateUploadingFiles: function (files, allowedMimeTypes = null, messageAllowedMimeTypes = null) {
            allowedMimeTypes = allowedMimeTypes || this.allowedMimeTypes
            messageAllowedMimeTypes = messageAllowedMimeTypes || this.messageAllowedMimeTypes
            let result = true
            let message = ''
            if (files.length) {

                if (files.length > this.maxNumberOfUploadedFiles) {
                    message = this.messageMaxNumberOfUploadedFiles ?? `Total number of uploaded files cannot be more than ${this.maxNumberOfUploadedFiles}`
                    result = false
                }

                if (result) {
                    for (let i = 0; i < files.length; i++) {
                        if (files[i].size > this.getMaxUploadFileSizeInBytes(this.maxUploadFileSize)) {
                            message = this.messageMaxUploadFileSize ?? `Size of each file should be less than ${this.getAllowedFileSize(this.maxUploadFileSize)}`
                            result = false
                            break
                        }

                        if (allowedMimeTypes && files[i].type != '') {
                            if (!allowedMimeTypes.includes(files[i].type)) {
                                message = messageAllowedMimeTypes ?? `Invalid file extension. The following extensions allowed: ${allowedMimeTypes}`
                                result = false
                                break
                            }
                        }
                    }
                }
            }

            if (message) {
                new Noty({
                    type: 'error',
                    text: message,
                    timeout: 3000
                }).show()
            }

            return result
        },

        getAllowedFileSize: function (val) {
            return (val >= 1024) ? (val / 1024 + 'Gb') : val + 'Mb'
        },

        getMaxUploadFileSizeInBytes: function (val) {
            return val * 1024 * 1024
        },

        getFileSize: function (val) {
            var kBs = 1024 * 1024
            return (val >= kBs) ? Math.round(val / kBs) + ' mb' :
                (val >= 1024 ? Math.round(val / 1024) + ' kb' : val + 'b')
        },

        move(arr, oldIndex, newIndex) {
            // Adjust negative indices to the equivalent positive indices
            while (oldIndex < 0) {
                oldIndex += arr.length;
            }
            while (newIndex < 0) {
                newIndex += arr.length;
            }

            // If 'new_index' is beyond the array length, extend the array with undefined elements
            if (newIndex >= arr.length) {
                let k = newIndex - arr.length;
                // Use a loop to push undefined elements to the array
                while ((k--) + 1) {
                    arr.push(undefined);
                }
            }

            // Remove the element at 'old_index' and insert it at 'new_index'
            arr.splice(newIndex, 0, arr.splice(oldIndex, 1)[0]);

            // Return the modified array
            return arr;
        }




    }
}
