var importContentUrl = "http://localhost:81/ilias/trunk/importLm.php"; 							// Stefan internal NAT
var importTrackingUrl = "http://localhost:81/ilias/trunk/ilias.php?baseClass=ilSAHSPresentationGUI&cmd=cmi";		// Stefan internal NAT
//&ref_id=55";  

function print(txt) {
	var divOut = document.getElementById("out");
	divOut.innerHTML = "";
	divOut.innerHTML = txt;
 }

function checkCallback(success) {
	var divCheck = document.getElementById("divCheckSopConnector");
	var divManager = document.getElementById("divOfflineManager");
	
	if (success) {
		divCheck.style.display = "none";
		divManager.style.display = "block";	
	}
	else {
		divCheck.style.display = "none";
		divManager.style.display = "none";
		print("no sopConnector found"); // Check user_agent Firefox and offer xpi download if allowed 
	}
} 

function checkSopConnector(handler) {
	var timeout = 5000;
	var sopFound = false;
	var counter = 0;

	var timer = setInterval(function() { 
		counter+=100;
		try {
			if (sopConnector) {
				sopFound = true;
				clearInterval(timer);
				return;
				//checkSopVersion(sopConnector.getSopVersion());
				//alert(sopConnector.getSopVersion());
			}
		}
		catch(e) {}
		finally {
			if (sopFound) {
				clearInterval(timer);
				if (typeof handler == "function") {
					handler.call(null,true);
				}
				return;
			}
			if (counter > timeout) {
				clearInterval(timer);
				if (typeof handler == "function") {
					handler.call(null,false);
				}
				return;
			}
		}
	} , 100);
}

function checkSopVersion(v) {
	var metas = document.getElementsByTagName('meta');  
	for (var i=0; i<metas.length; i++) {
		if (metas[i].getAttribute("name") == "require-sop-version") {
			var reqV =  metas[i].getAttribute("content");
			//alert(v + reqV);
		} 
	}
}



function getOfflineUrl(id) {
	var url = sopConnector.getOfflineUrl(id);
	return url;
}

function openLm(id) {
	var url = getOfflineUrl(id);
	open(url,"SCORM Offline Player");
}

function importLm(id) { // url: network address for binary and async zip download
	var url = sopConnector.atoB(importContentUrl + '?id='+id);
	function handler(success) {
		alert(success);
	}
	sopConnector.importLm(id, url, handler);
	
}

function importTracking(id, ref_id) {
	var url = sopConnector.atoB(importTrackingUrl + '&ref_id='+ref_id);
	function handler(success) {
		alert(success);
	}
	sopConnector.importTracking(id,url,handler);
}

checkSopConnector(checkCallback);

