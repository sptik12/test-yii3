class QuillHelper {

    static defaultConfig() {
        return {
            //debug: 'info',
            modules: {
                syntax: false,
                toolbar: true,
            },
            placeholder: '',
            theme: 'snow'
        }
    }

    static initQuill(targetId, config = null) {
        config = config || this.defaultConfig()

        const target = document.querySelector(targetId)
        if (target.dataset.placeholder) {
            config.placeholder = target.dataset.placeholder
        }

        return new Quill(targetId, config)
    }

    static trimContent(content) {
        return content.replace(/^(?:<p>(?:\s|&nbsp;|<br(?:\s[^>]*)?>)*<\/p>|<br(?:\s[^>]*)?>|\s)+|(?:<p>(?:\s|&nbsp;|<br(?:\s[^>]*)?>)*<\/p>|<br(?:\s[^>]*)?>|\s)+$/gi, '')
    }

}
