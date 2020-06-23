var Typeahead = require('typeahead');

var input = document.createElement('input');

// source is an array of items
var ta = Typeahead(input, {
    source: ['foo', 'bar', 'baz']
});

input // =>

var input = document.createElement('input');

// you can also specify a function
var ta = Typeahead(input, {
    source: function(query, cb) {
        // simulate some ajax
        // call 'callback' when we have a result array
        cb(['foo', 'bar']);
    }
});

input // =>

// If you want to know when the input changes, bind to the `change` event of the input element

var div = document.createElement('div');

input.addEventListener('change', function() {
    div.innerHTML = 'input value: ' + input.value;
});

div // =>
