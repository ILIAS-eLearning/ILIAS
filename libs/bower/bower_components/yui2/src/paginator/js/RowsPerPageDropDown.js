(function () {

var Paginator = YAHOO.widget.Paginator,
    l         = YAHOO.lang,
    setId     = YAHOO.util.Dom.generateId;

/**
 * ui Component to generate the rows-per-page dropdown
 *
 * @namespace YAHOO.widget.Paginator.ui
 * @class RowsPerPageDropdown
 * @for YAHOO.widget.Paginator
 *
 * @constructor
 * @param p {Pagintor} Paginator instance to attach to
 */
Paginator.ui.RowsPerPageDropdown = function (p) {
    this.paginator = p;

    p.subscribe('rowsPerPageChange',this.update,this,true);
    p.subscribe('rowsPerPageOptionsChange',this.rebuild,this,true);
    p.subscribe('totalRecordsChange',this._handleTotalRecordsChange,this,true);
    p.subscribe('destroy',this.destroy,this,true);

    // TODO: make this work
    p.subscribe('rowsPerPageDropdownClassChange',this.rebuild,this,true);
};

/**
 * Decorates Paginator instances with new attributes. Called during
 * Paginator instantiation.
 * @method init
 * @param p {Paginator} Paginator instance to decorate
 * @static
 */
Paginator.ui.RowsPerPageDropdown.init = function (p) {

    /**
     * Array of available rows-per-page sizes.  Converted into select options.
     * Array values may be positive integers or object literals in the form<br>
     * { value : NUMBER, text : STRING }
     * @attribute rowsPerPageOptions
     * @default []
     */
    p.setAttributeConfig('rowsPerPageOptions', {
        value : [],
        validator : l.isArray
    });

    /**
     * CSS class assigned to the select node
     * @attribute rowsPerPageDropdownClass
     * @default 'yui-pg-rpp-options'
     */
    p.setAttributeConfig('rowsPerPageDropdownClass', {
        value : 'yui-pg-rpp-options',
        validator : l.isString
    });
};

Paginator.ui.RowsPerPageDropdown.prototype = {

    /**
     * select node
     * @property select
     * @type HTMLElement
     * @private
     */
    select  : null,


    /**
     * option node for the optional All value
     *
     * @property all
     * @type HTMLElement
     * @protected
     */
    all : null,

    /**
     * Generate the select and option nodes and returns the select node.
     * @method render
     * @param id_base {string} used to create unique ids for generated nodes
     * @return {HTMLElement}
     */
    render : function (id_base) {
        this.select = document.createElement('select');
        setId(this.select, id_base + '-rpp');
        this.select.className = this.paginator.get('rowsPerPageDropdownClass');
        this.select.title = 'Rows per page';

        YAHOO.util.Event.on(this.select,'change',this.onChange,this,true);

        this.rebuild();

        return this.select;
    },

    /**
     * (Re)generate the select options.
     * @method rebuild
     */
    rebuild : function (e) {
        var p       = this.paginator,
            sel     = this.select,
            options = p.get('rowsPerPageOptions'),
            opt,cfg,val,i,len;

        this.all = null;

        for (i = 0, len = options.length; i < len; ++i) {
            cfg = options[i];
            opt = sel.options[i] ||
                  sel.appendChild(document.createElement('option'));
            val = l.isValue(cfg.value) ? cfg.value : cfg;
            opt.text = l.isValue(cfg.text) ? cfg.text : cfg;

            if (l.isString(val) && val.toLowerCase() === 'all') {
                this.all  = opt;
                opt.value = p.get('totalRecords');
            } else{
                opt.value = val;
            }

        }

        while (sel.options.length > options.length) {
            sel.removeChild(sel.firstChild);
        }

        this.update();
    },

    /**
     * Select the appropriate option if changed.
     * @method update
     * @param e {CustomEvent} The calling change event
     */
    update : function (e) {
        if (e && e.prevValue === e.newValue) {
            return;
        }

        var rpp     = this.paginator.get('rowsPerPage')+'',
            options = this.select.options,
            i,len;

        for (i = 0, len = options.length; i < len; ++i) {
            if (options[i].value === rpp) {
                options[i].selected = true;
                break;
            }
        }
    },

    /**
     * Listener for the select's onchange event.  Sent to setRowsPerPage method.
     * @method onChange
     * @param e {DOMEvent} The change event
     */
    onChange : function (e) {
        this.paginator.setRowsPerPage(
                parseInt(this.select.options[this.select.selectedIndex].value,10));
    },

    /**
     * Updates the all option value (and Paginator's rowsPerPage attribute if
     * necessary) in response to a change in the Paginator's totalRecords.
     *
     * @method _handleTotalRecordsChange
     * @param e {Event} attribute change event
     * @protected
     */
    _handleTotalRecordsChange : function (e) {
        if (!this.all || (e && e.prevValue === e.newValue)) {
            return;
        }

        this.all.value = e.newValue;
        if (this.all.selected) {
            this.paginator.set('rowsPerPage',e.newValue);
        }
    },

    /**
     * Removes the select node and clears event listeners
     * @method destroy
     * @private
     */
    destroy : function () {
        YAHOO.util.Event.purgeElement(this.select);
        this.select.parentNode.removeChild(this.select);
        this.select = null;
    }
};

})();
