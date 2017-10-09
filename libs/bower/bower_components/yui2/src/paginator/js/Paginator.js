(function () {
/**
 * The Paginator widget provides a set of controls to navigate through paged
 * data.
 *
 * @module paginator
 * @uses YAHOO.util.EventProvider
 * @uses YAHOO.util.AttributeProvider
 */

var Dom        = YAHOO.util.Dom,
    lang       = YAHOO.lang,
    isObject   = lang.isObject,
    isFunction = lang.isFunction,
    isArray    = lang.isArray,
    isString   = lang.isString;

/**
 * Instantiate a Paginator, passing a configuration object to the contructor.
 * The configuration object should contain the following properties:
 * <ul>
 *   <li>rowsPerPage : <em>n</em> (int)</li>
 *   <li>totalRecords : <em>n</em> (int or Paginator.VALUE_UNLIMITED)</li>
 *   <li>containers : <em>id | el | arr</em> (HTMLElement reference, its id, or an array of either)</li>
 * </ul>
 *
 * @namespace YAHOO.widget
 * @class Paginator
 * @constructor
 * @param config {Object} Object literal to set instance and ui component
 * configuration.
 */
function Paginator(config) {
    var UNLIMITED = Paginator.VALUE_UNLIMITED,
        attrib, initialPage, records, perPage, startIndex;

    config = isObject(config) ? config : {};

    this.initConfig();

    this.initEvents();

    // Set the basic config keys first
    this.set('rowsPerPage',config.rowsPerPage,true);
    if (Paginator.isNumeric(config.totalRecords)) {
        this.set('totalRecords',config.totalRecords,true);
    }
    
    this.initUIComponents();

    // Update the other config values
    for (attrib in config) {
        if (config.hasOwnProperty(attrib)) {
            this.set(attrib,config[attrib],true);
        }
    }

    // Calculate the initial record offset
    initialPage = this.get('initialPage');
    records     = this.get('totalRecords');
    perPage     = this.get('rowsPerPage');
    if (initialPage > 1 && perPage !== UNLIMITED) {
        startIndex = (initialPage - 1) * perPage;
        if (records === UNLIMITED || startIndex < records) {
            this.set('recordOffset',startIndex,true);
        }
    }
}


// Static members
lang.augmentObject(Paginator, {
    /**
     * Incrementing index used to give instances unique ids.
     * @static
     * @property Paginator.id
     * @type number
     * @private
     */
    id : 0,

    /**
     * Base of id strings used for ui components.
     * @static
     * @property Paginator.ID_BASE
     * @type string
     * @private
     */
    ID_BASE : 'yui-pg',

    /**
     * Used to identify unset, optional configurations, or used explicitly in
     * the case of totalRecords to indicate unlimited pagination.
     * @static
     * @property Paginator.VALUE_UNLIMITED
     * @type number
     * @final
     */
    VALUE_UNLIMITED : -1,

    /**
     * Default template used by Paginator instances.  Update this if you want
     * all new Paginators to use a different default template.
     * @static
     * @property Paginator.TEMPLATE_DEFAULT
     * @type string
     */
    TEMPLATE_DEFAULT : "{FirstPageLink} {PreviousPageLink} {PageLinks} {NextPageLink} {LastPageLink}",

    /**
     * Common alternate pagination format, including page links, links for
     * previous, next, first and last pages as well as a rows-per-page
     * dropdown.  Offered as a convenience.
     * @static
     * @property Paginator.TEMPLATE_ROWS_PER_PAGE
     * @type string
     */
    TEMPLATE_ROWS_PER_PAGE : "{FirstPageLink} {PreviousPageLink} {PageLinks} {NextPageLink} {LastPageLink} {RowsPerPageDropdown}",

    /**
     * Storage object for UI Components
     * @static
     * @property Paginator.ui
     */
    ui : {},

    /**
     * Similar to YAHOO.lang.isNumber, but allows numeric strings.  This is
     * is used for attribute validation in conjunction with getters that return
     * numbers.
     *
     * @method Paginator.isNumeric
     * @param v {Number|String} value to be checked for number or numeric string
     * @returns {Boolean} true if the input is coercable into a finite number
     * @static
     */
    isNumeric : function (v) {
        return isFinite(+v);
    },

    /**
     * Return a number or null from input
     *
     * @method Paginator.toNumber
     * @param n {Number|String} a number or numeric string
     * @return Number
     * @static
     */
    toNumber : function (n) {
        return isFinite(+n) ? +n : null;
    }

},true);


// Instance members and methods
Paginator.prototype = {

    // Instance members

    /**
     * Array of nodes in which to render pagination controls.  This is set via
     * the &quot;containers&quot; attribute.
     * @property _containers
     * @type Array(HTMLElement)
     * @private
     */
    _containers : [],

    /**
     * Flag used to indicate multiple attributes are being updated via setState
     * @property _batch
     * @type boolean
     * @protected
     */
    _batch : false,

    /**
     * Used by setState to indicate when a page change has occurred
     * @property _pageChanged
     * @type boolean
     * @protected
     */
    _pageChanged : false,

    /**
     * Temporary state cache used by setState to keep track of the previous
     * state for eventual pageChange event firing
     * @property _state
     * @type Object
     * @protected
     */
    _state : null,


    // Instance methods

    /**
     * Initialize the Paginator's attributes (see YAHOO.util.Element class
     * AttributeProvider).
     * @method initConfig
     * @private
     */
    initConfig : function () {

        var UNLIMITED = Paginator.VALUE_UNLIMITED;

        /**
         * REQUIRED. Number of records constituting a &quot;page&quot;
         * @attribute rowsPerPage
         * @type integer
         */
        this.setAttributeConfig('rowsPerPage', {
            value     : 0,
            validator : Paginator.isNumeric,
            setter    : Paginator.toNumber
        });

        /**
         * REQUIRED. Node references or ids of nodes in which to render the
         * pagination controls.
         * @attribute containers
         * @type {string|HTMLElement|Array(string|HTMLElement)}
         */
        this.setAttributeConfig('containers', {
            value     : null,
            validator : function (val) {
                if (!isArray(val)) {
                    val = [val];
                }
                for (var i = 0, len = val.length; i < len; ++i) {
                    if (isString(val[i]) || 
                        (isObject(val[i]) && val[i].nodeType === 1)) {
                        continue;
                    }
                    return false;
                }
                return true;
            },
            method : function (val) {
                val = Dom.get(val);
                if (!isArray(val)) {
                    val = [val];
                }
                this._containers = val;
            }
        });

        /**
         * Total number of records to paginate through
         * @attribute totalRecords
         * @type integer
         * @default 0
         */
        this.setAttributeConfig('totalRecords', {
            value     : 0,
            validator : Paginator.isNumeric,
            setter    : Paginator.toNumber
        });

        /**
         * Zero based index of the record considered first on the current page.
         * For page based interactions, don't modify this attribute directly;
         * use setPage(n).
         * @attribute recordOffset
         * @type integer
         * @default 0
         */
        this.setAttributeConfig('recordOffset', {
            value     : 0,
            validator : function (val) {
                var total = this.get('totalRecords');
                if (Paginator.isNumeric(val)) {
                    val = +val;
                    return total === UNLIMITED || total > val ||
                           (total === 0 && val === 0);
                }

                return false;
            },
            setter    : Paginator.toNumber
        });

        /**
         * Page to display on initial paint
         * @attribute initialPage
         * @type integer
         * @default 1
         */
        this.setAttributeConfig('initialPage', {
            value     : 1,
            validator : Paginator.isNumeric,
            setter    : Paginator.toNumber
        });

        /**
         * Template used to render controls.  The string will be used as
         * innerHTML on all specified container nodes.  Bracketed keys
         * (e.g. {pageLinks}) in the string will be replaced with an instance
         * of the so named ui component.
         * @see Paginator.TEMPLATE_DEFAULT
         * @see Paginator.TEMPLATE_ROWS_PER_PAGE
         * @attribute template
         * @type string
         */
        this.setAttributeConfig('template', {
            value : Paginator.TEMPLATE_DEFAULT,
            validator : isString
        });

        /**
         * Class assigned to the element(s) containing pagination controls.
         * @attribute containerClass
         * @type string
         * @default 'yui-pg-container'
         */
        this.setAttributeConfig('containerClass', {
            value : 'yui-pg-container',
            validator : isString
        });

        /**
         * Display pagination controls even when there is only one page.  Set
         * to false to forgo rendering and/or hide the containers when there
         * is only one page of data.  Note if you are using the rowsPerPage
         * dropdown ui component, visibility will be maintained as long as the
         * number of records exceeds the smallest page size.
         * @attribute alwaysVisible
         * @type boolean
         * @default true
         */
        this.setAttributeConfig('alwaysVisible', {
            value : true,
            validator : lang.isBoolean
        });

        /**
         * Update the UI immediately upon interaction.  If false, changeRequest
         * subscribers or other external code will need to explicitly set the
         * new values in the paginator to trigger repaint.
         * @attribute updateOnChange
         * @type boolean
         * @default false
         * @deprecated use changeRequest listener that calls setState
         */
        this.setAttributeConfig('updateOnChange', {
            value     : false,
            validator : lang.isBoolean
        });



        // Read only attributes

        /**
         * Unique id assigned to this instance
         * @attribute id
         * @type integer
         * @final
         */
        this.setAttributeConfig('id', {
            value    : Paginator.id++,
            readOnly : true
        });

        /**
         * Indicator of whether the DOM nodes have been initially created
         * @attribute rendered
         * @type boolean
         * @final
         */
        this.setAttributeConfig('rendered', {
            value    : false,
            readOnly : true
        });

    },

    /**
     * Initialize registered ui components onto this instance.
     * @method initUIComponents
     * @private
     */
    initUIComponents : function () {
        var ui = Paginator.ui,
            name,UIComp;
        for (name in ui) {
            if (ui.hasOwnProperty(name)) {
                UIComp = ui[name];
                if (isObject(UIComp) && isFunction(UIComp.init)) {
                    UIComp.init(this);
                }
            }
        }
    },

    /**
     * Initialize this instance's CustomEvents.
     * @method initEvents
     * @private
     */
    initEvents : function () {
        /**
         * Event fired when the Paginator is initially rendered
         * @event render
         */
        this.createEvent('render');

        /**
         * Event fired when the Paginator is initially rendered
         * @event rendered
         * @deprecated use render event
         */
        this.createEvent('rendered'); // backward compatibility

        /**
         * Event fired when a change in pagination values is requested,
         * either by interacting with the various ui components or via the
         * setStartIndex(n) etc APIs.
         * Subscribers will receive the proposed state as the first parameter.
         * The proposed state object will contain the following keys:
         * <ul>
         *   <li>paginator - the Paginator instance</li>
         *   <li>page</li>
         *   <li>totalRecords</li>
         *   <li>recordOffset - index of the first record on the new page</li>
         *   <li>rowsPerPage</li>
         *   <li>records - array containing [start index, end index] for the records on the new page</li>
         *   <li>before - object literal with all these keys for the current state</li>
         * </ul>
         * @event changeRequest
         */
        this.createEvent('changeRequest');

        /**
         * Event fired when attribute changes have resulted in the calculated
         * current page changing.
         * @event pageChange
         */
        this.createEvent('pageChange');

        /**
         * Event that fires before the destroy event.
         * @event beforeDestroy
         */
        this.createEvent('beforeDestroy');

        /**
         * Event used to trigger cleanup of ui components
         * @event destroy
         */
        this.createEvent('destroy');

        this._selfSubscribe();
    },

    /**
     * Subscribes to instance attribute change events to automate certain
     * behaviors.
     * @method _selfSubscribe
     * @protected
     */
    _selfSubscribe : function () {
        // Listen for changes to totalRecords and alwaysVisible 
        this.subscribe('totalRecordsChange',this.updateVisibility,this,true);
        this.subscribe('alwaysVisibleChange',this.updateVisibility,this,true);

        // Fire the pageChange event when appropriate
        this.subscribe('totalRecordsChange',this._handleStateChange,this,true);
        this.subscribe('recordOffsetChange',this._handleStateChange,this,true);
        this.subscribe('rowsPerPageChange',this._handleStateChange,this,true);

        // Update recordOffset when totalRecords is reduced below
        this.subscribe('totalRecordsChange',this._syncRecordOffset,this,true);
    },

    /**
     * Sets recordOffset to the starting index of the previous page when
     * totalRecords is reduced below the current recordOffset.
     * @method _syncRecordOffset
     * @param e {Event} totalRecordsChange event
     * @protected
     */
    _syncRecordOffset : function (e) {
        var v = e.newValue,rpp,state;
        if (e.prevValue !== v) {
            if (v !== Paginator.VALUE_UNLIMITED) {
                rpp = this.get('rowsPerPage');

                if (rpp && this.get('recordOffset') >= v) {
                    state = this.getState({
                        totalRecords : e.prevValue,
                        recordOffset : this.get('recordOffset')
                    });

                    this.set('recordOffset', state.before.recordOffset);
                    this._firePageChange(state);
                }
            }
        }
    },

    /**
     * Fires the pageChange event when the state attributes have changed in
     * such a way as to locate the current recordOffset on a new page.
     * @method _handleStateChange
     * @param e {Event} the attribute change event
     * @protected
     */
    _handleStateChange : function (e) {
        if (e.prevValue !== e.newValue) {
            var change = this._state || {},
                state;

            change[e.type.replace(/Change$/,'')] = e.prevValue;
            state = this.getState(change);

            if (state.page !== state.before.page) {
                if (this._batch) {
                    this._pageChanged = true;
                } else {
                    this._firePageChange(state);
                }
            }
        }
    },

    /**
     * Fires a pageChange event in the form of a standard attribute change
     * event with additional properties prevState and newState.
     * @method _firePageChange
     * @param state {Object} the result of getState(oldState)
     * @protected
     */
    _firePageChange : function (state) {
        if (isObject(state)) {
            var current = state.before;
            delete state.before;
            this.fireEvent('pageChange',{
                type      : 'pageChange',
                prevValue : state.page,
                newValue  : current.page,
                prevState : state,
                newState  : current
            });
        }
    },

    /**
     * Render the pagination controls per the format attribute into the
     * specified container nodes.
     * @method render
     * @return the Paginator instance
     * @chainable
     */
    render : function () {
        if (this.get('rendered')) {
            return this;
        }

        var template = this.get('template'),
            state    = this.getState(),
            // ex. yui-pg0-1 (first paginator, second container)
            id_base  = Paginator.ID_BASE + this.get('id') + '-',
            i, len;

        // Assemble the containers, keeping them hidden
        for (i = 0, len = this._containers.length; i < len; ++i) {
            this._renderTemplate(this._containers[i],template,id_base+i,true);
        }

        // Show the containers if appropriate
        this.updateVisibility();

        // Set render attribute manually to support its readOnly contract
        if (this._containers.length) {
            this.setAttributeConfig('rendered', { value: true });

            this.fireEvent('render', state);
            // For backward compatibility
            this.fireEvent('rendered', state);
        }

        return this;
    },

    /**
     * Creates the individual ui components and renders them into a container.
     *
     * @method _renderTemplate
     * @param container {HTMLElement} where to add the ui components
     * @param template {String} the template to use as a guide for rendering
     * @param id_base {String} id base for the container's ui components
     * @param hide {Boolean} leave the container hidden after assembly
     * @protected
     */
    _renderTemplate : function (container, template, id_base, hide) {
        var containerClass = this.get('containerClass'),
            markers, i, len;

        if (!container) {
            return;
        }

        // Hide the container while its contents are rendered
        Dom.setStyle(container,'display','none');

        Dom.addClass(container, containerClass);

        // Place the template innerHTML, adding marker spans to the template
        // html to indicate drop zones for ui components
        container.innerHTML = template.replace(/\{([a-z0-9_ \-]+)\}/gi,
            '<span class="yui-pg-ui yui-pg-ui-$1"></span>');

        // Replace each marker with the ui component's render() output
        markers = Dom.getElementsByClassName('yui-pg-ui','span',container);

        for (i = 0, len = markers.length; i < len; ++i) {
            this.renderUIComponent(markers[i], id_base);
        }

        if (!hide) {
            // Show the container allowing page reflow
            Dom.setStyle(container,'display','');
        }
    },

    /**
     * Replaces a marker node with a rendered UI component, determined by the
     * yui-pg-ui-(UI component class name) in the marker's className. e.g.
     * yui-pg-ui-PageLinks => new YAHOO.widget.Paginator.ui.PageLinks(this)
     *
     * @method renderUIComponent
     * @param marker {HTMLElement} the marker node to replace
     * @param id_base {String} string base the component's generated id
     * @return the Paginator instance
     * @chainable
     */
    renderUIComponent : function (marker, id_base) {
        var par    = marker.parentNode,
            name   = /yui-pg-ui-(\w+)/.exec(marker.className),
            UIComp = name && Paginator.ui[name[1]],
            comp;

        if (isFunction(UIComp)) {
            comp = new UIComp(this);
            if (isFunction(comp.render)) {
                par.replaceChild(comp.render(id_base),marker);
            }
        }

        return this;
    },

    /**
     * Removes controls from the page and unhooks events.
     * @method destroy
     */
    destroy : function () {
        this.fireEvent('beforeDestroy');
        this.fireEvent('destroy');

        this.setAttributeConfig('rendered',{value:false});
        this.unsubscribeAll();
    },

    /**
     * Hides the containers if there is only one page of data and attribute
     * alwaysVisible is false.  Conversely, it displays the containers if either
     * there is more than one page worth of data or alwaysVisible is turned on.
     * @method updateVisibility
     */
    updateVisibility : function (e) {
        var alwaysVisible = this.get('alwaysVisible'),
            totalRecords, visible, rpp, rppOptions, i, len, opt;

        if (!e || e.type === 'alwaysVisibleChange' || !alwaysVisible) {
            totalRecords = this.get('totalRecords');
            visible      = true;
            rpp          = this.get('rowsPerPage');
            rppOptions   = this.get('rowsPerPageOptions');

            if (isArray(rppOptions)) {
                for (i = 0, len = rppOptions.length; i < len; ++i) {
                    opt = rppOptions[i];
                    // account for value 'all'
                    if (lang.isNumber(opt || opt.value)) {
                        rpp = Math.min(rpp, (opt.value || opt));
                    }
                }
            }

            if (totalRecords !== Paginator.VALUE_UNLIMITED &&
                totalRecords <= rpp) {
                visible = false;
            }

            visible = visible || alwaysVisible;

            for (i = 0, len = this._containers.length; i < len; ++i) {
                Dom.setStyle(this._containers[i],'display',
                    visible ? '' : 'none');
            }
        }
    },




    /**
     * Get the configured container nodes
     * @method getContainerNodes
     * @return {Array} array of HTMLElement nodes
     */
    getContainerNodes : function () {
        return this._containers;
    },

    /**
     * Get the total number of pages in the data set according to the current
     * rowsPerPage and totalRecords values.  If totalRecords is not set, or
     * set to YAHOO.widget.Paginator.VALUE_UNLIMITED, returns
     * YAHOO.widget.Paginator.VALUE_UNLIMITED.
     * @method getTotalPages
     * @return {number}
     */
    getTotalPages : function () {
        var records = this.get('totalRecords'),
            perPage = this.get('rowsPerPage');

        // rowsPerPage not set.  Can't calculate
        if (!perPage) {
            return null;
        }

        if (records === Paginator.VALUE_UNLIMITED) {
            return Paginator.VALUE_UNLIMITED;
        }

        return Math.ceil(records/perPage);
    },

    /**
     * Does the requested page have any records?
     * @method hasPage
     * @param page {number} the page in question
     * @return {boolean}
     */
    hasPage : function (page) {
        if (!lang.isNumber(page) || page < 1) {
            return false;
        }

        var totalPages = this.getTotalPages();

        return (totalPages === Paginator.VALUE_UNLIMITED || totalPages >= page);
    },

    /**
     * Get the page number corresponding to the current record offset.
     * @method getCurrentPage
     * @return {number}
     */
    getCurrentPage : function () {
        var perPage = this.get('rowsPerPage');
        if (!perPage || !this.get('totalRecords')) {
            return 0;
        }
        return Math.floor(this.get('recordOffset') / perPage) + 1;
    },

    /**
     * Are there records on the next page?
     * @method hasNextPage
     * @return {boolean}
     */
    hasNextPage : function () {
        var currentPage = this.getCurrentPage(),
            totalPages  = this.getTotalPages();

        return currentPage && (totalPages === Paginator.VALUE_UNLIMITED || currentPage < totalPages);
    },

    /**
     * Get the page number of the next page, or null if the current page is the
     * last page.
     * @method getNextPage
     * @return {number}
     */
    getNextPage : function () {
        return this.hasNextPage() ? this.getCurrentPage() + 1 : null;
    },

    /**
     * Is there a page before the current page?
     * @method hasPreviousPage
     * @return {boolean}
     */
    hasPreviousPage : function () {
        return (this.getCurrentPage() > 1);
    },

    /**
     * Get the page number of the previous page, or null if the current page
     * is the first page.
     * @method getPreviousPage
     * @return {number}
     */
    getPreviousPage : function () {
        return (this.hasPreviousPage() ? this.getCurrentPage() - 1 : 1);
    },

    /**
     * Get the start and end record indexes of the specified page.
     * @method getPageRecords
     * @param page {number} (optional) The page (current page if not specified)
     * @return {Array} [start_index, end_index]
     */
    getPageRecords : function (page) {
        if (!lang.isNumber(page)) {
            page = this.getCurrentPage();
        }

        var perPage = this.get('rowsPerPage'),
            records = this.get('totalRecords'),
            start, end;

        if (!page || !perPage) {
            return null;
        }

        start = (page - 1) * perPage;
        if (records !== Paginator.VALUE_UNLIMITED) {
            if (start >= records) {
                return null;
            }
            end = Math.min(start + perPage, records) - 1;
        } else {
            end = start + perPage - 1;
        }

        return [start,end];
    },

    /**
     * Set the current page to the provided page number if possible.
     * @method setPage
     * @param newPage {number} the new page number
     * @param silent {boolean} whether to forcibly avoid firing the
     * changeRequest event
     */
    setPage : function (page,silent) {
        if (this.hasPage(page) && page !== this.getCurrentPage()) {
            if (this.get('updateOnChange') || silent) {
                this.set('recordOffset', (page - 1) * this.get('rowsPerPage'));
            } else {
                this.fireEvent('changeRequest',this.getState({'page':page}));
            }
        }
    },

    /**
     * Get the number of rows per page.
     * @method getRowsPerPage
     * @return {number} the current setting of the rowsPerPage attribute
     */
    getRowsPerPage : function () {
        return this.get('rowsPerPage');
    },

    /**
     * Set the number of rows per page.
     * @method setRowsPerPage
     * @param rpp {number} the new number of rows per page
     * @param silent {boolean} whether to forcibly avoid firing the
     * changeRequest event
     */
    setRowsPerPage : function (rpp,silent) {
        if (Paginator.isNumeric(rpp) && +rpp > 0 &&
            +rpp !== this.get('rowsPerPage')) {
            if (this.get('updateOnChange') || silent) {
                this.set('rowsPerPage',rpp);
            } else {
                this.fireEvent('changeRequest',
                    this.getState({'rowsPerPage':+rpp}));
            }
        }
    },

    /**
     * Get the total number of records.
     * @method getTotalRecords
     * @return {number} the current setting of totalRecords attribute
     */
    getTotalRecords : function () {
        return this.get('totalRecords');
    },

    /**
     * Set the total number of records.
     * @method setTotalRecords
     * @param total {number} the new total number of records
     * @param silent {boolean} whether to forcibly avoid firing the changeRequest event
     */
    setTotalRecords : function (total,silent) {
        if (Paginator.isNumeric(total) && +total >= 0 &&
            +total !== this.get('totalRecords')) {
            if (this.get('updateOnChange') || silent) {
                this.set('totalRecords',total);
            } else {
                this.fireEvent('changeRequest',
                    this.getState({'totalRecords':+total}));
            }
        }
    },

    /**
     * Get the index of the first record on the current page
     * @method getStartIndex
     * @return {number} the index of the first record on the current page
     */
    getStartIndex : function () {
        return this.get('recordOffset');
    },

    /**
     * Move the record offset to a new starting index.  This will likely cause
     * the calculated current page to change.  You should probably use setPage.
     * @method setStartIndex
     * @param offset {number} the new record offset
     * @param silent {boolean} whether to forcibly avoid firing the changeRequest event
     */
    setStartIndex : function (offset,silent) {
        if (Paginator.isNumeric(offset) && +offset >= 0 &&
            +offset !== this.get('recordOffset')) {
            if (this.get('updateOnChange') || silent) {
                this.set('recordOffset',offset);
            } else {
                this.fireEvent('changeRequest',
                    this.getState({'recordOffset':+offset}));
            }
        }
    },

    /**
     * Get an object literal describing the current state of the paginator.  If
     * an object literal of proposed values is passed, the proposed state will
     * be returned as an object literal with the following keys:
     * <ul>
     * <li>paginator - instance of the Paginator</li>
     * <li>page - number</li>
     * <li>totalRecords - number</li>
     * <li>recordOffset - number</li>
     * <li>rowsPerPage - number</li>
     * <li>records - [ start_index, end_index ]</li>
     * <li>before - (OPTIONAL) { state object literal for current state }</li>
     * </ul>
     * @method getState
     * @return {object}
     * @param changes {object} OPTIONAL object literal with proposed values
     * Supported change keys include:
     * <ul>
     * <li>rowsPerPage</li>
     * <li>totalRecords</li>
     * <li>recordOffset OR</li>
     * <li>page</li>
     * </ul>
     */
    getState : function (changes) {
        var UNLIMITED = Paginator.VALUE_UNLIMITED,
            M = Math, max = M.max, ceil = M.ceil,
            currentState, state, offset;

        function normalizeOffset(offset,total,rpp) {
            if (offset <= 0 || total === 0) {
                return 0;
            }
            if (total === UNLIMITED || total > offset) {
                return offset - (offset % rpp);
            }
            return total - (total % rpp || rpp);
        }

        currentState = {
            paginator    : this,
            totalRecords : this.get('totalRecords'),
            rowsPerPage  : this.get('rowsPerPage'),
            records      : this.getPageRecords()
        };
        currentState.recordOffset = normalizeOffset(
                                        this.get('recordOffset'),
                                        currentState.totalRecords,
                                        currentState.rowsPerPage);
        currentState.page = ceil(currentState.recordOffset /
                                 currentState.rowsPerPage) + 1;

        if (!changes) {
            return currentState;
        }

        state = {
            paginator    : this,
            before       : currentState,

            rowsPerPage  : changes.rowsPerPage || currentState.rowsPerPage,
            totalRecords : (Paginator.isNumeric(changes.totalRecords) ?
                                max(changes.totalRecords,UNLIMITED) :
                                +currentState.totalRecords)
        };

        if (state.totalRecords === 0) {
            state.recordOffset =
            state.page         = 0;
        } else {
            offset = Paginator.isNumeric(changes.page) ?
                        (changes.page - 1) * state.rowsPerPage :
                        Paginator.isNumeric(changes.recordOffset) ?
                            +changes.recordOffset :
                            currentState.recordOffset;

            state.recordOffset = normalizeOffset(offset,
                                    state.totalRecords,
                                    state.rowsPerPage);

            state.page = ceil(state.recordOffset / state.rowsPerPage) + 1;
        }

        state.records = [ state.recordOffset,
                          state.recordOffset + state.rowsPerPage - 1 ];

        // limit upper index to totalRecords - 1
        if (state.totalRecords !== UNLIMITED &&
            state.recordOffset < state.totalRecords && state.records &&
            state.records[1] > state.totalRecords - 1) {
            state.records[1] = state.totalRecords - 1;
        }

        return state;
    },

    /**
     * Convenience method to facilitate setting state attributes rowsPerPage,
     * totalRecords, recordOffset in batch.  Also supports calculating
     * recordOffset from state.page if state.recordOffset is not provided.
     * Fires only a single pageChange event, if appropriate.
     * This will not fire a changeRequest event.
     * @method setState
     * @param state {Object} Object literal of attribute:value pairs to set
     */
    setState : function (state) {
        if (isObject(state)) {
            // get flux state based on current state with before state as well
            this._state = this.getState({});

            // use just the state props from the input obj
            state = {
                page         : state.page,
                rowsPerPage  : state.rowsPerPage,
                totalRecords : state.totalRecords,
                recordOffset : state.recordOffset
            };

            // calculate recordOffset from page if recordOffset not specified.
            // not using lang.isNumber for support of numeric strings
            if (state.page && state.recordOffset === undefined) {
                state.recordOffset = (state.page - 1) *
                    (state.rowsPerPage || this.get('rowsPerPage'));
            }

            this._batch = true;
            this._pageChanged = false;

            for (var k in state) {
                if (state.hasOwnProperty(k) && this._configs.hasOwnProperty(k)) {
                    this.set(k,state[k]);
                }
            }

            this._batch = false;
            
            if (this._pageChanged) {
                this._pageChanged = false;

                this._firePageChange(this.getState(this._state));
            }
        }
    }
};

lang.augmentProto(Paginator, YAHOO.util.AttributeProvider);

YAHOO.widget.Paginator = Paginator;
})();
