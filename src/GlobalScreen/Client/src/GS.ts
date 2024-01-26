namespace il {
    /**
     * Utilities
     */
    export namespace Utilities {

        export class Hasher {
            public static unhash(hex: string) {
                let bytes = [];

                for (let i = 0; i < hex.length - 1; i += 2)
                    bytes.push(parseInt(hex.substr(i, 2), 16));

                return String.fromCharCode.apply(String, bytes);
            }

            public static hash(bin: string) {
                let i = 0, l = bin.length, chr, hex = '';
                for (i; i < l; ++i) {
                    chr = bin.charCodeAt(i).toString(16);
                    hex += chr.length < 2 ? '0' + chr : chr;
                }

                return hex

            }
        }

        /**
         * Logger
         */
        export class Logger {

            public static debug = false;

            static log(item: any): void {
                if (this.debug) {
                    if (item instanceof Object) {
                        console.log("GlobalScreen: ");
                        console.log(item);
                    } else {
                        let line = String(item);
                        console.log("GlobalScreen: " + line);
                    }
                }

            }
        }

        /**
         * Cookie
         */


        interface IKeyValueStorage {
            add(key: string, value: any): void;

            exists(key: string): boolean;

            get(key: string): any;

            remove(key: string): any;

            values(): Array<any>;
        }

        export interface ClassLoader {
            loadFromRawData(raw_data: any): any;
        }


        abstract class Storage {
            protected readonly namespace: string;
            protected items: { [index: string]: any } = {};
            private classloader: ClassLoader;

            public constructor(namespace: string, classloader: ClassLoader) {
                this.namespace = namespace;
                this.classloader = classloader;
                this.read();
            }

            protected getItemViaLoader(item: any): any {
                return this.classloader.loadFromRawData(item);
            }

            abstract store(): void;

            abstract read(): void;

            public add(key: string, value: any): void {
                this.items[key] = value;
                this.store();
            }

            public remove(key: string): any {
                let val = this.items[key];
                delete this.items[key];
                this.store();

                return val;
            }

            public get(key: string): any {
                return this.items[key];
            }

            public raw(): { [index: string]: any } {
                return this.items;
            }

            public values(): Array<any> {
                let values: any[] = [];

                for (let prop in this.items) {
                    if (this.items.hasOwnProperty(prop)) {
                        values.push(this.items[prop]);
                    }
                }

                return values;
            }

            public exists(key: string): boolean {
                return this.items.hasOwnProperty(key);
            }

            public keys(): Array<string> {
                let keySet: string[] = [];

                for (let prop in this.items) {
                    if (this.items.hasOwnProperty(prop)) {
                        keySet.push(prop);
                    }
                }

                return keySet;

            }
        }

        export class LocalStorage extends Storage implements IKeyValueStorage {
            read(): void {
                let items = JSON.parse(localStorage.getItem(this.namespace));
                let loaded_items: { [index: string]: any } = {};
                for (let i in items) {
                    loaded_items[i] = this.getItemViaLoader(items[i]);
                }
                this.items = loaded_items || {};
            }

            store(): void {
                localStorage[this.namespace] = JSON.stringify(this.items);
            }
        }

        export class CookieStorage extends Storage implements IKeyValueStorage {

            private readonly ttl_in_min: number = 5;


            constructor(namespace: string, classloader: ClassLoader = new class implements ClassLoader {
                loadFromRawData(raw_data: any): any {
                    return raw_data;
                }
            }, ttl_in_min: number = 5) {
                super(namespace, classloader);
                Logger.log("Creating new cookie storage " + namespace);
                this.ttl_in_min = ttl_in_min;
            }


            public read(): void {
                let name = this.namespace + "=";
                let decodedCookie = decodeURIComponent(document.cookie);
                let ca = decodedCookie.split(';');
                this.items = {};
                for (let i = 0; i < ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) == ' ') {
                        c = c.substring(1);
                    }
                    if (c.indexOf(name) == 0) {
                        let items: Array<any> = JSON.parse(c.substring(name.length, c.length));
                        for (let key in items) {
                            Logger.log("Add item from cookie " + key);
                            let raw_item = items[key];
                            Logger.log(raw_item);
                            this.add(key, raw_item);
                        }
                    }
                }

            }

            public store(): void {
                let d = new Date();
                d.setTime(d.getTime() + (this.ttl_in_min * 60 * 1000));
                let expires = "expires=" + d.toUTCString();
                let cookie = this.namespace + "=" + JSON.stringify(this.items) + ";" + expires + ";path=/";
                // Logger.log(cookie);
                document.cookie = cookie;
            }
        }
    }
    /**
     * GlobalScreen
     */
    export module GS {
        const SPLIITER: string = "|";

        class ClientSettings {
            public clear_states_for_levels: { [key: number]: Array<number> } = {
                1: [1, 2],
                2: [2],
                10: [],
            };
            public hashing: boolean = false;
            public logging: boolean = true;
            public store_state_for_levels: Array<number> = [];
        }

        /**
         * Namespace Provider
         */
        export namespace Provider {
            /**
             * Public API
             */
            export function getClientSideProvider(provider_name: string): ClientSideProvider {
                return new ClientSideProvider(provider_name);
            }

            export function getServerSideProviderFromCombinedString(from_serialized_string: string): ServerSideProvider {
                let elements = from_serialized_string.split(SPLIITER);

                return new ServerSideProvider(elements[0]);

            }

            export function getServerSideProvider(provider_name: string): ServerSideProvider {
                return new ServerSideProvider(provider_name);
            }

            /**
             * Interfaces
             */
            export interface isProvider {
                provider_name: string;
            }

            /**
             * Implementations
             */
            class ClientSideProvider implements isProvider {

                constructor(provider_name: string) {
                    this.provider_name = provider_name;
                    this.is_client_side = true;
                }

                provider_name: string;
                is_client_side: boolean;

            }

            class ServerSideProvider implements isProvider {
                constructor(provider_name: string) {
                    this.provider_name = provider_name;
                    this.is_client_side = false;
                }

                provider_name: string;
                is_client_side: boolean;
            }
        }

        /**
         * Namespace Identification
         */
        export namespace Identification {
            import getServerSideProvider = il.GS.Provider.getServerSideProvider;
            import isProvider = il.GS.Provider.isProvider;
            import Logger = il.Utilities.Logger;

            /**
             * Public API
             */
            export function getFromServerSideString(server_side_string: string): isIdentification {
                let elements = server_side_string.split(SPLIITER);
                let provider = getServerSideProvider(elements[0]);

                return new StandardIdentification(elements[1], provider);
            }

            export function get(internal_identifier: string, provider_string: string) {
                let provider = getServerSideProvider(provider_string);
                return new StandardIdentification(internal_identifier, provider);
            }

            export interface isIdentification {
                provider: isProvider,
                internal_identifier: string;

                toString(): string;
            }


            /**
             * Implementations
             */

            export class StandardIdentification implements isIdentification {

                private readonly as_string: string;

                constructor(internal_identifier: string, provider: isProvider) {
                    this.internal_identifier = internal_identifier;
                    this.provider = provider;
                    this.as_string = this.provider.provider_name + "|" + this.internal_identifier;
                }

                internal_identifier: string;
                provider: isProvider;

                toString(): string {
                    return this.as_string;
                }
            }
        }

        /**
         * Namespace Client
         */
        export namespace Client {


            import isIdentification = il.GS.Identification.isIdentification;
            import Logger = il.Utilities.Logger;
            import Hasher = il.Utilities.Hasher;
            import LocalStorage = il.Utilities.LocalStorage;
            import CookieStorage = il.Utilities.CookieStorage;
            import ClassLoader = il.Utilities.ClassLoader;


            const settings: ClientSettings = new ClientSettings();


            export interface isItem {
                identification: isIdentification;
                level: number;
                ui_id: string;
                active: boolean;
            }

            export class Item implements isItem {
                identification: isIdentification;
                level: number;
                ui_id: string;
                active: boolean = false;

                constructor(identification: il.GS.Identification.isIdentification, level: number, ui_id: string) {
                    this.identification = identification;
                    this.level = level;
                    this.ui_id = ui_id;
                }
            }

            export function newItem(id: isIdentification, ui_id: string, level: number): Item {
                return new Item(id, level, ui_id);
            }


            class ItemStorage {
                private readonly local_storage: LocalStorage = new LocalStorage('gs_item_storage', new class implements ClassLoader {
                    loadFromRawData(raw_data: any): any {
                        let id = Identification.get(raw_data.identification.internal_identifier, raw_data.identification.provider.provider_name);
                        let item = Client.newItem(id, raw_data.ui_id, raw_data.level);

                        return item;
                    }
                });
                private readonly cookie_storage: CookieStorage = new CookieStorage('gs_active_items');

                private hash(i: isIdentification): string {
                    if (settings.hashing === true) {
                        return Hasher.hash(i.toString());
                    } else {
                        return i.toString();
                    }
                }

                public storeItem(item: Item): void {
                    this.local_storage.add(this.hash(item.identification), item);
                }

                public itemExists(id: isIdentification): boolean {
                    let item = this.getItem(id);
                    return item instanceof Item;
                }

                public getItem(id: isIdentification): Item {
                    // @ts-ignore
                    return this.local_storage.values().find(function (item: Item) {
                        if (item.identification.toString() === id.toString()) {
                            return item;
                        }
                    });
                }

                public itemExistsByUIID(ui_id: string): boolean {
                    return (this.getItemByUUID(ui_id) !== undefined);
                }

                public getItemByUUID(ui_id: string): Item {
                    // @ts-ignore
                    return this.local_storage.values().find(function (item: Item) {
                        return (item.ui_id === ui_id);
                    });
                }

                public removeItem(item: Item): void {
                    this.local_storage.remove(this.hash(item.identification));
                    this.cookie_storage.remove(this.hash(item.identification));
                }

                private findItemWhichMustBeClosed(current_item: Item): Item[] {
                    let levels_to_close: Array<number> = [0];
                    if (current_item.level in settings.clear_states_for_levels) {
                        levels_to_close = settings.clear_states_for_levels[current_item.level]
                        Logger.log("Levels to close " + levels_to_close.toString())
                    }

                    return this.local_storage.values().filter(function (item: Item) {
                        return (levels_to_close.indexOf(item.level) > -1);
                    });
                }

                private activateItem(item: Item): void {
                    if (settings.store_state_for_levels.indexOf(item.level) > -1) {
                        item.active = true;
                        this.storeItem(item);
                        this.cookie_storage.add(this.hash(item.identification), true);
                    }
                }

                private deactivateItem(item: Item): void {
                    item.active = false;
                    this.storeItem(item);
                    this.cookie_storage.remove(this.hash(item.identification));
                }

                public handleTriggeredItem(triggered_item: Item): void {
                    Logger.log("Handle Item");
                    Logger.log(triggered_item);
                    if (triggered_item.active === true) {
                        Logger.log("Deactivate Item");
                        this.deactivateItem(triggered_item);
                    } else {
                        Logger.log("Activate Item");
                        triggered_item.active = true;
                        this.activateItem(triggered_item);

                        let items_to_close = this.findItemWhichMustBeClosed(triggered_item);
                        for (let i in items_to_close) {
                            let item: Item = items_to_close[i];
                            if (this.hash(item.identification) !== this.hash(triggered_item.identification)) {
                                Logger.log("Deactivating subsequent Item " + item.identification.toString());
                                this.deactivateItem(item);
                            }
                        }
                    }
                }
            }

            const item_storage: ItemStorage = new ItemStorage();


            export function register(id: isIdentification, ui_id: string, level: number) {
                if (!item_storage.itemExists(id)) {
                    Logger.log("Item not found, registering " + id.toString());
                    item_storage.storeItem(this.newItem(id, ui_id, level));
                } else {
                    let item = item_storage.getItem(id);
                    item.ui_id = ui_id;
                    item_storage.storeItem(item);
                }

            }

            export function trigger(ui_id: string) {
                if (item_storage.itemExistsByUIID(ui_id)) {
                    let item = item_storage.getItemByUUID(ui_id);
                    item_storage.handleTriggeredItem(item);
                } else {
                    Logger.log("Item not found");
                }
            }

            export function init(json: string) {
                json = JSON.parse(json);
                // @ts-ignore
                Object.assign(settings, json);
                Logger.log(settings);
                Logger.debug = settings.logging;
            }

        }


    }
}
