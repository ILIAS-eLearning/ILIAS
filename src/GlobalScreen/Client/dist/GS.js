var il;
(function (il) {
    var GS;
    (function (GS) {
        var Logger = (function () {
            function Logger() {
            }
            Logger.log = function (item) {
                if (this.debug) {
                    var line = String(item);
                    console.log("GlobalScreen: " + line);
                }
            };
            Logger.debug = true;
            return Logger;
        }());
        var Provider;
        (function (Provider) {
            var ProviderFactory = (function () {
                function ProviderFactory() {
                    this.providers = [];
                }
                ProviderFactory.prototype.getByProviderName = function (provider_name) {
                    for (var _i = 0, _a = this.providers; _i < _a.length; _i++) {
                        var provider = _a[_i];
                        if (provider.provider_name === provider_name) {
                            return provider;
                        }
                    }
                    var clientSideProvider = new ClientSideProvider(provider_name);
                    this.providers.push(clientSideProvider);
                    return clientSideProvider;
                };
                return ProviderFactory;
            }());
            Provider.ProviderFactory = ProviderFactory;
            var ClientSideProvider = (function () {
                function ClientSideProvider(provider_name) {
                    this.provider_name = provider_name;
                }
                return ClientSideProvider;
            }());
            Provider.ClientSideProvider = ClientSideProvider;
        })(Provider = GS.Provider || (GS.Provider = {}));
        var Identification;
        (function (Identification) {
            var StandardIdentification = (function () {
                function StandardIdentification(internal_identifier, provider) {
                    this.internal_identifier = internal_identifier;
                    this.provider = provider;
                }
                StandardIdentification.prototype.toString = function () {
                    return this.provider.provider_name + "|" + this.internal_identifier;
                };
                return StandardIdentification;
            }());
            Identification.StandardIdentification = StandardIdentification;
        })(Identification = GS.Identification || (GS.Identification = {}));
        var Collector;
        (function (Collector) {
            var CollectorFactory = (function () {
                function CollectorFactory() {
                }
                CollectorFactory.prototype.notifications = function () {
                    return new NotificationCollector();
                };
                return CollectorFactory;
            }());
            Collector.CollectorFactory = CollectorFactory;
            var NotificationCollector = (function () {
                function NotificationCollector() {
                    this.dtos = [];
                }
                NotificationCollector.prototype.getAll = function () {
                    return this.dtos;
                };
                NotificationCollector.prototype.push = function (dto) {
                    this.dtos.push(dto);
                };
                return NotificationCollector;
            }());
        })(Collector = GS.Collector || (GS.Collector = {}));
        var DTO;
        (function (DTO) {
            var Notifications;
            (function (Notifications) {
                var OnScreenNotificationDTO = (function () {
                    function OnScreenNotificationDTO(identification) {
                        this.identification = identification;
                        this.date = new Date();
                    }
                    return OnScreenNotificationDTO;
                }());
                Notifications.OnScreenNotificationDTO = OnScreenNotificationDTO;
                var NotificationDTOFactory = (function () {
                    function NotificationDTOFactory() {
                    }
                    NotificationDTOFactory.prototype.onScreen = function (identification) {
                        Logger.log('want to have an OnScreenNotificationDTO for identification ' + identification.toString());
                        return new OnScreenNotificationDTO(identification);
                    };
                    return NotificationDTOFactory;
                }());
                Notifications.NotificationDTOFactory = NotificationDTOFactory;
            })(Notifications = DTO.Notifications || (DTO.Notifications = {}));
            var DTOFactory = (function () {
                function DTOFactory() {
                }
                DTOFactory.prototype.notifications = function () {
                    return new NotificationDTOFactory();
                };
                return DTOFactory;
            }());
            DTO.DTOFactory = DTOFactory;
        })(DTO = GS.DTO || (GS.DTO = {}));
        var DTOFactory = il.GS.DTO.DTOFactory;
        var NotificationDTOFactory = il.GS.DTO.Notifications.NotificationDTOFactory;
        var StandardIdentification = il.GS.Identification.StandardIdentification;
        var ProviderFactory = il.GS.Provider.ProviderFactory;
        var CollectorFactory = il.GS.Collector.CollectorFactory;
        var Services = (function () {
            function Services() {
            }
            Services.provider = function () {
                return this.provider_factory;
            };
            Services.identification = function (provider, internal_identifier) {
                Logger.log("register identification for provider " + provider.provider_name + " and internal id " + internal_identifier);
                return new StandardIdentification(internal_identifier, provider);
            };
            Services.collector = function () {
                return this.collector_factory;
            };
            Services.factory = function () {
                return this.dto_factory;
            };
            Services.provider_factory = new ProviderFactory();
            Services.collector_factory = new CollectorFactory();
            Services.dto_factory = new DTOFactory();
            return Services;
        }());
        GS.Services = Services;
    })(GS = il.GS || (il.GS = {}));
})(il || (il = {}));
