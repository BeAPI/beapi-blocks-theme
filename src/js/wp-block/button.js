/**
 * Button block
 */

const AbstractDomElement = window.beapi.classes.AbstractDomElement;
const oneloop = window.beapi.libraries.oneloop;
const scrollDirection = window.beapi.instances.scrollDirection;

class Button extends AbstractDomElement {
	constructor(element, options) {
		const instance = super(element, options);

		// avoid double init :
		if (!instance.isNewInstance()) {
			return instance;
		}

		// eslint-disable-next-line no-console
		console.log(
			'[Button] constructor',
			this._element,
			oneloop,
			scrollDirection
		);
	}
}

Button.init('.wp-block-button');
