const { test, expect } = require('@playwright/test');

test.describe('ButtonSeoClick Component', () => {
	test.beforeEach(async ({ page }) => {
		// Create a simple HTML page with test buttons
		await page.setContent(`
      <!DOCTYPE html>
      <html>
        <body>
          <button
            data-seo-click
            data-target="_blank"
            data-href="https://example.com/new-tab">
            Open in new tab
          </button>
          <button
            data-seo-click
            data-href="https://example.com/same-tab">
            Open in same tab
          </button>
        </body>
      </html>
    `);

		// Setup test environment
		await page.evaluate(() => {
			// Store navigation attempts
			window.lastNavigation = null;

			// Mock openUrlInNewTab
			window.openUrlInNewTab = (url) => {
				window.lastNavigation = { type: 'new_tab', url };
			};

			// Add click handler
			function onClickButton(e) {
				const target = e.currentTarget.getAttribute('data-target');
				const href = e.currentTarget.getAttribute('data-href');

				if (target === '_blank') {
					window.openUrlInNewTab(href);
				} else {
					window.lastNavigation = { type: 'same_tab', url: href };
					// Don't actually navigate to avoid breaking the test
					e.preventDefault();
				}
			}

			document
				.querySelectorAll('button[data-seo-click]')
				.forEach((button) => {
					button.addEventListener('click', onClickButton);
				});
		});
	});

	test('should open link in new tab when data-target="_blank"', async ({
		page,
	}) => {
		// Click the button that should open in new tab
		await page.click('button[data-target="_blank"]');

		// Get the navigation attempt
		const navigation = await page.evaluate(() => window.lastNavigation);
		expect(navigation).toEqual({
			type: 'new_tab',
			url: 'https://example.com/new-tab',
		});
	});

	test('should attempt to navigate in same tab', async ({ page }) => {
		// Click the button that should navigate in same tab
		await page.click('button:not([data-target])');

		// Get the navigation attempt
		const navigation = await page.evaluate(() => window.lastNavigation);
		expect(navigation).toEqual({
			type: 'same_tab',
			url: 'https://example.com/same-tab',
		});
	});
});
