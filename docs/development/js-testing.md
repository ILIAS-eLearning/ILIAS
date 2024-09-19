# JavaScript Testing

Unit tests are important for continuous development and integration. This also includes JavaScript unit tests. ILIAS
uses the [Jest](https://jestjs.io/) framework for client-side unit tests. Check out their official documentation for
more detailed information.

## Writing Tests

A JavaScript unit test can be implemented, by simply creating a `.js` file inside a components `tests/` directory 
(regardless of its nesting). Jest will scan this directory recursively and treat any JavaScript file as a unit test.

### Using Jest

When writing a unit test, you need to import each global from the `@jest/globals` node module. This will give you proper
intelisense and make your code compliant to our code-style rules. An example unit test could look something like this:

```javascript
import { describe, it } from '@jest/globals';

describe('Test Block 1', function () {
  it('Test Case 1', function () {
    // ...
  });
  // ...
});
```

Jest provides a set of functions which can be used to set up your test blocks and/or test suites:

```javascript
import { beforeAll, beforeEach, afterAll, afterEach, describe } from '@jest/globals';

describe('Test Block 1', () => {
  beforeAll(() => {
    // runs once before the first test in this block
  });

  afterAll(() => {
    // runs once after the last test in this block
  });

  beforeEach(() => {
    // runs before each test in this block
  });

  afterEach(() => {
    // runs after each test in this block
  });
});
```

If one of these functions is used outside of a `describe()` block, their behaviour will affect the scope of the entire
test suite. E.g. `beforeEach()` will be called before **every** `it()` test case, regardless of its `describe()` block.

We peform our assertions using Jest's built-in `expect` object:

```javascript
import { expect } from '@jest/globals';

const object = { foo: 'foo', bar: 'bar', };

expect(object).toBeInstanceOf(Object);
expect(object).toContainEqual(expect.not.objectContaining({foobar: 'foobar'}));
expect(object).toContainEqual(expect.objectContaining({foo: 'foo'}));
// ...
```

Please refer to the official documentation for a full list of possible assertions: 
https://jestjs.io/docs/expect#reference

### Example

You can find a working example of a test suite in the [`js-unit-test`](./code-examples/js-unit-test) directory. You can run the example in the
terminal with:

```bash
# Run the example
npm test -- docs/development/js/code-examples/js-unit-test-example/tests/Component.js
```

## Running Tests

To run the JavaScript unit tests you can simply run the following command:

```bash
# Run all tests
npm test
```

However, if you like to have more control over which directory or file should be tested, you can use these commands
instead:

```bash
# Run all tests in a specific directory
npm test -- path/to/directory
```

```bash
# Run one specific test file
npm test -- path/to/file.js
```
