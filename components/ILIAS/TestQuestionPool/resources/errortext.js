(function () {
    let errortext = () => {
        let init = () => {
            let errortext = document.querySelectorAll('div.errortext a');
            errortext.forEach((elem) => {
                elem.addEventListener('click', click_function);
            });
        };

        let click_function = (e) => {
            e.preventDefault();
            e.stopPropagation();

            let context  = e.target.closest('.errortext');
            let class_list = e.target.classList;

            if (class_list.contains('ilc_qetitem_ErrorTextItem')) {
                class_list.remove('ilc_qetitem_ErrorTextItem');
                class_list.add('ilc_qetitem_ErrorTextSelected');
            } else if (class_list.contains('ilc_qetitem_ErrorTextSelected')) {
                class_list.remove('ilc_qetitem_ErrorTextSelected');
                class_list.add('ilc_qetitem_ErrorTextItem');
            }

            let selected = [];
            context.querySelectorAll('a').forEach((e,i) => {
                if (e.classList.contains('ilc_qetitem_ErrorTextSelected')) {
                    selected.push(i);
                }
            });
            context.querySelector('input[type=hidden]').value = selected.join(',');
        };

        let public_interface = {
            init
        };
        return public_interface;
    };

    il = il || {};
    il.test = il.test || {};
    il.test.player = il.test.player || {};
    il.test.player.errortext = errortext();
}());