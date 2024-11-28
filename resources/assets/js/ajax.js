function ajax(method, url, data, headers, options) {
    if (method !== 'GET') {
        if (!data || data instanceof FormData) {
            if (!data) {
                data = new FormData()
            }

            try {
                const csrfParam = document.querySelector('meta[name="csrf-param"]').getAttribute('content')
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                data.append(csrfParam, csrfToken)
            } catch (error) {}
        }
    }

    const defaultHeaders = {
        'X-Requested-With': 'XMLHttpRequest'
    }
    const defaultOptions = {
        useDefaultErrorsHandler: true,
        signal: null
    }
    headers = Object.assign(defaultHeaders, headers || {})
    options = Object.assign(defaultOptions, options || {})

    return fetch(url, {
        method: method,
        body: data,
        headers: headers,
        signal: options.signal
    })
        .then(async response => {
            if (response.status !== 200) {
                return Promise.reject(await response.json())
            }

            return await response.json()
        })
        .catch(error => {
            if (error.name === 'AbortError') {
                return
            }

            if (!options.useDefaultErrorsHandler) {
                throw error
            } else {
                if (error.errors !== undefined && Object.keys(error.errors).length) {
                    FormErrors.showAll(error.errors)
                } else {
                    new Noty({
                        type: 'error',
                        text: error.message ? error.message : 'Something went wrong. Please try again later or contact the Administration',
                        timeout: 5000
                    }).show()
                }
                return
            }
        })
}
