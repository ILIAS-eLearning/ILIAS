import math_mod from "./math.js";

export default class Component {

  /**
   * Pass dependency to math module in constructor
   * @param math
   */
  constructor(math) {
    this.math = math || math_mod;
  }

  calculate(a,b) {
    return this.math.sum(a,b);
  }
}