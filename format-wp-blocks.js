#!/usr/bin/env node

const fs = require('fs');
const glob = require('glob');

// Function to format JSON with proper tab indentation
function formatJson(jsonStr, indentLevel = 0) {
	try {
		// Parse the JSON string
		const jsonObj = JSON.parse(jsonStr);

		// Create proper indentation
		const baseIndent = '\t'.repeat(indentLevel);

		// Convert the JSON to a string with precise control over formatting
		let result = '{\n';

		// Process each key in the object
		const keys = Object.keys(jsonObj);
		keys.forEach((key, index) => {
			const value = jsonObj[key];
			const isLast = index === keys.length - 1;

			// Add the key with proper indentation
			result += `${baseIndent}\t"${key}": `;

			// Format the value based on its type
			if (typeof value === 'object' && value !== null) {
				if (Array.isArray(value)) {
					// Handle arrays
					result += formatArray(value, indentLevel + 1);
				} else {
					// Handle nested objects with increased indentation
					result += formatNestedObject(value, indentLevel + 1);
				}
			} else {
				// Handle primitive values
				result += JSON.stringify(value);
			}

			// Add comma if not the last item
			result += isLast ? '\n' : ',\n';
		});

		// Close the object
		result += `${baseIndent}}`;

		return result;
	} catch (e) {
		console.error(`Error parsing JSON: ${e.message}`);
		return jsonStr;
	}
}

// Format array with proper indentation
function formatArray(arr, indentLevel) {
	const indent = '\t'.repeat(indentLevel);

	if (arr.length === 0) {
		return '[]';
	}

	let result = '[\n';

	arr.forEach((item, index) => {
		const isLast = index === arr.length - 1;
		result += `${indent}\t`;

		if (typeof item === 'object' && item !== null) {
			if (Array.isArray(item)) {
				result += formatArray(item, indentLevel + 1);
			} else {
				result += formatNestedObject(item, indentLevel + 1);
			}
		} else {
			result += JSON.stringify(item);
		}

		result += isLast ? '\n' : ',\n';
	});

	result += `${indent}]`;
	return result;
}

// Format nested object with proper indentation
function formatNestedObject(obj, indentLevel) {
	const indent = '\t'.repeat(indentLevel);

	if (Object.keys(obj).length === 0) {
		return '{}';
	}

	let result = '{\n';

	Object.keys(obj).forEach((key, index) => {
		const value = obj[key];
		const isLast = index === Object.keys(obj).length - 1;

		result += `${indent}\t"${key}": `;

		if (typeof value === 'object' && value !== null) {
			if (Array.isArray(value)) {
				result += formatArray(value, indentLevel + 1);
			} else {
				result += formatNestedObject(value, indentLevel + 1);
			}
		} else {
			result += JSON.stringify(value);
		}

		result += isLast ? '\n' : ',\n';
	});

	result += `${indent}}`;
	return result;
}

// Process whole file content
function processContent(content) {
	// First convert any spaces used for indentation to tabs
	content = convertSpacesToTabs(content);

	const lines = content.split('\n');
	const formattedLines = [];

	let insideComment = false;
	let currentIndent = '';
	let commentBuffer = [];

	for (let i = 0; i < lines.length; i++) {
		const line = lines[i];

		// Detect the indentation level
		if (!insideComment) {
			const match = line.match(/^(\t*)/);
			if (match) {
				currentIndent = match[1];
			}
		}

		// Check if line contains opening WP comment
		if (line.includes('<!-- wp:') && !insideComment) {
			insideComment = true;
			commentBuffer = [line];
		}
		// Check if line contains closing WP comment
		else if (line.includes('-->') && insideComment) {
			insideComment = false;
			commentBuffer.push(line);

			// Process the buffer
			const fullComment = commentBuffer.join(' ').trim();

			// Format the comment
			const formattedComment = formatWpComment(
				fullComment,
				currentIndent
			);
			formattedLines.push(formattedComment);

			commentBuffer = [];
		}
		// Inside a comment, collect lines
		else if (insideComment) {
			commentBuffer.push(line);
		}
		// Not inside a comment, add line as-is
		else {
			formattedLines.push(line);
		}
	}

	return formattedLines.join('\n');
}

// Convert spaces to tabs for indentation
function convertSpacesToTabs(content) {
	// Replace leading spaces with tabs (assuming 2 spaces = 1 tab)
	return content.replace(/^([ ]{2})+/gm, (match) => {
		return '\t'.repeat(match.length / 2);
	});
}

// Format a WordPress comment
function formatWpComment(comment, indent = '') {
	// Handle ending block comments
	if (comment.includes('<!-- /wp:')) {
		return `${indent}<!-- /wp:${comment.match(/<!-- \/wp:([^ ]*)/)[1]} -->`;
	}

	// Regular expressions to match different parts of WP comments
	const basicMatch = comment.match(/<!-- wp:([^ ]*) -->/);
	if (basicMatch) {
		return `${indent}<!-- wp:${basicMatch[1]} -->`;
	}

	const complexMatch = comment.match(/<!-- wp:([^ ]*) ({.*})? ?-->/);
	if (complexMatch) {
		const blockName = complexMatch[1];
		const jsonPart = complexMatch[2];

		if (!jsonPart) {
			return `${indent}<!-- wp:${blockName} -->`;
		}

		// Format JSON with same indentation level
		const indentLevel = indent.length;
		const formattedJson = formatJson(jsonPart, indentLevel);

		return `${indent}<!-- wp:${blockName} ${formattedJson} -->`;
	}

	// If no patterns match, return the original comment
	return `${indent}${comment}`;
}

// Process a file
function processFile(filePath) {
	try {
		const content = fs.readFileSync(filePath, 'utf8');
		const formattedContent = processContent(content);

		if (content !== formattedContent) {
			fs.writeFileSync(filePath, formattedContent);
			console.log(`Formatted: ${filePath}`);
		} else {
			console.log(`No changes needed: ${filePath}`);
		}
	} catch (error) {
		console.error(`Error processing ${filePath}: ${error.message}`);
	}
}

// Main function
function main() {
	const args = process.argv.slice(2);

	if (args.length === 0) {
		console.log('Usage: node format-wp-blocks.js <glob-pattern>');
		console.log('Example: node format-wp-blocks.js "patterns/**/*.php"');
		process.exit(1);
	}

	args.forEach((pattern) => {
		const files = glob.sync(pattern);

		if (files.length === 0) {
			console.log(`No files matching pattern: ${pattern}`);
			return;
		}

		console.log(`Processing ${files.length} files...`);
		files.forEach(processFile);
	});
}

main();
