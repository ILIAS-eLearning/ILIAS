
var params = function() {
    var
    amendParameterToSignal = function(target, parameter_name, values) {
        target = JSON.parse(target);
        target.options[parameter_name] = values;
        return target;
    },

    amendParameterToUrl = function(target, parameter_name, values) {

        var base = target.split('?')[0],
            params = getParametersFromUrl(decodeURI(target)),
            search = '', k;

        params[parameter_name] = encodeURI(JSON.stringify(values));

        for(k in params) {
            search = search + '&' + k + '=' + params[k];
        }

        target = base + '?' + search.substr(1);
        return target;
    },

    getParametersFromUrl = function (url) {
        var params = {},
            parts = url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m, key, value) {
                params[key] = value;
            });
        return params;
    },

    public_interface = {
        getParametersFromUrl: getParametersFromUrl,
        amendParameterToSignal: amendParameterToSignal,
        amendParameterToUrl: amendParameterToUrl
    };

    return public_interface;
}

export default params;