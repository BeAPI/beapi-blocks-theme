import extend from '../utils/extend.js';

/**
 * @class AbstractDomElement
 * @description Abstract class for DOM elements
 * @example
 * class MyClass extends AbstractDomElement {
 *   constructor(element, options) {
 *     super(element, options);
 *   }
 * }
 *  MyClass.defaults = {
 *
 * }
 *
 * MyClass.preset = {
 * '.selector' : {this object will be extended with defaults options}
 * }
 *
 * // loop on each preset
 * MyClass.initFromPreset()
 */
export default class AbstractDomElement {
	constructor(element, options) {
		// provide an explicit spaceName to prevent conflict after minification
		// MaClass.nameSpace = 'MaClass'
		this.constructor.nameSpace =
			this.constructor.nameSpace || this.constructor.name;
		const nameSpace = this.constructor.nameSpace;

		// if no spacename beapi, create it - avoid futur test
		if (!element.beapi) {
			element.beapi = {};
		}

		const oldInstance = element.beapi[nameSpace];

		if (oldInstance) {
			// eslint-disable-next-line no-console
			console.warn(
				'[AbstractDomElement] more than 1 class is initialised with the same name space on :',
				element,
				oldInstance
			);
			oldInstance._isNewInstance = false;
			return oldInstance;
		}

		this._element = element;
		this._settings = extend(true, {}, this.constructor.defaults, options);
		this._element.beapi[nameSpace] = this;
		this._isNewInstance = true;
	}

	/**
	 * Check if the instance is new
	 *
	 * @return {boolean} - True if the instance is new, false otherwise
	 */
	isNewInstance() {
		return this._isNewInstance;
	}

	/**
	 * Destroy the instance
	 *
	 * @return {AbstractDomElement} - The instance
	 */
	destroy() {
		this._element.beapi[this.constructor.nameSpace] = undefined;
		return this;
	}

	/**
	 * Initialize the instance
	 *
	 * @param {string|HTMLElement|Array<string|HTMLElement>} element - The element to initialize the instance on
	 * @param {Object}                                       options - Options for the instance
	 * @return {AbstractDomElement} - The instance
	 */
	static init(element, options) {
		foreach(element, (el) => {
			new this(el, options);
		});

		return this;
	}

	/**
	 * Check if the instance has an instance on the element
	 *
	 * @param {string|HTMLElement} element - The element to check the instance on
	 * @return {boolean} - True if the instance has an instance on the element, false otherwise
	 */
	static hasInstance(element) {
		const el = getDomElement(element);
		return el && el.beapi && !!el.beapi[this.nameSpace];
	}

	/**
	 * Get the instance on the element
	 *
	 * @param {string|HTMLElement} element - The element to get the instance on
	 * @return {AbstractDomElement} - The instance
	 */
	static getInstance(element) {
		const el = getDomElement(element);
		return el && el.beapi ? el.beapi[this.nameSpace] : undefined;
	}

	/**
	 * Destroy the instance on the element
	 *
	 * @param {string|HTMLElement} element - The element to destroy the instance on
	 * @return {AbstractDomElement} - The instance
	 */
	static destroy(element) {
		this.foreach(element, (el) => {
			if (el.beapi && el.beapi[this.nameSpace]) {
				el.beapi[this.nameSpace].destroy();
			}
		});

		return this;
	}

	/**
	 * Loop through the elements
	 *
	 * @param {string|HTMLElement|Array<string|HTMLElement>} element  - The element to loop through
	 * @param {Function}                                     callback - The callback to call for each element
	 * @return {AbstractDomElement} - The instance
	 */
	static foreach(element, callback) {
		foreach(element, (el) => {
			if (el.beapi && el.beapi[this.nameSpace]) {
				callback(el);
			}
		});

		return this;
	}

	/**
	 * Initialize the instance from the preset
	 *
	 * @return {AbstractDomElement} - The instance
	 */
	static initFromPreset() {
		const preset = this.preset;
		let selector;

		for (selector in preset) {
			this.init(selector, preset[selector]);
		}

		return this;
	}

	/**
	 * Destroy the instance from the preset
	 *
	 * @return {AbstractDomElement} - The instance
	 */
	static destroyFromPreset() {
		const preset = this.preset;
		let selector;

		for (selector in preset) {
			this.destroy(selector);
		}

		return this;
	}
}

// ----
// utils
// ----

/**
 * Loop through the elements
 *
 * @param {string|HTMLElement|Array<string|HTMLElement>} element  - The element to loop through
 * @param {Function}                                     callback - The callback to call for each element
 */
function foreach(element, callback) {
	const el = getDomElements(element);
	let i;

	for (i = 0; i < el.length; i++) {
		if (callback(el[i]) === false) {
			break;
		}
	}
}

/**
 * Get the DOM elements
 *
 * @param {string|HTMLElement|Array<string|HTMLElement>} element - The element to get the DOM elements on
 * @return {Array<HTMLElement>} - The DOM elements
 */
function getDomElements(element) {
	if (typeof element === 'string') {
		return document.querySelectorAll(element);
	}

	if (Array.isArray(element) && element.length > 0) {
		return element;
	}

	return [element];
}

/**
 * Get the DOM element
 *
 * @param {string|HTMLElement|Array<string|HTMLElement>} element - The element to get the DOM element on
 * @return {HTMLElement} - The DOM element
 */
function getDomElement(element) {
	return getDomElements(element)[0];
}
