(() => {
    let confirmSettings = () => {
        let init = (id) => {
            const form = id.getElementsByTagName('form')[0];
            form.addEventListener('submit',  (e) => {
                e.preventDefault();
                id.previousElementSibling.action = form.action;
                id.previousElementSibling.submit();
            });
        };

        let public_interface = {
            init
        };
        return public_interface;
    };

    il = il || {};
    il.test = il.test || {};
    il.test.confirmSettings = confirmSettings();
})();
