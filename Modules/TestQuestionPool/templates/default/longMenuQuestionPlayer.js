(function () {
    let longmenu = () => {
        let init = (autocomplete_length, answer_options) => {
            let long_menu_input = Array.from(document.getElementsByClassName('long_menu_input'));
            let long_menu_input_ignore = Array.from(document.getElementsByClassName('long_menu_input_ignore'));

            long_menu_input.forEach(
                (value, index) => {
                    if (value.nodeName === 'INPUT') {
                        var longest = answer_options[index].reduce((a, b) => {
                            return a.length > b.length ? a : b;
                        });
                        value.setAttribute('size', longest.length);
                        $(value).autocomplete({
                            source: answer_options[index],
                            minLength: parseInt(autocomplete_length, 10)
                        });
                    }
                }
            );

            long_menu_input_ignore.forEach(
                (value) => {
                    if (value.nodeName === 'INPUT') {
                        let longest = value.value;
                        value.setAttribute('size', longest.length);
                    }
                }
            );
        };

        let public_interface = {
            init
        };
        return public_interface;
    };

    il = il || {};
    il.test = il.test || {};
    il.test.player = il.test.player || {};
    il.test.player.longmenu = longmenu();
}());
