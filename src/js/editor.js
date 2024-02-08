import domReady from '@wordpress/dom-ready'
import { addFilter } from '@wordpress/hooks'
import { unregisterBlockStyle, getBlockVariations, unregisterBlockVariation } from '@wordpress/blocks'

// Native Gutenberg
domReady(() => {
  unregisterBlockStyle('core/separator', ['wide', 'dots'])

  unregisterBlockStyle('core/button', 'outline')

  // whitelist core embeds
  const allowedEmbedVariants = ['youtube', 'vimeo', 'dailymotion']
  getBlockVariations('core/embed').forEach((variant) => {
    if (!allowedEmbedVariants.includes(variant.name)) {
      unregisterBlockVariation('core/embed', variant.name)
    }
  })
})

// ACF Blocks
if (window.acf) {
  // Do stuff
}

addFilter('blocks.registerBlockType', 'beapi-framework', function (settings, name) {
  if (name === 'core/separator' || name === 'core/quote' || name === 'core/pullquote' || name === 'core/table') {
    // remove custom styles
    settings.styles = []
  }

  if (name === 'core/image') {
    // remove custom styles
    settings.styles = []
    // set default aligment for images to null
    settings.attributes.align = {
      type: 'string',
    }
  }

  return settings
})
