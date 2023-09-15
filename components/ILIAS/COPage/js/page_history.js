// for enhanced RecentChanges
function toggleVisibility(_levelId, _otherId, _linkId) {
	var thisLevel = document.getElementById(_levelId);
	var otherLevel = document.getElementById(_otherId);
	var linkLevel = document.getElementById(_linkId);
	if (thisLevel.style.display == 'none') {
		thisLevel.style.display = 'block';
		otherLevel.style.display = 'none';
		linkLevel.style.display = 'inline';
	} else {
		thisLevel.style.display = 'none';
		otherLevel.style.display = 'inline';
		linkLevel.style.display = 'none';
	}
}

function historyRadios(parent) {
	var inputs = parent.getElementsByTagName('input');
	var radios = [];
	for (var i = 0; i < inputs.length; i++) {
		if (inputs[i].name == "left" || inputs[i].name == "right") {
			radios[radios.length] = inputs[i];
		}
	}
	return radios;
}

// check selection and tweak visibility/class onclick
function diffcheck() {
	var dli = false; // the li where the diff radio is checked
	var oli = false; // the li where the oldid radio is checked
	
	var htable = document.getElementById('hist_table');
	if (!htable)
	{
		return;
	}
	var rows = htable.getElementsByTagName('tr');
	for (var i=0;i<rows.length;i++) {
		var inputs = historyRadios(rows[i]);
		if (inputs[1] && inputs[0]) {
			if (inputs[1].checked || inputs[0].checked) { // this row has a checked radio button
				if (inputs[1].checked && inputs[0].checked && inputs[0].value == inputs[1].value) {
					return false;
				}
				if (oli) { // it's the second checked radio
					if (inputs[1].checked) {
						oli.className = "selected";
						return false;
					}
				} else if (inputs[0].checked) {
					return false;
				}
				if (inputs[0].checked) {
					dli = rows[i];
				}
				if (!oli) {
					inputs[0].style.visibility = 'hidden';
				}
				if (dli) {
					inputs[1].style.visibility = 'hidden';
				}
//				rows[i].className = "selected";
				oli = rows[i];
			}  else { // no radio is checked in this row
				if (!oli) {
					inputs[0].style.visibility = 'hidden';
				} else {
					inputs[0].style.visibility = 'visible';
				}
				if (dli) {
					inputs[1].style.visibility = 'hidden';
				} else {
					inputs[1].style.visibility = 'visible';
				}
//				rows[i].className = "";
			}
		}
	}
	return true;
}

// page history stuff
// attach event handlers to the input elements on history page
function histrowinit()
{
	var j = 0;
	var inp = document.getElementsByTagName('input');
	for (var i = 0; i < inp.length; i++)
	{
		if (inp[i].name == "left" || inp[i].name == "right")
		{
			inp[i].onclick = diffcheck;
			j++;
		}
	}
	diffcheck();
}

il.Util.addOnLoad(histrowinit);
