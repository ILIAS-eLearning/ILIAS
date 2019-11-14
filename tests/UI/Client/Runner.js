/**
 * Runner is loaded by index and runs all tests defined in TestPackage as soon as the document is loaded
 */
$( document ).ready(function() {
	var TestPackage = [
		CounterTests,
		//Place further test packages here.
	];

	run($,TestPackage);
});

var run = function ($, unit_packages) {
	var total = 0;
	var passed = 0;
	var failed = [];
	var promises = [];

	$.each(unit_packages,function (index,unit_package) {
		promises.push(
			//Additional html defined in yourPackage.html is loaded here
			$.ajax(unit_package.html).done(function (data) {
				console.log("Executing Package "+index);
				$("body").html(data);
				$.each(unit_package,function (index,test) {
					if(typeof test === "function"){
						console.log("Executing Test "+index);

						total++;
						try {
							var result = test();
						}
						catch (e) {
							console.error("Exception during test:"+index +": "+e);
						}
						if(!result){
							console.warn("Test Failed:"+index);
							failed.push(index);
						}else{
							passed++;
						}
					}

				});
			})
		);
	});

	//Will be executed when all tests are done.
	$.when.apply($, promises).then(function() {
		console.log("All Tests have been executed.");
		var result = passed+" of "+total +" tests passed. See console for details.";
		if(failed.length){
			result += "<hr>"+"Failed Tests: "+failed.join(", ");
		}
		console.log(result);
		$("html").html(result);
	});
};


