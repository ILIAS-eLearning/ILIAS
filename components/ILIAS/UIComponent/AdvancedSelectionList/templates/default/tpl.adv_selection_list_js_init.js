il.AdvancedSelectionList.init['{ID}'] = function ()
{
	il.AdvancedSelectionList.add('{ID}', {CFG});
	<!-- BEGIN js_item -->
	il.AdvancedSelectionList.addItem('{IT_ID}', '{IT_HID_NAME}', '{IT_HID_VAL}', '{IT_TITLE}');
	<!-- END js_item -->
	<!-- BEGIN init_hidden_input -->
	il.AdvancedSelectionList.setHiddenInput('{H2ID}', '{HID_NAME}', '{HID_VALUE}');
	<!-- END init_hidden_input -->
};
il.AdvancedSelectionList.init['{ID}']();

<!-- BEGIN asynch_bl -->
$("#ilAdvSelListAnchorText_{ASYNCH_TRIGGER_ID}").click(function() {
	il.Util.ajaxReplaceInner('{ASYNCH_URL}', 'ilAdvSelListTable_{ASYNCH_ID}');
	$(this).unbind("click");
});
<!-- END asynch_bl -->
