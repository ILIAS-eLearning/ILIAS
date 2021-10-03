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

The expression for mocha to run the files which contain the tests are listed in package.json as such: 

```
{
  ...
  "scripts": {
    "test": "mocha tests/UI/Client/* --require esm"
  },
  ...
}
```

Note that it might be good practise to keep the tests cloze to the source an only symlink the files to the above
directories.


## Run tests

With this in place, the tests in the above dirs can be executed with:

```
> npm test
```

## Example

Your tests should be located in a subdirectory `test`.

See [js-unit-test-example/test/component.test.js](./js-unit-test-example/test/component.test.js).