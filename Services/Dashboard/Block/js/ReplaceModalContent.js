/* global il */
il.Dashboard = il.Dashboard || {};
/**
 * @param {string} id
 * @param {int} view
 * @param {string} url
 */
il.Dashboard.replaceModalContent = function (id, view, url) {
    document.getElementById(id).addEventListener('click', function (e) {
        const form = document.querySelector('form[name="pd_remove_multiple_view_' + view + '"]');
        const formData = new FormData(form);
        let post_data = '';
        for (const [name, value] of formData) {
            post_data += 'id[]=' + encodeURIComponent(value) + '&';
        }
        const modal = document.querySelector('div[data-modal-name="remove_modal_view_' + view + '"]');
        post_data = post_data.slice(0, -1);
        form.addEventListener('submit', function (e) {
            e.preventDefault();
        });
            const modalFooter = modal.querySelector('.modal-footer');
        modalFooter.parentNode.removeChild(modalFooter);
        il.Util.ajaxReplacePostRequestInner(url, post_data, 'pd_unsubscribe_multiple_view_' + view);
    });
};
