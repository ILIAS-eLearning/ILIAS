/****************************************************************************/
/****************************************************************************/
/****************************************************************************/

/**
 * The LogMsg class defines a single log message.
 *
 * @class LogMsg
 * @constructor
 * @param oConfigs {Object} Object literal of configuration params.
 */
YAHOO.widget.LogMsg = function(oConfigs) {
    // Parse configs
    /**
     * Log message.
     *
     * @property msg
     * @type String
     */
    this.msg =
    /**
     * Log timestamp.
     *
     * @property time
     * @type Date
     */
    this.time =

    /**
     * Log category.
     *
     * @property category
     * @type String
     */
    this.category =

    /**
     * Log source. The first word passed in as the source argument.
     *
     * @property source
     * @type String
     */
    this.source =

    /**
     * Log source detail. The remainder of the string passed in as the source argument, not
     * including the first word (if any).
     *
     * @property sourceDetail
     * @type String
     */
    this.sourceDetail = null;

    if (oConfigs && (oConfigs.constructor == Object)) {
        for(var param in oConfigs) {
            if (oConfigs.hasOwnProperty(param)) {
                this[param] = oConfigs[param];
            }
        }
    }
};
