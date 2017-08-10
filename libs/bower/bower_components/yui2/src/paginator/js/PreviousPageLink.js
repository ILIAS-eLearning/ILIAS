(function () {

var Paginator = YAHOO.widget.Paginator,
    l         = YAHOO.lang,
    setId     = YAHOO.util.Dom.generateId;

/**
 * ui Component to generate the link to jump to the previous page.
 *
 * @namespace YAHOO.widget.Paginator.ui
 * @class PreviousPageLink
 * @for YAHOO.widget.Paginator
 *
 * @constructor
 * @param p {Pagintor} Paginator instance to attach to
 */
Paginator.ui.PreviousPageLink = function (p) {
    this.paginator = p;

    p.subscribe('recordOffsetChange',this.update,this,true);
    p.subscribe('rowsPerPageChange',this.update,this,true);
    p.subscribe('totalRecordsChange',this.update,this,true);
    p.subscribe('destroy',this.destroy,this,true);

    // TODO: make this work
    p.subscribe('previousPageLinkLabelChange',this.update,this,true);
    p.subscribe('previousPageLinkClassChange',this.update,this,true);
};

/**
 * Decorates Paginator instances with new attributes. Called during
 * Paginator instantiation.
 * @method init
 * @param p {Paginator} Paginator instance to decorate
 * @static
 */
Paginator.ui.PreviousPageLink.init = function (p) {

    /**
     * Used as innerHTML for the previous page link/span.
     * @attribute previousPageLinkLabel
     * @default '&lt; prev'
     */
    p.setAttributeConfig('previousPageLinkLabel', {
        value : '&lt; prev',
        validator : l.isString
    });

    /**
     * CSS class assigned to the link/span
     * @attribute previousPageLinkClass
     * @default 'yui-pg-previous'
     */
    p.setAttributeConfig('previousPageLinkClass', {
        value : 'yui-pg-previous',
        validator : l.isString
    });

    /**
     * Used as title for the previous page link.
     * @attribute previousPageLinkTitle
     * @default 'Previous Page'
     */
    p.setAttributeConfig('previousPageLinkTitle', {
        value : 'Previous Page',
        validator : l.isString
    });

};

Paginator.ui.PreviousPageLink.prototype = {

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
            c     = p.get('previousPageLinkClass'),
            label = p.get('previousPageLinkLabel'),
            title = p.get('previousPageLinkTitle');

        this.link     = document.createElement('a');
        this.span     = document.createElement('span');

        setId(this.link, id_base + '-prev-link');
        this.link.href      = '#';
        this.link.className = c;
        this.link.innerHTML = label;
        this.link.title     = title;
        YAHOO.util.Event.on(this.link,'click',this.onClick,this,true);

        setId(this.span, id_base + '-prev-span');
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
        this.paginator.setPage(this.paginator.getPreviousPage());
    }
};

})();
