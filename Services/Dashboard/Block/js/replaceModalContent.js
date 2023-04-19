const replaceModalContent = (function (id, url) {
    document.getElementById(id).addEventListener('click', function (e) {
        const form = document.querySelector('form[name="pd_remove_multiple"]');
        const formData = new FormData(form);
        let selected_ids = [];
        for (const [name, value] of formData) {
            selected_ids.push(value);
        }
        const modal = document.querySelector('div[data-modal-name="remove_modal"]');
        let post_data = '';
        for (let i = 0; i < selected_ids.length; i++) {
            post_data += 'id[]=' + encodeURIComponent(selected_ids[i]) + '&';
        }
        post_data = post_data.slice(0, -1);
        form.addEventListener('submit', function (e) {
            e.preventDefault();
        });
        const modalFooter = modal.querySelector('.modal-footer');
        modalFooter.parentNode.removeChild(modalFooter);
        il.Util.ajaxReplacePostRequestInner(url, post_data, 'pd_unsubscribe_multiple');
    });
});