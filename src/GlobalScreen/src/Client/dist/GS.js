var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var il;
(function (il) {
    var Utilities;
    (function (Utilities) {
        var Hasher = (function () {
            function Hasher() {
            }
            Hasher.unhash = function (hex) {
                var bytes = [];
                for (var i = 0; i < hex.length - 1; i += 2)
                    bytes.push(parseInt(hex.substr(i, 2), 16));
                return String.fromCharCode.apply(String, bytes);
            };
            Hasher.hash = function (bin) {
                var i = 0, l = bin.length, chr, hex = '';
                for (i; i < l; ++i) {
                    chr = bin.charCodeAt(i).toString(16);
                    hex += chr.length < 2 ? '0' + chr : chr;
                }
                return hex;
            };
            return Hasher;
        }());
        Utilities.Hasher = Hasher;
        var Logger = (function () {
            function Logger() {
            }
            Logger.log = function (item) {
                if (this.debug) {
                    if (item instanceof Object) {
                        console.log("GlobalScreen: ");
                        console.log(item);
                    }
                    else {
                        var line = String(item);
                        console.log("GlobalScreen: " + line);
                    }
                }
            };
            Logger.debug = false;
            return Logger;
        }());
        Utilities.Logger = Logger;
        var Storage = (function () {
            function Storage(namespace, classloader) {
                this.items = {};
                this.namespace = namespace;
                this.classloader = classloader;
                this.read();
            }
            Storage.prototype.getItemViaLoader = function (item) {
                return this.classloader.loadFromRawData(item);
            };
            Storage.prototype.add = function (key, value) {
                this.items[key] = value;
                this.store();
            };
            Storage.prototype.remove = function (key) {
                var val = this.items[key];
                delete this.items[key];
                this.store();
                return val;
            };
            Storage.prototype.get = function (key) {
                return this.items[key];
            };
            Storage.prototype.raw = function () {
                return this.items;
            };
            Storage.prototype.values = function () {
                var values = [];
                for (var prop in this.items) {
                    if (this.items.hasOwnProperty(prop)) {
                        values.push(this.items[prop]);
                    }
                }
                return values;
            };
            Storage.prototype.exists = function (key) {
                return this.items.hasOwnProperty(key);
            };
            Storage.prototype.keys = function () {
                var keySet = [];
                for (var prop in this.items) {
                    if (this.items.hasOwnProperty(prop)) {
                        keySet.push(prop);
                    }
                }
                return keySet;
            };
            return Storage;
        }());
        var LocalStorage = (function (_super) {
            __extends(LocalStorage, _super);
            function LocalStorage() {
                return _super !== null && _super.apply(this, arguments) || this;
            }
            LocalStorage.prototype.read = function () {
                var items = JSON.parse(localStorage.getItem(this.namespace));
                var loaded_items = {};
                for (var i in items) {
                    loaded_items[i] = this.getItemViaLoader(items[i]);
                }
                this.items = loaded_items || {};
            };
            LocalStorage.prototype.store = function () {
                localStorage[this.namespace] = JSON.stringify(this.items);
            };
            return LocalStorage;
        }(Storage));
        Utilities.LocalStorage = LocalStorage;
        var CookieStorage = (function (_super) {
            __extends(CookieStorage, _super);
            function CookieStorage(namespace, classloader, ttl_in_min) {
                if (classloader === void 0) { classloader = new (function () {
                    function class_1() {
                    }
                    class_1.prototype.loadFromRawData = function (raw_data) {
                        return raw_data;
                    };
                    return class_1;
                }()); }
                if (ttl_in_min === void 0) { ttl_in_min = 5; }
                var _this = _super.call(this, namespace, classloader) || this;
                _this.ttl_in_min = 5;
                Logger.log("Creating new cookie storage " + namespace);
                _this.ttl_in_min = ttl_in_min;
                return _this;
            }
            CookieStorage.prototype.read = function () {
                var name = this.namespace + "=";
                var decodedCookie = decodeURIComponent(document.cookie);
                var ca = decodedCookie.split(';');
                this.items = {};
                for (var i = 0; i < ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0) == ' ') {
                        c = c.substring(1);
                    }
                    if (c.indexOf(name) == 0) {
                        var items = JSON.parse(c.substring(name.length, c.length));
                        for (var key in items) {
                            Logger.log("Add item from cookie " + key);
                            var raw_item = items[key];
                            Logger.log(raw_item);
                            this.add(key, raw_item);
                        }
                    }
                }
            };
            CookieStorage.prototype.store = function () {
                var d = new Date();
                d.setTime(d.getTime() + (this.ttl_in_min * 60 * 1000));
                var expires = "expires=" + d.toUTCString();
                var cookie = this.namespace + "=" + JSON.stringify(this.items) + ";" + expires + ";path=/";
                document.cookie = cookie;
            };
            return CookieStorage;
        }(Storage));
        Utilities.CookieStorage = CookieStorage;
    })(Utilities = il.Utilities || (il.Utilities = {}));
    var GS;
    (function (GS) {
        var SPLIITER = "|";
        var ClientSettings = (function () {
            function ClientSettings() {
                this.clear_states_for_levels = {
                    1: [1, 2],
                    2: [2],
                    10: [],
                };
                this.hashing = false;
                this.logging = true;
                this.store_state_for_levels = [];
            }
            return ClientSettings;
        }());
        var Provider;
        (function (Provider) {
            function getClientSideProvider(provider_name) {
                return new ClientSideProvider(provider_name);
            }
            Provider.getClientSideProvider = getClientSideProvider;
            function getServerSideProviderFromCombinedString(from_serialized_string) {
                var elements = from_serialized_string.split(SPLIITER);
                return new ServerSideProvider(elements[0]);
            }
            Provider.getServerSideProviderFromCombinedString = getServerSideProviderFromCombinedString;
            function getServerSideProvider(provider_name) {
                return new ServerSideProvider(provider_name);
            }
            Provider.getServerSideProvider = getServerSideProvider;
            var ClientSideProvider = (function () {
                function ClientSideProvider(provider_name) {
                    this.provider_name = provider_name;
                    this.is_client_side = true;
                }
                return ClientSideProvider;
            }());
            var ServerSideProvider = (function () {
                function ServerSideProvider(provider_name) {
                    this.provider_name = provider_name;
                    this.is_client_side = false;
                }
                return ServerSideProvider;
            }());
        })(Provider = GS.Provider || (GS.Provider = {}));
        var Identification;
        (function (Identification) {
            var getServerSideProvider = il.GS.Provider.getServerSideProvider;
            function getFromServerSideString(server_side_string) {
                var elements = server_side_string.split(SPLIITER);
                var provider = getServerSideProvider(elements[0]);
                return new StandardIdentification(elements[1], provider);
            }
            Identification.getFromServerSideString = getFromServerSideString;
            function get(internal_identifier, provider_string) {
                var provider = getServerSideProvider(provider_string);
                return new StandardIdentification(internal_identifier, provider);
            }
            Identification.get = get;
            var StandardIdentification = (function () {
                function StandardIdentification(internal_identifier, provider) {
                    this.internal_identifier = internal_identifier;
                    this.provider = provider;
                    this.as_string = this.provider.provider_name + "|" + this.internal_identifier;
                }
                StandardIdentification.prototype.toString = function () {
                    return this.as_string;
                };
                return StandardIdentification;
            }());
            Identification.StandardIdentification = StandardIdentification;
        })(Identification = GS.Identification || (GS.Identification = {}));
        var Client;
        (function (Client) {
            var Logger = il.Utilities.Logger;
            var Hasher = il.Utilities.Hasher;
            var LocalStorage = il.Utilities.LocalStorage;
            var CookieStorage = il.Utilities.CookieStorage;
            var settings = new ClientSettings();
            var Item = (function () {
                function Item(identification, level, ui_id) {
                    this.active = false;
                    this.identification = identification;
                    this.level = level;
                    this.ui_id = ui_id;
                }
                return Item;
            }());
            Client.Item = Item;
            function newItem(id, ui_id, level) {
                return new Item(id, level, ui_id);
            }
            Client.newItem = newItem;
            var ItemStorage = (function () {
                function ItemStorage() {
                    this.local_storage = new LocalStorage('gs_item_storage', new (function () {
                        function class_2() {
                        }
                        class_2.prototype.loadFromRawData = function (raw_data) {
                            var id = Identification.get(raw_data.identification.internal_identifier, raw_data.identification.provider.provider_name);
                            var item = Client.newItem(id, raw_data.ui_id, raw_data.level);
                            return item;
                        };
                        return class_2;
                    }()));
                    this.cookie_storage = new CookieStorage('gs_active_items');
                }
                ItemStorage.prototype.hash = function (i) {
                    if (settings.hashing === true) {
                        return Hasher.hash(i.toString());
                    }
                    else {
                        return i.toString();
                    }
                };
                ItemStorage.prototype.storeItem = function (item) {
                    this.local_storage.add(this.hash(item.identification), item);
                };
                ItemStorage.prototype.itemExists = function (id) {
                    var item = this.getItem(id);
                    return item instanceof Item;
                };
                ItemStorage.prototype.getItem = function (id) {
                    return this.local_storage.values().find(function (item) {
                        if (item.identification.toString() === id.toString()) {
                            return item;
                        }
                    });
                };
                ItemStorage.prototype.itemExistsByUIID = function (ui_id) {
                    return (this.getItemByUUID(ui_id) !== undefined);
                };
                ItemStorage.prototype.getItemByUUID = function (ui_id) {
                    return this.local_storage.values().find(function (item) {
                        return (item.ui_id === ui_id);
                    });
                };
                ItemStorage.prototype.removeItem = function (item) {
                    this.local_storage.remove(this.hash(item.identification));
                    this.cookie_storage.remove(this.hash(item.identification));
                };
                ItemStorage.prototype.findItemWhichMustBeClosed = function (current_item) {
                    var levels_to_close = [0];
                    if (current_item.level in settings.clear_states_for_levels) {
                        levels_to_close = settings.clear_states_for_levels[current_item.level];
                        Logger.log("Levels to close " + levels_to_close.toString());
                    }
                    return this.local_storage.values().filter(function (item) {
                        return (levels_to_close.indexOf(item.level) > -1);
                    });
                };
                ItemStorage.prototype.activateItem = function (item) {
                    if (settings.store_state_for_levels.indexOf(item.level) > -1) {
                        item.active = true;
                        this.storeItem(item);
                        this.cookie_storage.add(this.hash(item.identification), true);
                    }
                };
                ItemStorage.prototype.deactivateItem = function (item) {
                    item.active = false;
                    this.storeItem(item);
                    this.cookie_storage.remove(this.hash(item.identification));
                };
                ItemStorage.prototype.handleTriggeredItem = function (triggered_item) {
                    Logger.log("Handle Item");
                    Logger.log(triggered_item);
                    if (triggered_item.active === true) {
                        Logger.log("Deactivate Item");
                        this.deactivateItem(triggered_item);
                    }
                    else {
                        Logger.log("Activate Item");
                        triggered_item.active = true;
                        this.activateItem(triggered_item);
                        var items_to_close = this.findItemWhichMustBeClosed(triggered_item);
                        for (var i in items_to_close) {
                            var item = items_to_close[i];
                            if (this.hash(item.identification) !== this.hash(triggered_item.identification)) {
                                Logger.log("Deactivating subsequent Item " + item.identification.toString());
                                this.deactivateItem(item);
                            }
                        }
                    }
                };
                return ItemStorage;
            }());
            var item_storage = new ItemStorage();
            function register(id, ui_id, level) {
                if (!item_storage.itemExists(id)) {
                    Logger.log("Item not found, registering " + id.toString());
                    item_storage.storeItem(this.newItem(id, ui_id, level));
                }
                else {
                    var item = item_storage.getItem(id);
                    item.ui_id = ui_id;
                    item_storage.storeItem(item);
                }
            }
            Client.register = register;
            function trigger(ui_id) {
                if (item_storage.itemExistsByUIID(ui_id)) {
                    var item = item_storage.getItemByUUID(ui_id);
                    item_storage.handleTriggeredItem(item);
                }
                else {
                    Logger.log("Item not found");
                }
            }
            Client.trigger = trigger;
            function init(json) {
                json = JSON.parse(json);
                Object.assign(settings, json);
                Logger.log(settings);
                Logger.debug = settings.logging;
            }
            Client.init = init;
        })(Client = GS.Client || (GS.Client = {}));
    })(GS = il.GS || (il.GS = {}));
})(il || (il = {}));
