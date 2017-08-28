/**
 * Provides Attribute configurations.
 * @namespace YAHOO.util
 * @class Attribute
 * @constructor
 * @param hash {Object} The intial Attribute.
 * @param {YAHOO.util.AttributeProvider} The owner of the Attribute instance.
 */

YAHOO.util.Attribute = function(hash, owner) {
    if (owner) { 
        this.owner = owner;
        this.configure(hash, true);
    }
};

YAHOO.util.Attribute.INVALID_VALUE = {};

YAHOO.util.Attribute.prototype = {
    /**
     * The name of the attribute.
     * @property name
     * @type String
     */
    name: undefined,
    
    /**
     * The value of the attribute.
     * @property value
     * @type String
     */
    value: null,
    
    /**
     * The owner of the attribute.
     * @property owner
     * @type YAHOO.util.AttributeProvider
     */
    owner: null,
    
    /**
     * Whether or not the attribute is read only.
     * @property readOnly
     * @type Boolean
     */
    readOnly: false,
    
    /**
     * Whether or not the attribute can only be written once.
     * @property writeOnce
     * @type Boolean
     */
    writeOnce: false,

    /**
     * The attribute's initial configuration.
     * @private
     * @property _initialConfig
     * @type Object
     */
    _initialConfig: null,
    
    /**
     * Whether or not the attribute's value has been set.
     * @private
     * @property _written
     * @type Boolean
     */
    _written: false,
    
    /**
     * A function to call when setting the attribute's value.
     * The method receives the new value as the first arg and the attribute name as the 2nd
     * @property method
     * @type Function
     */
    method: null,
    
    /**
     * The function to use when setting the attribute's value.
     * The setter receives the new value as the first arg and the attribute name as the 2nd
     * The return value of the setter replaces the value passed to set(). 
     * @property setter
     * @type Function
     */
    setter: null,
    
    /**
     * The function to use when getting the attribute's value.
     * The getter receives the new value as the first arg and the attribute name as the 2nd
     * The return value of the getter will be used as the return from get().
     * @property getter
     * @type Function
     */
    getter: null,

    /**
     * The validator to use when setting the attribute's value.
     * @property validator
     * @type Function
     * @return Boolean
     */
    validator: null,
    
    /**
     * Retrieves the current value of the attribute.
     * @method getValue
     * @return {any} The current value of the attribute.
     */
    getValue: function() {
        var val = this.value;

        if (this.getter) {
            val = this.getter.call(this.owner, this.name, val);
        }

        return val;
    },
    
    /**
     * Sets the value of the attribute and fires beforeChange and change events.
     * @method setValue
     * @param {Any} value The value to apply to the attribute.
     * @param {Boolean} silent If true the change events will not be fired.
     * @return {Boolean} Whether or not the value was set.
     */
    setValue: function(value, silent) {
        var beforeRetVal,
            owner = this.owner,
            name = this.name,
            invalidValue = YAHOO.util.Attribute.INVALID_VALUE,
        
            event = {
                type: name, 
                prevValue: this.getValue(),
                newValue: value
        };
        
        if (this.readOnly || ( this.writeOnce && this._written) ) {
            YAHOO.log( 'setValue ' + name + ', ' +  value +
                    ' failed: read only', 'error', 'Attribute');
            return false; // write not allowed
        }
        
        if (this.validator && !this.validator.call(owner, value) ) {
            YAHOO.log( 'setValue ' + name + ', ' + value +
                    ' validation failed', 'error', 'Attribute');
            return false; // invalid value
        }

        if (!silent) {
            beforeRetVal = owner.fireBeforeChangeEvent(event);
            if (beforeRetVal === false) {
                YAHOO.log('setValue ' + name + 
                        ' cancelled by beforeChange event', 'info', 'Attribute');
                return false;
            }
        }

        if (this.setter) {
            value = this.setter.call(owner, value, this.name);
            if (value === undefined) {
                YAHOO.log('setter for ' + this.name + ' returned undefined', 'warn', 'Attribute');
            }

            if (value === invalidValue) {
                return false;
            }
        }
        
        if (this.method) {
            if (this.method.call(owner, value, this.name) === invalidValue) {
                return false; 
            }
        }
        
        this.value = value; // TODO: set before calling setter/method?
        this._written = true;
        
        event.type = name;
        
        if (!silent) {
            this.owner.fireChangeEvent(event);
        }
        
        return true;
    },
    
    /**
     * Allows for configuring the Attribute's properties.
     * @method configure
     * @param {Object} map A key-value map of Attribute properties.
     * @param {Boolean} init Whether or not this should become the initial config.
     */
    configure: function(map, init) {
        map = map || {};

        if (init) {
            this._written = false; // reset writeOnce
        }

        this._initialConfig = this._initialConfig || {};
        
        for (var key in map) {
            if ( map.hasOwnProperty(key) ) {
                this[key] = map[key];
                if (init) {
                    this._initialConfig[key] = map[key];
                }
            }
        }
    },
    
    /**
     * Resets the value to the initial config value.
     * @method resetValue
     * @return {Boolean} Whether or not the value was set.
     */
    resetValue: function() {
        return this.setValue(this._initialConfig.value);
    },
    
    /**
     * Resets the attribute config to the initial config state.
     * @method resetConfig
     */
    resetConfig: function() {
        this.configure(this._initialConfig, true);
    },
    
    /**
     * Resets the value to the current value.
     * Useful when values may have gotten out of sync with actual properties.
     * @method refresh
     * @return {Boolean} Whether or not the value was set.
     */
    refresh: function(silent) {
        this.setValue(this.value, silent);
    }
};

