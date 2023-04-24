
class params {
    /**
     * @param string target
     * @param string parameter_name
     * @param array values
     */
    amendParameterToSignal(target, parameter_name, values) {
        target = JSON.parse(target);
        target.options[parameter_name] = values;
        return target;
    }

    /**
     * @param string target
     * @param string parameter_name
     * @param array values
     */
    amendParameterToUrl(target, parameter_name, values) {
        let base = target.split('?')[0];
        let params = this.getParametersFromUrl(decodeURI(target));
        let search = '';
        let k;

        params[parameter_name] = encodeURI(JSON.stringify(values));

        for(k in params) {
            search = search + '&' + k + '=' + params[k];
        }

        target = base + '?' + search.substr(1);
        return target;
    }

    /**
     * @param string url
     */
    getParametersFromUrl(url) {
        let params = {};
        let parts = url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m, key, value) {
                params[key] = value;
            });
        return params;
    }
}

export default params;