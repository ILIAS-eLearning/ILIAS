# JS Unit Testing

ILIAS is using [Mocha](https://mochajs.org/) as test framework for Javascript unit tests. The `esm` extension 
allows to use ES6 modules seamlessly. [Chai](https://www.chaijs.com/) is being used as assertion library.

## Install

(not necessary as long as we keep npm modules in our git repo)

```
> npm i --save-dev mocha
> npm i --save-dev esm
> npm i --save-dev chai
```

## package.json changes

The link to the mocha.config.js files that contains the tests is listed in package.json as such: 

```
{
  ...
  "scripts": {
    "test": "mocha --recursive --config tests/mocha.config.js --require esm"
  },
  ...
}
```

In tests/mocha.config.js file you can link your js test folders as such
```
module.exports = {
    spec: [
        'tests/UI/Client',
        'docs/development/js/js-unit-test-example/test',
    ],
};
```
## Run tests

With this in place, the tests in the above dirs can be executed with:

```
> npm test
```

## Example

Your tests should be located in a subdirectory `test`.

See [js-unit-test-example/test/component.test.js](./js-unit-test-example/test/component.test.js).