namespace il {

    /**
     * Module GlobalScreen
     */
    export module GS {


        /**
         * Logger
         */
        class Logger {
            private static readonly debug = true;

            static log(item: any): void {
                if (this.debug) {
                    let line = String(item);
                    console.log("GlobalScreen: " + line)
                }

            }
        }

        /**
         * Provider
         */
        export namespace Provider {
            export class ProviderFactory {
                private readonly providers: isProvider[];


                constructor() {
                    this.providers = [];
                }

                public getByProviderName(provider_name: string) {
                    for (let provider of this.providers) {
                        if (provider.provider_name === provider_name) {
                            return provider;
                        }
                    }
                    let clientSideProvider = new ClientSideProvider(provider_name);
                    this.providers.push(clientSideProvider);
                    return clientSideProvider;
                }
            }

            export interface isProvider {
                provider_name: string;
            }

            export class ClientSideProvider implements isProvider {

                constructor(provider_name: string) {
                    this.provider_name = provider_name;
                }

                provider_name: string;

            }
        }


        /**
         * Identification
         */
        export namespace Identification {
            import isProvider = il.GS.Provider.isProvider;

            export interface isIdentification {
                provider: isProvider,
                internal_identifier: string;

                toString(): string;
            }


            export class StandardIdentification implements isIdentification {

                constructor(internal_identifier: string, provider: isProvider) {
                    this.internal_identifier = internal_identifier;
                    this.provider = provider;
                }

                internal_identifier: string;
                provider: isProvider;

                toString(): string {
                    return this.provider.provider_name + "|" + this.internal_identifier;
                }
            }
        }

        /**
         * Collector
         */
        export namespace Collector {
            import isDTO = il.GS.DTO.isDTO;

            export class CollectorFactory {
                notifications(): NotificationCollector {
                    return new NotificationCollector();
                }
            }


            class NotificationCollector {
                private dtos: isDTO[] = [];


                getAll(): isDTO[] {
                    return this.dtos;
                }

                push(dto: isDTO): void {
                    this.dtos.push(dto);
                }
            }
        }
        export namespace DTO {
            /**
             * DTO
             */
            import isIdentification = il.GS.Identification.isIdentification;


            export interface isDTO {
                identification: isIdentification;
            }

            export namespace Notifications {
                export class OnScreenNotificationDTO implements isDTO {
                    constructor(identification: il.GS.Identification.isIdentification) {
                        this.identification = identification;
                        this.date = new Date();
                    }

                    identification: isIdentification;
                    title: string;
                    summary: string;
                    date: Date;
                }

                export class NotificationDTOFactory {
                    public onScreen(identification: isIdentification): OnScreenNotificationDTO {
                        Logger.log('want to have an OnScreenNotificationDTO for identification ' + identification.toString())
                        return new OnScreenNotificationDTO(identification);
                    }
                }
            }


            export class DTOFactory {
                public notifications(): NotificationDTOFactory {
                    return new NotificationDTOFactory();
                }
            }

        }


        /**
         * PUBLIC INTERFACE
         */


        import DTOFactory = il.GS.DTO.DTOFactory;
        import NotificationDTOFactory = il.GS.DTO.Notifications.NotificationDTOFactory;
        import StandardIdentification = il.GS.Identification.StandardIdentification;
        import ProviderFactory = il.GS.Provider.ProviderFactory;
        import isProvider = il.GS.Provider.isProvider;
        import CollectorFactory = il.GS.Collector.CollectorFactory;

        export class Services {

            private static provider_factory: ProviderFactory = new ProviderFactory();
            private static collector_factory: CollectorFactory = new CollectorFactory();
            private static dto_factory: DTOFactory = new DTOFactory();

            public static provider(): ProviderFactory {
                return this.provider_factory;
            }

            public static identification(provider: isProvider, internal_identifier: string) {
                Logger.log("register identification for provider " + provider.provider_name + " and internal id " + internal_identifier);
                return new StandardIdentification(internal_identifier, provider);
            }

            public static collector(): CollectorFactory {
                return this.collector_factory
            }

            public static factory(): DTOFactory {
                return this.dto_factory;
            }
        }


    }
}