(function () {

var Paginator = YAHOO.widget.Paginator,
    l         = YAHOO.lang,
    setId     = YAHOO.util.Dom.generateId;

/**
 * ui Component to generate the link to jump to the next page.
 *
 * @namespace YAHOO.widget.Paginator.ui
 * @class NextPageLink
 * @for YAHOO.widget.Paginator
 *
 * @constructor
 * @param p {Pagintor} Paginator instance to attach to
 */
Paginator.ui.NextPageLink = function (p) {
    this.paginator = p;

    p.subscribe('recordOffsetChange', this.update,this,true);
    p.subscribe('rowsPerPageChange', this.update,this,true);
    p.subscribe('totalRecordsChange', this.update,this,true);
    p.subscribe('destroy',this.destroy,this,true);

    // TODO: make this work
    p.subscribe('nextPageLinkLabelChange', this.update,this,true);
    p.subscribe('nextPageLinkClassChange', this.update,this,true);
};

/**
 * Decorates Paginator instances with new attributes. Called during
 * Paginator instantiation.
 * @method init
 * @param p {Paginator} Paginator instance to decorate
 * @static
 */
Paginator.ui.NextPageLink.init = function (p) {

    /**
     * Used as innerHTML for the next page link/span.
     * @attribute nextPageLinkLabel
     * @default 'next &gt;'
     */
    p.setAttributeConfig('nextPageLinkLabel', {
        value : 'next &gt;',
        validator : l.isString
    });

    /**
     * CSS class assigned to the link/span
     * @attribute nextPageLinkClass
     * @default 'yui-pg-next'
     */
    p.setAttributeConfig('nextPageLinkClass', {
        value : 'yui-pg-next',
        validator : l.isString
    });

    /**
     * Used as title for the next page link.
     * @attribute nextPageLinkTitle
     * @default 'Next Page'
     */
    p.setAttributeConfig('nextPageLinkTitle', {
        value : 'Next Page',
        validator : l.isString
    });

};

Paginator.ui.NextPageLink.prototype = {

    /**
     * Currently placed HTMLElement node
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
            c     = p.get('nextPageLinkClass'),
            label = p.get('nextPageLinkLabel'),
            last  = p.getTotalPages(),
            title = p.get('nextPageLinkTitle');

        this.link     = document.createElement('a');
        this.span     = document.createElement('span');

        setId(this.link, id_base + '-next-link');
        this.link.href      = '#';
        this.link.className = c;
        this.link.innerHTML = label;
        this.link.title     = title;
        YAHOO.util.Event.on(this.link,'click',this.onClick,this,true);

        setId(this.span, id_base + '-next-span');
        this.span.className = c;
        this.span.innerHTML = label;

        this.current = p.getCurrentPage() === last ? this.span : this.link;

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

        var last = this.paginator.getTotalPages(),
            par  = this.current ? this.current.parentNode : null;

        if (this.paginator.getCurrentPage() !== last) {
            if (par && this.current === this.span) {
                par.replaceChild(this.link,this.current);
                this.current = this.link;
            }
        } else if (this.current === this.link) {
            if (par) {
                par.replaceChild(this.span,this.current);
                this.current = this.span;
            }
        }
    },

    /**
     * Removes the link/span node and clears event listeners
     * @method destroy
     * @private
     */
    destroy : function () {
        YAHOO.util.Event.purgeElement(this.link);
        this.current.parentNode.removeChild(this.current);
        this.link = this.span = null;
    },

    /**
     * Listener for the link's onclick event.  Passes to setPage method.
     * @method onClick
     * @param e {DOMEvent} The click event
     */
    onClick : function (e) {
        YAHOO.util.Event.stopEvent(e);
        this.paginator.setPage(this.paginator.getNextPage());
    }
};

})();
