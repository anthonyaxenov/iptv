document.querySelectorAll('tr.pls').forEach((tr) => {
    const id = tr.attributes['data-playlist-id'].value
    const xhr = new XMLHttpRequest()
    xhr.responseType = 'json'
    xhr.timeout = 60000 // ms = 1 min
    let el_status = tr.querySelector('span.status')
    let el_count = tr.querySelector('td.count')
    xhr.onreadystatechange = () => {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            console.log('[' + id + '] DONE', xhr.response)
            el_status.classList.remove('bg-secondary')
            el_status.innerText = xhr.response.status
            el_count.innerText = xhr.response.count
            switch (xhr.response.status) {
                case 'online':
                    el_status.classList.add('bg-success')
                    break
                case 'timeout':
                    el_status.classList.add('bg-warning')
                    break
                default:
                    el_status.classList.add('bg-danger')
                    break
            }
        }
    }
    xhr.onerror = () => {
        console.log('[' + id + '] ERROR', xhr.response)
        el_status.classList.add('bg-danger')
        el_status.innerText = 'error'
        el_count.innerText = '-'
    }
    xhr.onabort = () => {
        console.log('[' + id + '] ABORTED', xhr.response)
        el_status.classList.add('bg-secondary')
        el_count.innerText = '-'
    }
    xhr.ontimeout = () => {
        console.log('[' + id + '] TIMEOUT', xhr.response)
        el_status.classList.add('bg-secondary')
        el_status.innerText = 'timeout'
        el_count.innerText = '-'
    }
    xhr.open('GET', '/' + id + '/json')
    xhr.send()
})
