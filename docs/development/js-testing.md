# JavaScript Testing

Unit tests are important for continuous integration, which also includes JavaScript unit tests. ILIAS uses the
[mocha.js](https://mochajs.org/) library for client-side testing, along with the [chai.js](https://www.chaijs.com/)
assertion library. Check out their official documentation for more detailed information.

## Writing Tests

JavaScript unit tests should either be located in the ILIAS [`tests`](./tests) directory or in a module or services own
dedicated sub-directory. If you decide to introduce a new dedicated sub-directory outside of the `tests` directory you
must adjust the `spec` property in the global [`.mocharc.json`](./.mocharc.json) configuration file accordingly, e.g.

```json
{
  //...
  spec: [
    "path/to/testDir"
    //...
  ]
}
```

### Using Mocha.js

Mocha.js is a JavaScript test framework that runs on Node.js and in the browser. It does not natively support ES6
modules, which is why we use the [`@babel/register`](https://babeljs.io/docs/babel-register) to transpile ES6 modules at
runtime. This is why we need to pass the `--require "@babel/register"` flag to the `mocha` command, as will be described
later on.

When writing a test there are some global functions that you might use to setup and structure your tests:

```javascript
describe('Test Suite', function () {
  before(function () {
    // runs once before the first test in this block
  });

  after(function () {
    // runs once after the last test in this block
  });

  beforeEach(function () {
    // runs before each test in this block
  });

  afterEach(function () {
    // runs after each test in this block
  });

  // test cases
  it('Test Case 1', function () {
    // test case
  });
});
```

Each file should only contain one `describe()` block, which can contain multiple `it()` blocks. The `before*()`
and `after*()` can be used as needed.

**Please note the use of `function` instead of arrow-functions (`() => {}`), which is recommended by the official
documentation to allow using `this` in the right context.** Our code-style enforce the usage of arrow-functions over
normal ones, so we should only use `function` if we need to use `this`.

### Using Chai.js

Chai.js is an assertion library we use in combination with Mocha.js. It provides a lot of different ways to assert
things as we know them from tools like PHPUnit. Check out the official [documentation](https://www.chaijs.com/api/) for
more details. A simple example would be:

```javascript
import { assert } from 'chai';

describe('Test Suite', function () {
  it('Test Case 1', function () {
    assert.equal(1, 1);
  });
});
```

_You can also import and use `expect()` or `should()`, which are somewhat similar to normal assertions._

### Example

You can find a working example of a unit test in the [`js-unit-test`](./code-examples/js-unit-test) directory. You can
run the example in the terminal with:

```bash
# Run all tests in a specific directory
mocha --no-config --require "@babel/register" "./docs/development/js/code-examples/js-unit-test-example/test"
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
mocha --no-config --require "@babel/register" "path/to/directory"
```

```bash
# Run one specific test file
mocha --no-config --require "@babel/register" "path/to/file.js"
```

_Note that `mocha` might not be resolved properly in each shell, in which case you need to replace id
by `node_modules/.bin/mocha`._