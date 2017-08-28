for (var i = 0; i < 1000000; i++) ; // Simulate a whole bunch of JavaScript parsing and execution to help exaggerate the problem.

YAHOO.util.Event.onDOMReady(
	function() {
		var div = document.createElement('div');
		div.innerHTML = 'onDOMReady successfully fired after the DOM was ready :)';
		try {
			document.body.appendChild(div);
		} catch (e) {
			alert('onDOMReady fired before the DOM was ready :(');
		}
	}
);