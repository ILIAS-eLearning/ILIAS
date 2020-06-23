# JS Unit Testing

ILIAS is using [Mocha](https://mochajs.org/) as test framework for Javascript unit tests. The `esm` extension allows to use ES6 modules seamlessly. [Chai](https://www.chaijs.com/) is being used as assertion library.

## Install

(not necessary as long as we keep npm modules in our git repo)

```
> npm i --save-dev mocha
> npm i --save-dev esm
> npm i --save-dev chai
```

## package.json changes

```
{
  ...
  "scripts": {
    "test": "mocha --require esm"
  },
  ...
}
```

## Run tests

```
> npm test
```

## Example

Your tests should be located in a subdirectory `test`.

See [js-unit-test-example/test/component.test.js](./js-unit-test-example/test/component.test.js).