import AbstractDomElement from './AbstractDomElement';
import Swiper from 'swiper';
import { A11y, Navigation } from 'swiper/modules';
import { __ } from '@wordpress/i18n';
import noop from '../utils/noop';

/**
 * Thin wrapper around Swiper with project defaults (A11y + Navigation).
 *
 * @augments AbstractDomElement
 */
export default class Slider extends AbstractDomElement {
	/**
	 * @param {HTMLElement}   element   - Root `.swiper` element
	 * @param {SliderOptions} [options]
	 */
	constructor(element, options) {
		const instance = super(element, options);

		// avoid double init :
		if (!instance.isNewInstance()) {
			return instance;
		}

		this.init();
	}

	init() {
		const el = this._element;
		const {
			options,
			onChange,
			hideNavigationFromA11y,
			fixLastSlideActiveOnEnd,
		} = this._settings;

		const defaultModules = Slider.defaults.options.modules;
		const userModules = options.modules || [];
		const allModules = [
			...defaultModules,
			...userModules.filter((mod) => !defaultModules.includes(mod)),
		];

		const slidesAreListItems =
			el.querySelector('.swiper-slide')?.tagName === 'LI';

		const hideNavigationButtons = (swiper) => {
			if (!hideNavigationFromA11y) {
				return;
			}

			swiper.navigation.prevEl?.setAttribute('tabindex', '-1');
			swiper.navigation.prevEl?.setAttribute('aria-hidden', 'true');
			swiper.navigation.nextEl?.setAttribute('tabindex', '-1');
			swiper.navigation.nextEl?.setAttribute('aria-hidden', 'true');
		};

		const applyLastSlideActiveFix = (swiper) => {
			if (!swiper.isEnd || swiper.isBeginning) {
				return;
			}

			swiper.activeIndex = swiper.slides.length - 1;
			swiper.updateSlidesClasses();
		};

		const mergedOptions = {
			...options,
			modules: allModules,
			a11y: {
				...(slidesAreListItems ? { slideRole: '' } : {}),
				...options.a11y,
			},
			on: {
				...options.on,
				afterInit(swiper) {
					hideNavigationButtons(swiper);

					if (hideNavigationFromA11y) {
						swiper.el
							.querySelector('.swiper-notification')
							?.remove();
					}

					options.on?.afterInit?.call(this, swiper);
				},
				transitionEnd(swiper) {
					hideNavigationButtons(swiper);
					options.on?.transitionEnd?.call(this, swiper);
				},
				progress(swiper, progress) {
					if (fixLastSlideActiveOnEnd) {
						applyLastSlideActiveFix(swiper);
					}

					options.on?.progress?.call(this, swiper, progress);
				},
				slideChange(swiper) {
					if (fixLastSlideActiveOnEnd) {
						applyLastSlideActiveFix(swiper);
					}

					options.on?.slideChange?.call(this, swiper);
				},
			},
		};

		this.swiper = new Swiper(el, mergedOptions);

		this.swiper.on('slideChange', onChange.bind(this));
	}

	/**
	 * @return {import('swiper').Swiper} Native Swiper instance.
	 */
	getInstance() {
		return this.swiper;
	}
}

/**
 * @typedef {Object} SliderOptions
 * @property {Function}                       [onChange]                      - Called on slideChange (`this` = Slider instance)
 * @property {boolean}                        [hideNavigationFromA11y=false]  - Hide prev/next from AT when slides are already focusable; also removes `.swiper-notification`
 * @property {boolean}                        [fixLastSlideActiveOnEnd=false] - Fix `.swiper-slide-active` at the end with `slidesPerView: 'auto'` or fractional values other than halves (e.g. 2.75)
 * @property {import('swiper').SwiperOptions} [options]                       - Native Swiper options (merged with defaults)
 */

Slider.defaults = {
	onChange: noop,
	hideNavigationFromA11y: false,
	fixLastSlideActiveOnEnd: false,
	options: {
		modules: [A11y, Navigation],
		a11y: {
			prevSlideMessage: __('Previous slide', 'beapi-blocks-theme'),
			nextSlideMessage: __('Next slide', 'beapi-blocks-theme'),
			firstSlideMessage: __(
				'This is the first slide',
				'beapi-blocks-theme'
			),
			lastSlideMessage: __(
				'This is the last slide',
				'beapi-blocks-theme'
			),
			paginationBulletMessage: __(
				'Go to slide {{index}}',
				'beapi-blocks-theme'
			),
			slideLabelMessage: __(
				'{{index}} of {{slidesLength}}',
				'beapi-blocks-theme'
			),
		},
	},
};
