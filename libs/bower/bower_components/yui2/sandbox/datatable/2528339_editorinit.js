YAHOO.widget.DataTable.prototype.initCellEditor = function(editor) {
    editor.subscribe("showEvent", this._onEditorShowEvent, this, true);
    editor.subscribe("keydownEvent", this._onEditorKeydownEvent, this, true);
    editor.subscribe("revertEvent", this._onEditorRevertEvent, this, true);
    editor.subscribe("saveEvent", this._onEditorSaveEvent, this, true);
    editor.subscribe("cancelEvent", this._onEditorCancelEvent, this, true);
    editor.subscribe("blurEvent", this._onEditorBlurEvent, this, true);
    editor.subscribe("blockEvent", this._onEditorBlockEvent, this, true);
    editor.subscribe("unblockEvent", this._onEditorUnblockEvent, this, true);
};
