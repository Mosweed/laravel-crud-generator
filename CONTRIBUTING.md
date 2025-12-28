# Contributing to Laravel CRUD Generator

First off, thanks for taking the time to contribute! 🎉

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When you create a bug report, include as many details as possible:

- **Use a clear and descriptive title**
- **Describe the exact steps to reproduce the problem**
- **Provide specific examples** (command you ran, config you used)
- **Describe the behavior you observed and what you expected**
- **Include your environment details** (PHP version, Laravel version, OS)

### Suggesting Features

Feature suggestions are welcome! Please:

- **Use a clear and descriptive title**
- **Provide a detailed description** of the suggested feature
- **Explain why this feature would be useful**
- **Provide code examples** if applicable

### Pull Requests

1. **Fork the repository** and create your branch from `main`
2. **Install dependencies**: `composer install`
3. **Make your changes**
4. **Add tests** for any new functionality
5. **Run the test suite**: `composer test`
6. **Run code style fixes**: `composer pint` (if available)
7. **Commit your changes** using a descriptive commit message
8. **Push to your fork** and submit a pull request

## Development Setup

```bash
# Clone your fork
git clone https://github.com/your-username/laravel-crud-generator.git
cd laravel-crud-generator

# Install dependencies
composer install

# Run tests
composer test

# Run tests with coverage
composer test-coverage
```

## Coding Standards

- Follow PSR-12 coding standards
- Use type hints where possible
- Write meaningful commit messages
- Add PHPDoc blocks to all public methods
- Keep methods small and focused

### Commit Messages

- Use the present tense ("Add feature" not "Added feature")
- Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit the first line to 72 characters or less
- Reference issues and pull requests liberally after the first line

Examples:
```
feat: add support for polymorphic relations
fix: resolve migration generation for enum fields
docs: update README with new theme examples
test: add tests for factory generator
```

## Testing

- Write tests for any new functionality
- Ensure all tests pass before submitting a PR
- Aim for high test coverage

```bash
# Run all tests
composer test

# Run specific test file
vendor/bin/phpunit tests/Unit/FieldParserTest.php

# Run with coverage report
vendor/bin/phpunit --coverage-html coverage
```

## Documentation

- Update the README.md if you change functionality
- Add PHPDoc comments to new methods
- Update example files if relevant

## Questions?

Feel free to open an issue with your question or reach out to the maintainers.

Thank you for contributing! 🙏
