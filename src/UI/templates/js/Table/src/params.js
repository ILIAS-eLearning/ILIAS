class Params {
    /**
     * @param {string} target
     * @param {string} parameter_name
     * @param {string[]} values
     * @return {object}
     */
    amendParameterToSignal(target, parameter_name, values) {
        let sig = JSON.parse(target);
        sig.options[parameter_name] = values;
        return sig;
    }

    /**
     * @param {string} target
     * @param {string} parameter_name
     * @param {string[]} values
     * @return {string}
     */
    amendParameterToUrl(target, parameter_name, values) {
        const base = target.split('?')[0];
        let params = this.getParametersFromUrl(decodeURI(target));
        let search = '';
        let k;

        params[parameter_name] = encodeURI(JSON.stringify(values));

        for (k in params) {
            search = `${search}&${k}=${params[k]}`;
        }
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
