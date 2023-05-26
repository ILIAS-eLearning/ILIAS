class Params {
  /**
   * @param {string} target
   * @param {string} parameterName
   * @param {string[]} values
   * @return {object}
   */
  amendParameterToSignal(target, parameterName, values) {
    const sig = JSON.parse(target);
    sig.options[parameterName] = values;
    return sig;
  }

  /**
   * @param {string} target
   * @param {string} parameterName
   * @param {string[]} values
   * @return {string}
   */
  amendParameterToUrl(target, parameterName, values) {
    const base = target.split('?')[0];
    const params = this.getParametersFromUrl(decodeURI(target));
    let search = '';

    params[parameterName] = encodeURI(JSON.stringify(values));
    Object.keys(params).forEach(
      (k) => {
        search = `${search}&${k}=${params[k]}`;
      },
    );
    return `${base}?${search.substr(1)}`;
  }

  /**
   * @param {string} url
   * @return {array<string,string>}
   */
  getParametersFromUrl(url) {
    const params = {};
    url.replace(/[?&]+([^=&]+)=([^&]*)/gi, (m, key, value) => {
      params[key] = value;
    });
    return params;
  }
}

export default Params;
