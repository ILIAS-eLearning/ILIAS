(function () {

var Paginator = YAHOO.widget.Paginator,
    l         = YAHOO.lang,
    setId     = YAHOO.util.Dom.generateId;

/**
 * ui Component to generate the link to jump to the first page.
 *
 * @namespace YAHOO.widget.Paginator.ui
 * @class FirstPageLink
 * @for YAHOO.widget.Paginator
 *
 * @constructor
 * @param p {Pagintor} Paginator instance to attach to
 */
Paginator.ui.FirstPageLink = function (p) {
    this.paginator = p;

    p.subscribe('recordOffsetChange',this.update,this,true);
    p.subscribe('rowsPerPageChange',this.update,this,true);
    p.subscribe('totalRecordsChange',this.update,this,true);
    p.subscribe('destroy',this.destroy,this,true);

    // TODO: make this work
    p.subscribe('firstPageLinkLabelChange',this.update,this,true);
    p.subscribe('firstPageLinkClassChange',this.update,this,true);
};

/**
 * Decorates Paginator instances with new attributes. Called during
 * Paginator instantiation.
 * @method init
 * @param p {Paginator} Paginator instance to decorate
 * @static
 */
Paginator.ui.FirstPageLink.init = function (p) {

    /**
     * Used as innerHTML for the first page link/span.
     * @attribute firstPageLinkLabel
     * @default '&lt;&lt; first'
     */
    p.setAttributeConfig('firstPageLinkLabel', {
        value : '&lt;&lt; first',
        validator : l.isString
    });

    /**
     * CSS class assigned to the link/span
     * @attribute firstPageLinkClass
     * @default 'yui-pg-first'
     */
    p.setAttributeConfig('firstPageLinkClass', {
        value : 'yui-pg-first',
        validator : l.isString
    });

    /**
     * Used as title for the first page link.
     * @attribute firstPageLinkTitle
     * @default 'First Page'
     */
    p.setAttributeConfig('firstPageLinkTitle', {
        value : 'First Page',
        validator : l.isString
    });
};

// Instance members and methods
Paginator.ui.FirstPageLink.prototype = {

    /**
     * The currently placed HTMLElement node
     * @property current
     * @type HTMLElement
     * @private
     */
    current   : null,

    /**
     * Link node
     * @property link
     * @type HTMLElement
     * @private
     */
    link      : null,

    /**
     * Span node (inactive link)
     * @property span
     * @type HTMLElement
     * @private
     */
    span      : null,

    /**
     * Generate the nodes and return the appropriate node given the current
     * pagination state.
     * @method render
     * @param id_base {string} used to create unique ids for generated nodes
     * @return {HTMLElement}
     */
    render : function (id_base) {
        var p     = this.paginator,
            c     = p.get('firstPageLinkClass'),
            label = p.get('firstPageLinkLabel'),
            title = p.get('firstPageLinkTitle');

        this.link     = document.createElement('a');
        this.span     = document.createElement('span');

        setId(this.link, id_base + '-first-link');
        this.link.href      = '#';
        this.link.className = c;
        this.link.innerHTML = label;
        this.link.title     = title;
        YAHOO.util.Event.on(this.link,'click',this.onClick,this,true);

        setId(this.span, id_base + '-first-span');
        this.span.className = c;
        this.span.innerHTML = label;

        this.current = p.getCurrentPage() > 1 ? this.link : this.span;
        return this.current;
    },

    /**
     * Swap the link and span nodes if appropriate.
     * @method update
     * @param e {CustomEvent} The calling change event
     */
    update : function (e) {
        if (e && e.prevValue === e.newValue) {
            return;
        }

        var par = this.current ? this.current.parentNode : null;
        if (this.paginator.getCurrentPage() > 1) {
            if (par && this.current === this.span) {
                par.replaceChild(this.link,this.current);
                this.current = this.link;
            }
        } else {
            if (par && this.current === this.link) {
                par.replaceChild(this.span,this.current);
                this.current = this.span;
            }
        }
    },

    /**
     * Removes the link/span node and clears event listeners
     * removal.
     * @method destroy
     * @private
     */
    destroy : function () {
        YAHOO.util.Event.purgeElement(this.link);
        this.current.parentNode.removeChild(this.current);
        this.link = this.span = null;
    },

    /**
     * Listener for the link's onclick event.  Pass new value to setPage method.
     * @method onClick
     * @param e {DOMEvent} The click event
     */
    onClick : function (e) {
        YAHOO.util.Event.stopEvent(e);
        this.paginator.setPage(1);
    }
};

})();
