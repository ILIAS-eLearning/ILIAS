/* XHTML Xtras Plugin
 * Andrew Tetlaw 2006/02/21
 * http://tetlaw.id.au/view/blog/xhtml-xtras-plugin-for-tinymce/
 */
function preinit() {
	// Initialize
	tinyMCE.setWindowArg('mce_windowresize', false);
}

function init() {
	tinyMCEPopup.resizeToInnerSize();
	SXE.initElementDialog('ins');
	if (SXE.currentAction == "update") {
		setFormValue('datetime', tinyMCE.getAttrib(SXE.updateElement, 'datetime'));
		setFormValue('cite', tinyMCE.getAttrib(SXE.updateElement, 'cite'));
		SXE.showRemoveButton();
	}
	setTimeout('initCal()',1); //needed for IE *shrug* I think maybe the template process creates a pause while the body.innerHTML returns to the DOM...
}

function initCal () {
	Calendar.setup({
		inputField  : "datetime",
		ifFormat    : "%Y-%m-%dT%H:%M:%S",
		button      : "datetime_picker",
		showsTime : true,
		singleClick : true,
		align : "BR",
		step : 1,
		weekNumbers : false,
		electric : false,
		cache : true
	});
}

function setElementAttribs(elm) {
	setAllCommonAttribs(elm);
	setAttrib(elm, 'datetime');
	setAttrib(elm, 'cite');
}

function insertIns() {
	var elm = tinyMCE.getParentElement(SXE.focusElement, 'ins');
	tinyMCEPopup.execCommand('mceBeginUndoLevel');
	if (elm == null) {
		var s = SXE.inst.selection.getSelectedHTML();
		if(s.length > 0) {
			tinyMCEPopup.execCommand('mceInsertContent', false, '<ins id="#sxe_temp_ins#">' + s + '</ins>');
			var elementArray = tinyMCE.getElementsByAttributeValue(SXE.inst.getBody(), 'ins', 'id', '#sxe_temp_ins#');
			for (var i=0; i<elementArray.length; i++) {
				var elm = elementArray[i];
				setElementAttribs(elm);
			}
		}
	} else {
		setElementAttribs(elm);
	}
	tinyMCE.triggerNodeChange();
	tinyMCEPopup.execCommand('mceEndUndoLevel');
	tinyMCEPopup.close();
}

function removeIns() {
	SXE.removeElement('ins');
	tinyMCEPopup.close();
}