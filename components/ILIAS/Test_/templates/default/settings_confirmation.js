(() => {
    let confirmSettings = () => {
        let init = (id) => {
            id.getElementsByTagName('form')[0].addEventListener('submit',  (e) => {
                e.preventDefault();
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
