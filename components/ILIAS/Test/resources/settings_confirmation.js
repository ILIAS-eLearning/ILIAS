(() => {
    let confirmSettings = () => {
        let init = (id) => {
            const submit = id.querySelector('input[type="submit"]');
            submit.addEventListener('click',  (e) => {
                e.preventDefault();
                id.previousElementSibling.action = e.target.form.action;
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
