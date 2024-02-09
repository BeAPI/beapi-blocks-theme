;(function () {
  /*
   * Get the site wrapper.
   * The skip-link will be injected in the beginning of it.
   */
  sibling = document.querySelector('.wp-site-blocks')

  // Early exit if the root element was not found.
  if (!sibling) {
    return
  }

  // Get the skip-link target's ID, and generate one if it doesn't exist.
  skipLink = document.createElement('a')
  skipLink.classList.add('skip-link', 'screen-reader-text')
  skipLink.href = '#' + skipLinkTargetID
  skipLink.innerHTML = 'Aller au contenu'

  // Inject the skip link.
  sibling.parentElement.insertBefore(skipLink, sibling)
})()
