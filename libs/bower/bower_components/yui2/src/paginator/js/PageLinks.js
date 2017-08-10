(function () {

var Paginator = YAHOO.widget.Paginator,
    l         = YAHOO.lang,
    setId     = YAHOO.util.Dom.generateId;

/**
 * ui Component to generate the page links
 *
 * @namespace YAHOO.widget.Paginator.ui
 * @class PageLinks
 * @for YAHOO.widget.Paginator
 *
 * @constructor
 * @param p {Pagintor} Paginator instance to attach to
 */
Paginator.ui.PageLinks = function (p) {
    this.paginator = p;

    p.subscribe('recordOffsetChange',this.update,this,true);
    p.subscribe('rowsPerPageChange',this.update,this,true);
    p.subscribe('totalRecordsChange',this.update,this,true);
    p.subscribe('pageLinksChange',   this.rebuild,this,true);
    p.subscribe('pageLinkClassChange', this.rebuild,this,true);
    p.subscribe('currentPageClassChange', this.rebuild,this,true);
    p.subscribe('destroy',this.destroy,this,true);

    //TODO: Make this work
    p.subscribe('pageLinksContainerClassChange', this.rebuild,this,true);
};

/**
 * Decorates Paginator instances with new attributes. Called during
 * Paginator instantiation.
 * @method init
 * @param p {Paginator} Paginator instance to decorate
 * @static
 */
Paginator.ui.PageLinks.init = function (p) {

    /**
     * CSS class assigned to each page link/span.
     * @attribute pageLinkClass
     * @default 'yui-pg-page'
     */
    p.setAttributeConfig('pageLinkClass', {
        value : 'yui-pg-page',
        validator : l.isString
    });

    /**
     * CSS class assigned to the current page span.
     * @attribute currentPageClass
     * @default 'yui-pg-current-page'
     */
    p.setAttributeConfig('currentPageClass', {
        value : 'yui-pg-current-page',
        validator : l.isString
    });

    /**
     * CSS class assigned to the span containing the page links.
     * @attribute pageLinksContainerClass
     * @default 'yui-pg-pages'
     */
    p.setAttributeConfig('pageLinksContainerClass', {
        value : 'yui-pg-pages',
        validator : l.isString
    });

    /**
     * Maximum number of page links to display at one time.
     * @attribute pageLinks
     * @default 10
     */
    p.setAttributeConfig('pageLinks', {
        value : 10,
        validator : Paginator.isNumeric
    });

    /**
     * Function used generate the innerHTML for each page link/span.  The
     * function receives as parameters the page number and a reference to the
     * paginator object.
     * @attribute pageLabelBuilder
     * @default function (page, paginator) { return page; }
     */
    p.setAttributeConfig('pageLabelBuilder', {
        value : function (page, paginator) { return page; },
        validator : l.isFunction
    });

    /**
     * Function used generate the title for each page link.  The
     * function receives as parameters the page number and a reference to the
     * paginator object.
     * @attribute pageTitleBuilder
     * @default function (page, paginator) { return page; }
     */
    p.setAttributeConfig('pageTitleBuilder', {
        value : function (page, paginator) { return "Page " + page; },
        validator : l.isFunction
    });
};

/**
 * Calculates start and end page numbers given a current page, attempting
 * to keep the current page in the middle
 * @static
 * @method calculateRange
 * @param {int} currentPage  The current page
 * @param {int} totalPages   (optional) Maximum number of pages
 * @param {int} numPages     (optional) Preferred number of pages in range
 * @return {Array} [start_page_number, end_page_number]
 */
Paginator.ui.PageLinks.calculateRange = function (currentPage,totalPages,numPages) {
    var UNLIMITED = Paginator.VALUE_UNLIMITED,
        start, end, delta;

    // Either has no pages, or unlimited pages.  Show none.
    if (!currentPage || numPages === 0 || totalPages === 0 ||
        (totalPages === UNLIMITED && numPages === UNLIMITED)) {
        return [0,-1];
    }

    // Limit requested pageLinks if there are fewer totalPages
    if (totalPages !== UNLIMITED) {
        numPages = numPages === UNLIMITED ?
                    totalPages :
                    Math.min(numPages,totalPages);
    }

    // Determine start and end, trying to keep current in the middle
    start = Math.max(1,Math.ceil(currentPage - (numPages/2)));
    if (totalPages === UNLIMITED) {
        end = start + numPages - 1;
    } else {
        end = Math.min(totalPages, start + numPages - 1);
    }

    // Adjust the start index when approaching the last page
    delta = numPages - (end - start + 1);
    start = Math.max(1, start - delta);

    return [start,end];
};


Paginator.ui.PageLinks.prototype = {

    /**
     * Current page
     * @property current
     * @type number
     * @private
     */
    current     : 0,

    /**
     * Span node containing the page links
     * @property container
     * @type HTMLElement
     * @private
     */
    container   : null,


    /**
     * Generate the nodes and return the container node containing page links
     * appropriate to the current pagination state.
     * @method render
     * @param id_base {string} used to create unique ids for generated nodes
     * @return {HTMLElement}
     */
    render : function (id_base) {
        var p = this.paginator;

        // Set up container
        this.container = document.createElement('span');
        setId(this.container, id_base + '-pages');
        this.container.className = p.get('pageLinksContainerClass');
        YAHOO.util.Event.on(this.container,'click',this.onClick,this,true);

        // Call update, flagging a need to rebuild
        this.update({newValue : null, rebuild : true});

        return this.container;
    },

    /**
     * Update the links if appropriate
     * @method update
     * @param e {CustomEvent} The calling change event
     */
    update : function (e) {
        if (e && e.prevValue === e.newValue) {
            return;
        }

        var p           = this.paginator,
            currentPage = p.getCurrentPage();

        // Replace content if there's been a change
        if (this.current !== currentPage || !currentPage || e.rebuild) {
            var labelBuilder = p.get('pageLabelBuilder'),
                titleBuilder = p.get('pageTitleBuilder'),
                range        = Paginator.ui.PageLinks.calculateRange(
                                currentPage,
                                p.getTotalPages(),
                                p.get('pageLinks')),
                start        = range[0],
                end          = range[1],
                content      = '',
                linkTemplate,i,spanTemplate;

            linkTemplate = '<a href="#" class="{class}" page="{page}" title="{title}">{label}</a>';
            spanTemplate = '<span class="{class}">{label}</span>';
            for (i = start; i <= end; ++i) {

                if (i === currentPage) {
                    content += l.substitute(spanTemplate, {
                        'class' : p.get('currentPageClass') + ' ' + p.get('pageLinkClass'),
                        'label' : labelBuilder(i,p)
                    });

                } else {
                    content += l.substitute(linkTemplate, {
                        'class' : p.get('pageLinkClass'),
                        'page'  : i,
                        'label' : labelBuilder(i,p),
                        'title' : titleBuilder(i,p)
                    });
                }
            }

            this.container.innerHTML = content;
        }
    },

    /**
     * Force a rebuild of the page links.
     * @method rebuild
     * @param e {CustomEvent} The calling change event
     */
    rebuild     : function (e) {
        e.rebuild = true;
        this.update(e);
    },

    /**
     * Removes the page links container node and clears event listeners
     * @method destroy
     * @private
     */
    destroy : function () {
        YAHOO.util.Event.purgeElement(this.container,true);
        this.container.parentNode.removeChild(this.container);
        this.container = null;
    },

    /**
     * Listener for the container's onclick event.  Looks for qualifying link
     * clicks, and pulls the page number from the link's page attribute.
     * Sends link's page attribute to the Paginator's setPage method.
     * @method onClick
     * @param e {DOMEvent} The click event
     */
    onClick : function (e) {
        var t = YAHOO.util.Event.getTarget(e);
        if (t && YAHOO.util.Dom.hasClass(t,
                        this.paginator.get('pageLinkClass'))) {

            YAHOO.util.Event.stopEvent(e);

            this.paginator.setPage(parseInt(t.getAttribute('page'),10));
        }
    }

};

})();
