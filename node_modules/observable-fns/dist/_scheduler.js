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
exports.AsyncSerialScheduler = void 0;
class AsyncSerialScheduler {
    constructor(observer) {
        this._baseObserver = observer;
        this._pendingPromises = new Set();
    }
    complete() {
        Promise.all(this._pendingPromises)
            .then(() => this._baseObserver.complete())
            .catch(error => this._baseObserver.error(error));
    }
    error(error) {
        this._baseObserver.error(error);
    }
    schedule(task) {
        const prevPromisesCompletion = Promise.all(this._pendingPromises);
        const values = [];
        const next = (value) => values.push(value);
        const promise = Promise.resolve()
            .then(() => __awaiter(this, void 0, void 0, function* () {
            yield prevPromisesCompletion;
            yield task(next);
            this._pendingPromises.delete(promise);
            for (const value of values) {
                this._baseObserver.next(value);
            }
        }))
            .catch(error => {
            this._pendingPromises.delete(promise);
            this._baseObserver.error(error);
        });
        this._pendingPromises.add(promise);
    }
}
exports.AsyncSerialScheduler = AsyncSerialScheduler;
