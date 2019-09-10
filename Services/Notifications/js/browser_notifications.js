(function (root, scope, factory) {
    scope.BrowserNotifications = factory(root, root.jQuery);
}(window, il, function init(root, $) {
    "use strict";

    const
        PERMISSION_GRANTED = "granted",
        PERMISSION_DEFAULT = "default",
        PERMISSION_DENIED = "denied";

    class BrowserNotification {
        /**
         *
         * @param {string} title
         * @param {Object} options
         */
        constructor(title, options = {}) {
            if (typeof title !== "string") {
                throw new Error('First argument (title) must be a string.');
            }

            if (typeof options !== "object") {
                throw new Error('Second argument (options) must be an object.');
            }

            const {
                onShow = null,
                onClose = null,
                onClick = null,
                onError = null,
                closeOnClick = false,
                timeout = null,
                ...rest
            } = options;

            this.title = title;
            this.options = rest;
            this.closeOnClick = closeOnClick;
            this.timeout = timeout;

            if ($.isFunction(onShow)) {
                this.onShow = onShow;
            }

            if ($.isFunction(onClick)) {
                this.onClick = onClick;
            }

            if ($.isFunction(onClose)) {
                this.onClose = onClose;
            }

            if ($.isFunction(onError)) {
                this.onError = onError;
            }
        }

        /**
         *
         */
        show() {
            this.n = new root.Notification(this.title, this.options);
            this.n.addEventListener('show', this, false);
            this.n.addEventListener('error', this, false);
            this.n.addEventListener('close', this, false);
            this.n.addEventListener('click', this, false);
        }

        /**
         *
         * @param e
         */
        onShowNotification(e) {
            if (this.onShow) {
                this.onShow(e);
            }

            if (!this.options.requireInteraction && this.timeout && !isNaN(this.timeout)) {
                root.setTimeout(this.n.close.bind(this.n), this.timeout * 1000);
            }
        };

        /**
         *
         * @param e
         */
        onCloseNotification(e) {
            if (this.onClose) {
                this.onClose(e);
            }
            this.destroy();
        };

        /**
         *
         * @param e
         */
        onClickNotification(e) {
            if (this.onClick) {
                this.onClick(e);
            }

            if (this.closeOnClick) {
                this.close();
            }
        };

        /**
         *
         * @param e
         */
        onErrorNotification (e) {
            if (this.onError) {
                this.onError(e);
            }
            this.destroy();
        };

        /**
         *
         */
        destroy() {
            this.n.removeEventListener('show', this, false);
            this.n.removeEventListener('error', this, false);
            this.n.removeEventListener('close', this, false);
            this.n.removeEventListener('click', this, false);
        };

        /**
         *
         */
        close() {
            this.n.close();
        };

        /**
         *
         * @param e
         */
        handleEvent(e) {
            switch (e.type) {
                case 'show':
                    this.onShowNotification(e);
                    break;

                case 'close':
                    this.onCloseNotification(e);
                    break;

                case 'click':
                    this.onClickNotification(e);
                    break;

                case 'error':
                    this.onErrorNotification(e);
                    break;
            }
        };
    }

    let methods = {};

    /**
     *
     * @returns {boolean}
     */
    methods.isSupported = function() {
        if (root.location.protocol !== "https:") {
            return false;
        }

        if (!root.Notification || !root.Notification.requestPermission) {
            return false;
        }

        return true;
    };

    /**
     *
     * @returns {boolean}
     */
    methods.isBlocked = function() {
        return (
            root.Notification &&
            root.Notification.permission &&
            root.Notification.permission === PERMISSION_DENIED
        );
    };

    /**
     *
     * @returns {boolean}
     */
    methods.isGranted = function() {
        return !methods.needsPermission();
    };

    /**
     *
     * @returns {boolean}
     */
    methods.needsPermission = function() {
        return !(
            root.Notification &&
            root.Notification.permission &&
            root.Notification.permission === PERMISSION_GRANTED
        );
    };

    /**
     *
     * @param {string} title
     * @param {Object} options
     * @returns {*}
     */
    methods.notification = function(title, options) {
        return new BrowserNotification(title, options);
    };

    /**
     *
     * @returns {Promise<any>}
     */
    methods.requestPermission = function() {
        return new Promise(function(resolve, reject) {
            setTimeout(() => {
                const pc = (permission) => {
                    switch (permission) {
                        case PERMISSION_GRANTED:
                            resolve(permission);
                            break;

                        case PERMISSION_DEFAULT:
                        case PERMISSION_DENIED:
                        default:
                            reject(permission);
                            break;
                    }
                };

                let np = root.Notification.requestPermission(pc);
                // This stunt is necessary because of old Safari browsers
                if (np && typeof np.then === "function") {
                    np.then(pc).catch(() => reject());
                }
            }, 0);
        });
    };

    return methods;
}));