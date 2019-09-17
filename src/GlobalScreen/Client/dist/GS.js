var il;
(function (il) {
    let Utilities;
    (function (Utilities) {
        class Hasher {
            static unhash(hex) {
                let bytes = [];
                for (let i = 0; i < hex.length - 1; i += 2)
                    bytes.push(parseInt(hex.substr(i, 2), 16));
                return String.fromCharCode.apply(String, bytes);
            }
            static hash(bin) {
                let i = 0, l = bin.length, chr, hex = '';
                for (i; i < l; ++i) {
                    chr = bin.charCodeAt(i).toString(16);
                    hex += chr.length < 2 ? '0' + chr : chr;
                }
                return hex;
            }
        }
        Utilities.Hasher = Hasher;
        class Logger {
            static log(item) {
                if (this.debug) {
                    if (item instanceof Object) {
                        console.log("GlobalScreen: ");
                        console.log(item);
                    }
                    else {
                        let line = String(item);
                        console.log("GlobalScreen: " + line);
                    }
                }
            }
        }
        Logger.debug = true;
        Utilities.Logger = Logger;
        class Storage {
            constructor(namespace) {
                this.items = {};
                this.namespace = namespace;
                this.read();
            }
            add(key, value) {
                this.items[key] = value;
                this.store();
            }
            remove(key) {
                let val = this.items[key];
                delete this.items[key];
                this.store();
                return val;
            }
            get(key) {
                return this.items[key];
            }
            raw() {
                return this.items;
            }
            values() {
                let values = [];
                for (let prop in this.items) {
                    if (this.items.hasOwnProperty(prop)) {
                        values.push(this.items[prop]);
                    }
                }
                return values;
            }
            exists(key) {
                return this.items.hasOwnProperty(key);
            }
            keys() {
                let keySet = [];
                for (let prop in this.items) {
                    if (this.items.hasOwnProperty(prop)) {
                        keySet.push(prop);
                    }
                }
                return keySet;
            }
        }
        class LocalStorage extends Storage {
            read() {
                let items = JSON.parse(localStorage.getItem(this.namespace));
                Logger.log("Stored Items");
                Logger.log(items);
                this.items = items || {};
                Logger.log(this.values());
            }
            store() {
                localStorage[this.namespace] = JSON.stringify(this.items);
            }
        }
        Utilities.LocalStorage = LocalStorage;
        class CookieStorage extends Storage {
            constructor(namespace, ttl_in_min = 5) {
                super(namespace);
                this.ttl_in_min = 5;
                Logger.log("Creating new cookie storage " + namespace);
                this.ttl_in_min = ttl_in_min;
            }
            read() {
                let name = this.namespace + "=";
                let decodedCookie = decodeURIComponent(document.cookie);
                let ca = decodedCookie.split(';');
                for (let i = 0; i < ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) == ' ') {
                        c = c.substring(1);
                    }
                    if (c.indexOf(name) == 0) {
                        let items = JSON.parse(c.substring(name.length, c.length));
                        for (let key in items) {
                            Logger.log("Add item from cookie " + key);
                            Logger.log(items[key]);
                            this.add(key, items[key]);
                        }
                    }
                }
                this.items = {};
            }
            store() {
                let d = new Date();
                d.setTime(d.getTime() + (this.ttl_in_min * 60 * 1000));
                let expires = "expires=" + d.toUTCString();
                let cookie = this.namespace + "=" + JSON.stringify(this.items) + ";" + expires + ";path=/";
                document.cookie = cookie;
            }
        }
        Utilities.CookieStorage = CookieStorage;
    })(Utilities = il.Utilities || (il.Utilities = {}));
    let GS;
    (function (GS) {
        const SPLIITER = "|";
        class ClientSettings {
            constructor() {
                this.clear_states_for_levels = {
                    1: [1, 2],
                    2: [1, 2],
                    10: [20],
                };
                this.hashing = true;
                this.store_state_for_levels = [];
            }
        }
        let Client;
        (function (Client) {
            var Logger = il.Utilities.Logger;
            var Hasher = il.Utilities.Hasher;
            var LocalStorage = il.Utilities.LocalStorage;
            var CookieStorage = il.Utilities.CookieStorage;
            class Item {
                constructor(identification, level, ui_id) {
                    this.active = false;
                    this.identification = identification;
                    this.level = level;
                    this.ui_id = ui_id;
                }
            }
            class ItemStorage {
                constructor() {
                    this.local_storage = new LocalStorage('gs_item_storage');
                    this.cookie_storage = new CookieStorage('gs_active_items');
                }
                hash(i) {
                    if (settings.hashing === true) {
                        return Hasher.hash(i.toString());
                    }
                    else {
                        return i.toString();
                    }
                }
                storeItem(item) {
                    this.local_storage.add(this.hash(item.identification), item);
                }
                itemExists(id) {
                    return this.getItem(id) instanceof Item;
                }
                getItem(id) {
                    return this.local_storage.values().find(function (item) {
                        return (item.identification.internal_identifier === id.internal_identifier && item.identification.provider.provider_name === id.provider.provider_name);
                    });
                }
                itemExistsByUIID(ui_id) {
                    return (this.getItemByUUID(ui_id) !== undefined);
                }
                getItemByUUID(ui_id) {
                    return this.local_storage.values().find(function (item) {
                        return (item.ui_id === ui_id);
                    });
                }
                removeItem(item) {
                    this.local_storage.remove(this.hash(item.identification));
                    this.cookie_storage.remove(this.hash(item.identification));
                }
                findItemWhichMustBeClosed(current_item) {
                    let levels_to_close = [0];
                    if (current_item.level in settings.clear_states_for_levels) {
                        levels_to_close = settings.clear_states_for_levels[current_item.level];
                    }
                    return this.local_storage.values().filter(function (item) {
                        return (levels_to_close.indexOf(item.level) > -1);
                    });
                }
                activateItem(item) {
                    if (settings.store_state_for_levels.indexOf(item.level) > -1) {
                        item.active = true;
                        this.storeItem(item);
                        this.cookie_storage.add(this.hash(item.identification), true);
                    }
                }
                deactivateItem(item) {
                    item.active = false;
                    this.storeItem(item);
                    this.cookie_storage.remove(this.hash(item.identification));
                }
                handleTriggeredItem(triggered_item) {
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
                        let items_to_close = this.findItemWhichMustBeClosed(triggered_item);
                        for (let i in items_to_close) {
                            let item = items_to_close[i];
                            if (this.hash(item.identification) !== this.hash(triggered_item.identification)) {
                                Logger.log("Deactivating subsequent Item " + item.identification.toString());
                                this.deactivateItem(item);
                            }
                        }
                    }
                }
            }
            function register(id, ui_id, level) {
                if (!item_storage.itemExists(id)) {
                    Logger.log("Item not found, registering " + id.toString());
                    let item = new Item(id, level, ui_id);
                    item_storage.storeItem(item);
                }
                else {
                    let item = item_storage.getItem(id);
                    item.ui_id = ui_id;
                    item_storage.storeItem(item);
                }
            }
            Client.register = register;
            function trigger(ui_id) {
                if (item_storage.itemExistsByUIID(ui_id)) {
                    let item = item_storage.getItemByUUID(ui_id);
                    item_storage.handleTriggeredItem(item);
                }
                else {
                    Logger.log("Item not found");
                }
            }
            Client.trigger = trigger;
            function init(json) {
                json = JSON.parse(json);
                let new_settings = Object.assign(settings, json);
                Logger.log(settings);
                Logger.log(json);
                Logger.log(new_settings);
            }
            Client.init = init;
            let settings = new ClientSettings();
            let item_storage = new ItemStorage();
        })(Client = GS.Client || (GS.Client = {}));
        let Provider;
        (function (Provider) {
            function getClientSideProvider(provider_name) {
                return new ClientSideProvider(provider_name);
            }
            Provider.getClientSideProvider = getClientSideProvider;
            function getServerSideProvider(from_serialized_string) {
                let elements = from_serialized_string.split(SPLIITER);
                return new ServerSideProvider(elements[0]);
            }
            Provider.getServerSideProvider = getServerSideProvider;
            class ClientSideProvider {
                constructor(provider_name) {
                    this.provider_name = provider_name;
                    this.is_client_side = true;
                }
            }
            class ServerSideProvider {
                constructor(provider_name) {
                    this.provider_name = provider_name;
                    this.is_client_side = false;
                }
            }
        })(Provider = GS.Provider || (GS.Provider = {}));
        let Identification;
        (function (Identification) {
            var getServerSideProvider = il.GS.Provider.getServerSideProvider;
            function getFromServerSideString(server_side_string) {
                let provider = getServerSideProvider(server_side_string);
                let elements = server_side_string.split(SPLIITER);
                return new StandardIdentification(elements[1], provider);
            }
            Identification.getFromServerSideString = getFromServerSideString;
            function getFromClientSideString(client_side_string, provider) {
                return new StandardIdentification(client_side_string, provider);
            }
            Identification.getFromClientSideString = getFromClientSideString;
            class StandardIdentification {
                constructor(internal_identifier, provider) {
                    this.internal_identifier = internal_identifier;
                    this.provider = provider;
                }
                toString() {
                    return this.provider.provider_name + "|" + this.internal_identifier;
                }
            }
            Identification.StandardIdentification = StandardIdentification;
        })(Identification = GS.Identification || (GS.Identification = {}));
        let Collector;
        (function (Collector) {
            class CollectorFactory {
                notifications() {
                    return new NotificationCollector();
                }
            }
            Collector.CollectorFactory = CollectorFactory;
            class NotificationCollector {
                constructor() {
                    this.dtos = [];
                }
                getAll() {
                    return this.dtos;
                }
                push(dto) {
                    this.dtos.push(dto);
                }
            }
        })(Collector = GS.Collector || (GS.Collector = {}));
        let DTO;
        (function (DTO) {
            let Notifications;
            (function (Notifications) {
                var Logger = il.Utilities.Logger;
                class OnScreenNotificationDTO {
                    constructor(identification) {
                        this.identification = identification;
                        this.date = new Date();
                    }
                }
                Notifications.OnScreenNotificationDTO = OnScreenNotificationDTO;
                class NotificationDTOFactory {
                    onScreen(identification) {
                        Logger.log('want to have an OnScreenNotificationDTO for identification ' + identification.toString());
                        return new OnScreenNotificationDTO(identification);
                    }
                }
                Notifications.NotificationDTOFactory = NotificationDTOFactory;
            })(Notifications = DTO.Notifications || (DTO.Notifications = {}));
            var NotificationDTOFactory = il.GS.DTO.Notifications.NotificationDTOFactory;
            class DTOFactory {
                notifications() {
                    return new NotificationDTOFactory();
                }
            }
            DTO.DTOFactory = DTOFactory;
        })(DTO = GS.DTO || (GS.DTO = {}));
    })(GS = il.GS || (il.GS = {}));
})(il || (il = {}));
