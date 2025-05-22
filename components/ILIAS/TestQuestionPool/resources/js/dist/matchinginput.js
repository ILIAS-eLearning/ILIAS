(() => {
    let matchingquestion = () => {
        let init = () => {
            const add_links = document.getElementsByClassName('matchingwizard_add');
            const remove_links = document.getElementsByClassName('matchingwizard_remove');

            for (const add_link of add_links) {
                add_link.childNodes[0].addEventListener('click', (e) => {
                    onClickHandler('add', e);
                });
            }

            for (const remove_link of remove_links) {
                remove_link.childNodes[0].addEventListener('click', (e) => {
                    onClickHandler('remove', e);
                });
            }
        };

        let onClickHandler = (action, e) => {
            e.preventDefault();
            const id_tag = e.currentTarget.parentNode.id.split('[');
            const id = id_tag.pop().slice(0, -1);
            const target = id_tag[0].split('_').pop();
            let button = document.createElement('BUTTON');
            button.type = 'submit';
            button.name = 'cmd[' + action + target  + '][' + id + ']';
            button.style.display = 'none';
            e.target.insertAdjacentElement('afterend', button);
            button.form.requestSubmit(button);
        };

        let public_interface = {
            init
        };

        return public_interface;
    };

    il = il || {};
    il.test = il.test || {};
    il.test.matchingquestion = matchingquestion();
})();

