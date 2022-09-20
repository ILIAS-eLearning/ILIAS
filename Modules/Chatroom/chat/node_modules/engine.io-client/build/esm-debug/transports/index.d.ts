import { XHR } from "./polling-xhr.js";
import { WS } from "./websocket.js";
export declare const transports: {
    websocket: typeof WS;
    polling: typeof XHR;
};
