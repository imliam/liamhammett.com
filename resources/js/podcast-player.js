document.querySelectorAll('[data-podcast-player]').forEach(player => {
    const audio = player.querySelector('audio')
    const chapters = [...player.querySelectorAll('[data-chapter-start]')]
    const status = player.querySelector('[role="status"]')
    const showError = () => {
        status.hidden = false
    }

    // Metadata requests must not count as downloads. Switch to tracking only on Play.
    audio.addEventListener('play', () => {
        const playbackUrl = audio.dataset.playbackUrl
        if (audio.currentSrc === playbackUrl) return

        const start = audio.currentTime
        audio.src = playbackUrl
        audio.currentTime = start
        audio.play().catch(error => {
            if (error.name !== 'AbortError') showError()
        })
    }, { once: true })

    chapters.forEach(chapter => {
        chapter.addEventListener('click', event => {
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return
            event.preventDefault()
            audio.currentTime = Number(chapter.dataset.chapterStart)
            audio.play().catch(error => {
                if (error.name !== 'AbortError') showError()
            })
        })
    })

    const updateChapter = () => {
        const current = chapters.findLast(chapter => Number(chapter.dataset.chapterStart) <= audio.currentTime)
        chapters.forEach(chapter => {
            if (chapter === current) chapter.setAttribute('aria-current', 'true')
            else chapter.removeAttribute('aria-current')
        })
    }
    audio.addEventListener('timeupdate', updateChapter)
    audio.addEventListener('loadedmetadata', updateChapter)
    audio.addEventListener('error', showError)
    audio.querySelector('source').addEventListener('error', showError)
    audio.addEventListener('playing', () => { status.hidden = true })
})

document.querySelectorAll('[data-embed-code]').forEach(field => {
    field.addEventListener('focus', () => field.select())
    field.addEventListener('click', () => field.select())
})
