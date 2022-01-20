"use strict";
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
Object.defineProperty(exports, "__esModule", { value: true });
const _scheduler_1 = require("./_scheduler");
const observable_1 = require("./observable");
const unsubscribe_1 = require("./unsubscribe");
function scan(accumulator, seed) {
    return (observable) => {
        return new observable_1.default(observer => {
            let accumulated;
            let index = 0;
            const scheduler = new _scheduler_1.AsyncSerialScheduler(observer);
            const subscription = observable.subscribe({
                complete() {
                    scheduler.complete();
                },
                error(error) {
                    scheduler.error(error);
                },
                next(value) {
                    scheduler.schedule((next) => __awaiter(this, void 0, void 0, function* () {
                        const prevAcc = index === 0
                            ? (typeof seed === "undefined" ? value : seed)
                            : accumulated;
                        accumulated = yield accumulator(prevAcc, value, index++);
                        next(accumulated);
                    }));
                }
            });
            return () => unsubscribe_1.default(subscription);
        });
    };
}
exports.default = scan;
