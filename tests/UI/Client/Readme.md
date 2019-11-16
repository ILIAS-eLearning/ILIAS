Note, client side (JS) testings are highly experimental and not yet required. Further in is not sure, 
in which shape client side testing in future will be shaped out in ILIAS Development.

Note that they are currently not part of any CI process.

If you intend to use this small infrastructure here, you may do the following:

- Add a folder providing your tests (e.g. see the Counter Folder).
- Add the necessary JS and html files for your tests.
- In one of our Test Files define a scope by an object containing all your functions to be tested (see Counter/CounterTest.js as an example).
  - Note: Those functions must return true if succeeded or false if not.
- In this test file, also contain a html variable pointing to your html source (see Counter/CounterTest.js as an example).
- Add this object to the array defined in TestPackages.js
- Add the js file to index.html (and all other need js sources)
- Run the tests by opening test/UI/Client/index.html in your browser


