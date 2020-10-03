let browserSupportsTextareaTextNodes;

/**
 * @param {HTMLElement} input
 * @return {boolean}
 */
function canManipulateViaTextNodes(input) {
	if (input.nodeName !== "TEXTAREA") {
		return false;
	}

	if (typeof browserSupportsTextareaTextNodes === "undefined") {
		const textarea = document.createElement("textarea");
		textarea.value = 1;
		browserSupportsTextareaTextNodes = !!textarea.firstChild;
	}

	return browserSupportsTextareaTextNodes;
}


function insertTextIntoTextField(text, obj_id)
{
	const input = document.getElementById(obj_id);

	input.focus();

	const isSuccess = document.execCommand("insertText", false, text);
	if (!isSuccess) {
		const start = input.selectionStart, end = input.selectionEnd;

		if (typeof input.setRangeText === "function") {
			input.setRangeText(text);
		} else {
			const range = document.createRange(), textNode = document.createTextNode(text);

			if (canManipulateViaTextNodes(input)) {
				let node = input.firstChild;

				if (!node) {
					input.appendChild(textNode);
				} else {
					let offset = 0, startNode = null, endNode = null;

					while (node && (startNode === null || endNode === null)) {
						const nodeLength = node.nodeValue.length;

						if (start >= offset && start <= offset + nodeLength) {
							range.setStart((startNode = node), start - offset);
						}

						if (end >= offset && end <= offset + nodeLength) {
							range.setEnd((endNode = node), end - offset);
						}

						offset += nodeLength;
						node = node.nextSibling;
					}

					if (start !== end) {
						range.deleteContents();
					}
				}
			}

			if (canManipulateViaTextNodes(input) && range.commonAncestorContainer.nodeName === '#text') {
				range.insertNode(textNode);
			} else {
				const value = input.value;
				input.value = value.slice(0, start) + text + value.slice(end);
			}
		}

		input.setSelectionRange(start + text.length, start + text.length);

		const e = document.createEvent("UIEvent");
		e.initEvent("input", true, false);
		input.dispatchEvent(e);
	}
}

// removes ',' at the ending of recipients textfield
function getStripCommaCallback(obj)
{
	return function ()
	{
		var val = obj.value.replace(/^\s+/, '').replace(/\s+$/, '');
		var stripcount = 0;
		var i;
		for (i = 0; i < val.length && val.charAt(val.length - i - 1) == ','; i++)
			stripcount++;
		obj.value = val.substr(0, val.length - stripcount);
	}
}

// initializes textfields for comma stripping on leaving recipients textfields
il.Util.addOnLoad(
	function()
	{
		var ar = ['rcp_to', 'rcp_cc', 'rcp_bcc'];
		for(var i = 0; i < ar.length; i++)
		{
			var obj = document.getElementById(ar[i]); 
			if (obj)
			{
				obj.onblur = getStripCommaCallback(document.getElementById(ar[i]));
			}
		}
	}
);
