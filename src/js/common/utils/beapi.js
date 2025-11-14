import AbstractDomElement from '../classes/AbstractDomElement';
import scrollDirection from '../classes/ScrollDirection';
import * as oneloop from 'oneloop.js';

// ----
// First declaration of beapi object is normally done in Assets.php
// ----
window.beapi = window.beapi || {};

// ----
// Expose classes and libraries for partial assets (wp-block, wp-pattern, template)
// ----
Object.assign(window.beapi, {
	classes: {
		AbstractDomElement,
	},
	libraries: {
		oneloop,
	},
	instances: {
		scrollDirection,
	},
});
