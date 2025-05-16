// @ts-check
import { test, expect } from '@playwright/test';

test.describe('ScrollDirection Component', () => {
	test('The html element should have scroll-top class by default', async ({
		page,
	}) => {
		await page.goto('/');

		// Check if the html element has the scroll-top class
		const hasScrollTopClass = await page.evaluate(() => {
			return document.documentElement.classList.contains('scroll-top');
		});

		expect(hasScrollTopClass).toBeTruthy();
	});

	test('The html element should have scroll-down class when scrolling down', async ({
		page,
	}) => {
		await page.goto('/');

		// Scroll down by 100px
		await page.evaluate(() => {
			window.scrollTo(0, 100);
		});

		// Wait a bit for the scroll event to be processed
		await page.waitForTimeout(100);

		// Check if the html element has the scroll-down class
		const hasScrollDownClass = await page.evaluate(() => {
			return document.documentElement.classList.contains('scroll-down');
		});

		expect(hasScrollDownClass).toBeTruthy();
	});

	test('The html element should have appropriate class based on page scrollability', async ({
		page,
	}) => {
		await page.goto('/');

		// First check natural scrollability
		const isNaturallyScrollable = await page.evaluate(() => {
			return document.documentElement.scrollHeight > window.innerHeight;
		});

		// If not naturally scrollable, add content to make it scrollable
		if (!isNaturallyScrollable) {
			await page.evaluate(() => {
				const div = document.createElement('div');
				div.style.height = '200vh'; // Make the page at least 2 viewport heights
				div.style.background = 'linear-gradient(to bottom, #fff, #000)'; // Visual feedback
				document.body.appendChild(div);
			});

			// Wait for any potential layout recalculations
			await page.waitForTimeout(100);
		}

		// Verify we can now scroll
		const canScroll = await page.evaluate(() => {
			return document.documentElement.scrollHeight > window.innerHeight;
		});

		if (canScroll) {
			// Scroll to bottom
			await page.evaluate(() => {
				window.scrollTo({
					top:
						document.documentElement.scrollHeight -
						window.innerHeight,
					behavior: 'instant',
				});
			});

			// Wait for scroll event to be processed
			await page.waitForTimeout(200);

			// Get all scroll-related states for debugging
			const scrollState = await page.evaluate(() => {
				const scrollTop =
					document.documentElement.scrollTop ||
					document.body.scrollTop;
				const scrollHeight =
					document.documentElement.scrollHeight ||
					document.body.scrollHeight;
				const {clientHeight} = document.documentElement;
				const isAtBottom =
					Math.abs(scrollHeight - clientHeight - scrollTop) <= 1;

				return {
					scrollTop,
					scrollHeight,
					clientHeight,
					isAtBottom,
					classes: document.documentElement.className,
					hasScrollBottom:
						document.documentElement.classList.contains(
							'scroll-bottom'
						),
				};
			});

			// Verify scroll position and class
			expect(
				scrollState.isAtBottom,
				'Page should be scrolled to the bottom'
			).toBeTruthy();
			expect(
				scrollState.hasScrollBottom,
				'HTML element should have scroll-bottom class when at bottom'
			).toBeTruthy();
		} else {
			// If somehow still not scrollable, fail the test with detailed information
			const pageMetrics = await page.evaluate(() => {
				return {
					scrollHeight: document.documentElement.scrollHeight,
					innerHeight: window.innerHeight,
					bodyHeight: document.body.offsetHeight,
				};
			});
			throw new Error(
				`Page is not scrollable. Metrics: ${JSON.stringify(
					pageMetrics,
					null,
					2
				)}`
			);
		}
	});

	test('The html element should have scroll-up class when scrolling up after scrolling down', async ({
		page,
	}) => {
		await page.goto('/');

		// First check natural scrollability and add content if needed
		const isNaturallyScrollable = await page.evaluate(() => {
			return document.documentElement.scrollHeight > window.innerHeight;
		});

		if (!isNaturallyScrollable) {
			await page.evaluate(() => {
				const div = document.createElement('div');
				div.style.height = '200vh';
				div.style.background = 'linear-gradient(to bottom, #fff, #000)';
				document.body.appendChild(div);
			});

			// Wait for layout recalculations
			await page.waitForTimeout(100);
		}

		// Verify we can scroll
		const canScroll = await page.evaluate(() => {
			return document.documentElement.scrollHeight > window.innerHeight;
		});

		if (canScroll) {
			// First scroll down significantly
			await page.evaluate(() => {
				window.scrollTo({
					top: Math.max(200, window.innerHeight / 2),
					behavior: 'instant',
				});
			});

			// Wait for scroll event
			await page.waitForTimeout(200);

			// Then scroll up a bit
			await page.evaluate(() => {
				window.scrollTo({
					top: Math.max(100, window.innerHeight / 4),
					behavior: 'instant',
				});
			});

			// Wait for scroll event
			await page.waitForTimeout(200);

			// Get scroll state for debugging
			const scrollState = await page.evaluate(() => {
				const scrollTop =
					document.documentElement.scrollTop ||
					document.body.scrollTop;

				return {
					currentScrollTop: scrollTop,
					classes: document.documentElement.className,
					hasScrollUp:
						document.documentElement.classList.contains(
							'scroll-up'
						),
					allClasses: Array.from(document.documentElement.classList),
				};
			});

			// Verify the scroll-up class is present
			expect(
				scrollState.hasScrollUp,
				`HTML should have scroll-up class. Current classes: ${scrollState.allClasses.join(
					', '
				)}`
			).toBeTruthy();
		} else {
			// If somehow still not scrollable, fail with metrics
			const pageMetrics = await page.evaluate(() => {
				return {
					scrollHeight: document.documentElement.scrollHeight,
					innerHeight: window.innerHeight,
					bodyHeight: document.body.offsetHeight,
				};
			});
			throw new Error(
				`Page is not scrollable. Metrics: ${JSON.stringify(
					pageMetrics,
					null,
					2
				)}`
			);
		}
	});
});
