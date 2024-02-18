;(function () {
  const darkModeToggle = document.querySelector('.dark-mode-toggle a')
  if (darkModeToggle) {
    darkModeToggle.setAttribute('title', darkModeToggle.innerText)
    darkModeToggle.setAttribute('aria-hidden', 'true')
    darkModeToggle.innerHTML = ''
    darkModeToggle.addEventListener('click', function () {
      let DarkState = !document.body.classList.contains('dark-mode') ? 'true' : 'false'
      localStorage.setItem('theme-dark-mode', DarkState)
      document.body.classList.toggle('dark-mode')
    })
  }
})()
