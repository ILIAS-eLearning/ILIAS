import { Worker as WorkerImplementation } from "./index";
if (typeof global !== "undefined") {
    global.Worker = WorkerImplementation;
}
else if (typeof window !== "undefined") {
    window.Worker = WorkerImplementation;
}
