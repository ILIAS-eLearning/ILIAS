YAHOO.widget.BaseCellEditor.prototype.useIFrame = true;

YAHOO.widget.BaseCellEditor.prototype.origRender = YAHOO.widget.BaseCellEditor.prototype.render;
YAHOO.widget.BaseCellEditor.prototype.render = function() {
    this.origRender();

    // Render Cell Editor container shim element as first child of body
    if(this.useIFrame) {
        if(!this._elIFrame) {
            var elIFrame = document.createElement("iframe");
            elIFrame.src = "javascript:false";
            elIFrame.frameBorder = 0;
            elIFrame.scrolling = "no";
            elIFrame.style.display = "none";
            elIFrame.style.position = "absolute";
            elIFrame.style.zIndex = 9000;
            elIFrame.tabIndex = -1;
            elIFrame.role = "presentation";
            elIFrame.title = "Presentational iframe shim";
            document.body.insertBefore(elIFrame, document.body.firstChild);
            this._elIFrame = elIFrame;
        }
    }
};

YAHOO.widget.BaseCellEditor.prototype.origMove = YAHOO.widget.BaseCellEditor.prototype.move;
YAHOO.widget.BaseCellEditor.prototype.move = function() {
    this.origMove();

    if(this._elIFrame) {
        this._elIFrame.style.left = this.getContainerEl().style.left;
        this._elIFrame.style.top = this.getContainerEl().style.top;
    }
};

YAHOO.widget.BaseCellEditor.prototype.show = function() {
    var elContainer = this.getContainerEl(),
        elIFrame = this._elIFrame;
    this.resetForm();
    this.isActive = true;
    elContainer.style.display = "";
    if(elIFrame) {
        elIFrame.style.width = elContainer.offsetWidth + "px";
        elIFrame.style.height = elContainer.offsetHeight + "px";
        elIFrame.style.display = "";
    }
    this.focus();
    this.fireEvent("showEvent", {editor:this});
};

YAHOO.widget.BaseCellEditor.prototype.save = function() {
    // Get new value
    var inputValue = this.getInputValue();
    var validValue = inputValue;

    // Validate new value
    if(this.validator) {
        validValue = this.validator.call(this.getDataTable(), inputValue, this.value, this);
        if(validValue === undefined ) {
            if(this.resetInvalidData) {
                this.resetForm();
            }
            this.fireEvent("invalidDataEvent",
                    {editor:this, oldData:this.value, newData:inputValue});
            return;
        }
    }

    var oSelf = this;
    var finishSave = function(bSuccess, oNewValue) {
        var oOrigValue = oSelf.value;
        if(bSuccess) {
            // Update new value
            oSelf.value = oNewValue;
            oSelf.getDataTable().updateCell(oSelf.getRecord(), oSelf.getColumn(), oNewValue);

            // Hide CellEditor
            oSelf.getContainerEl().style.display = "none";
            if(oSelf._elIFrame) {
                oSelf._elIFrame.style.display = "none";
            }
            oSelf.isActive = false;
            oSelf.getDataTable()._oCellEditor =  null;

            oSelf.fireEvent("saveEvent",
                    {editor:oSelf, oldData:oOrigValue, newData:oSelf.value});
        }
        else {
            oSelf.resetForm();
            oSelf.fireEvent("revertEvent",
                    {editor:oSelf, oldData:oOrigValue, newData:oNewValue});
        }
        oSelf.unblock();
    };

    this.block();
    if(YAHOO.lang.isFunction(this.asyncSubmitter)) {
        this.asyncSubmitter.call(this, finishSave, validValue);
    }
    else {
        finishSave(true, validValue);
    }
};

YAHOO.widget.BaseCellEditor.prototype.cancel = function() {
    if(this.isActive) {
        this.getContainerEl().style.display = "none";
        if(this._elIFrame) {
            this._elIFrame.style.display = "none";
        }
        this.isActive = false;
        this.getDataTable()._oCellEditor =  null;
        this.fireEvent("cancelEvent", {editor:this});
    }
};