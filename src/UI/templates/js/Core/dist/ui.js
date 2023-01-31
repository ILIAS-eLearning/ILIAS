(function () {
    'use strict';

    /**
     * Replace a component or parts of a component using ajax call
     *
     * @param id component id
     * @param url replacement url
     * @param marker replacement marker ("component", "content", "header", ...)
     */
    var replaceContent = function($) {
        return function (id, url, marker) {
            // get new stuff via ajax
            $.ajax({
                url: url,
                dataType: 'html'
            }).done(function(html) {
                var $new_content = $("<div>" + html + "</div>");
                var $marked_new_content = $new_content.find("[data-replace-marker='" + marker + "']").first();

                if ($marked_new_content.length == 0) {

                    // if marker does not come with the new content, we put the new content into the existing marker
                    // (this includes all script tags already)
                    $("#" + id + " [data-replace-marker='" + marker + "']").html(html);

                } else {

                    // if marker is in new content, we replace the complete old node with the marker
                    // with the new marked node
                    $("#" + id + " [data-replace-marker='" + marker + "']").first()
                        .replaceWith($marked_new_content);

                    // append included script (which will not be part of the marked node
                    $("#" + id + " [data-replace-marker='" + marker + "']").first()
                        .after($new_content.find("[data-replace-marker='script']"));
                }
            });
        }
    };

    il = il || {};
    il.UI = il.UI || {};
    il.UI.core = il.UI.core || {};

    il.UI.core.replaceContent = replaceContent($);

})();
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidWkuanMiLCJzb3VyY2VzIjpbIi4uL3NyYy9jb3JlLnJlcGxhY2VDb250ZW50LmpzIiwiLi4vc3JjL2NvcmUuanMiXSwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBSZXBsYWNlIGEgY29tcG9uZW50IG9yIHBhcnRzIG9mIGEgY29tcG9uZW50IHVzaW5nIGFqYXggY2FsbFxuICpcbiAqIEBwYXJhbSBpZCBjb21wb25lbnQgaWRcbiAqIEBwYXJhbSB1cmwgcmVwbGFjZW1lbnQgdXJsXG4gKiBAcGFyYW0gbWFya2VyIHJlcGxhY2VtZW50IG1hcmtlciAoXCJjb21wb25lbnRcIiwgXCJjb250ZW50XCIsIFwiaGVhZGVyXCIsIC4uLilcbiAqL1xudmFyIHJlcGxhY2VDb250ZW50ID0gZnVuY3Rpb24oJCkge1xuICAgIHJldHVybiBmdW5jdGlvbiAoaWQsIHVybCwgbWFya2VyKSB7XG4gICAgICAgIC8vIGdldCBuZXcgc3R1ZmYgdmlhIGFqYXhcbiAgICAgICAgJC5hamF4KHtcbiAgICAgICAgICAgIHVybDogdXJsLFxuICAgICAgICAgICAgZGF0YVR5cGU6ICdodG1sJ1xuICAgICAgICB9KS5kb25lKGZ1bmN0aW9uKGh0bWwpIHtcbiAgICAgICAgICAgIHZhciAkbmV3X2NvbnRlbnQgPSAkKFwiPGRpdj5cIiArIGh0bWwgKyBcIjwvZGl2PlwiKTtcbiAgICAgICAgICAgIHZhciAkbWFya2VkX25ld19jb250ZW50ID0gJG5ld19jb250ZW50LmZpbmQoXCJbZGF0YS1yZXBsYWNlLW1hcmtlcj0nXCIgKyBtYXJrZXIgKyBcIiddXCIpLmZpcnN0KCk7XG5cbiAgICAgICAgICAgIGlmICgkbWFya2VkX25ld19jb250ZW50Lmxlbmd0aCA9PSAwKSB7XG5cbiAgICAgICAgICAgICAgICAvLyBpZiBtYXJrZXIgZG9lcyBub3QgY29tZSB3aXRoIHRoZSBuZXcgY29udGVudCwgd2UgcHV0IHRoZSBuZXcgY29udGVudCBpbnRvIHRoZSBleGlzdGluZyBtYXJrZXJcbiAgICAgICAgICAgICAgICAvLyAodGhpcyBpbmNsdWRlcyBhbGwgc2NyaXB0IHRhZ3MgYWxyZWFkeSlcbiAgICAgICAgICAgICAgICAkKFwiI1wiICsgaWQgKyBcIiBbZGF0YS1yZXBsYWNlLW1hcmtlcj0nXCIgKyBtYXJrZXIgKyBcIiddXCIpLmh0bWwoaHRtbCk7XG5cbiAgICAgICAgICAgIH0gZWxzZSB7XG5cbiAgICAgICAgICAgICAgICAvLyBpZiBtYXJrZXIgaXMgaW4gbmV3IGNvbnRlbnQsIHdlIHJlcGxhY2UgdGhlIGNvbXBsZXRlIG9sZCBub2RlIHdpdGggdGhlIG1hcmtlclxuICAgICAgICAgICAgICAgIC8vIHdpdGggdGhlIG5ldyBtYXJrZWQgbm9kZVxuICAgICAgICAgICAgICAgICQoXCIjXCIgKyBpZCArIFwiIFtkYXRhLXJlcGxhY2UtbWFya2VyPSdcIiArIG1hcmtlciArIFwiJ11cIikuZmlyc3QoKVxuICAgICAgICAgICAgICAgICAgICAucmVwbGFjZVdpdGgoJG1hcmtlZF9uZXdfY29udGVudCk7XG5cbiAgICAgICAgICAgICAgICAvLyBhcHBlbmQgaW5jbHVkZWQgc2NyaXB0ICh3aGljaCB3aWxsIG5vdCBiZSBwYXJ0IG9mIHRoZSBtYXJrZWQgbm9kZVxuICAgICAgICAgICAgICAgICQoXCIjXCIgKyBpZCArIFwiIFtkYXRhLXJlcGxhY2UtbWFya2VyPSdcIiArIG1hcmtlciArIFwiJ11cIikuZmlyc3QoKVxuICAgICAgICAgICAgICAgICAgICAuYWZ0ZXIoJG5ld19jb250ZW50LmZpbmQoXCJbZGF0YS1yZXBsYWNlLW1hcmtlcj0nc2NyaXB0J11cIikpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcbiAgICB9XG59O1xuXG5leHBvcnQgZGVmYXVsdCByZXBsYWNlQ29udGVudDtcbiIsImltcG9ydCByZXBsYWNlQ29udGVudCBmcm9tICcuL2NvcmUucmVwbGFjZUNvbnRlbnQuanMnXG5cbmlsID0gaWwgfHwge307XG5pbC5VSSA9IGlsLlVJIHx8IHt9O1xuaWwuVUkuY29yZSA9IGlsLlVJLmNvcmUgfHwge307XG5cbmlsLlVJLmNvcmUucmVwbGFjZUNvbnRlbnQgPSByZXBsYWNlQ29udGVudCgkKTtcblxuIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiI7OztJQUFBO0lBQ0E7SUFDQTtJQUNBO0lBQ0E7SUFDQTtJQUNBO0lBQ0EsSUFBSSxjQUFjLEdBQUcsU0FBUyxDQUFDLEVBQUU7SUFDakMsSUFBSSxPQUFPLFVBQVUsRUFBRSxFQUFFLEdBQUcsRUFBRSxNQUFNLEVBQUU7SUFDdEM7SUFDQSxRQUFRLENBQUMsQ0FBQyxJQUFJLENBQUM7SUFDZixZQUFZLEdBQUcsRUFBRSxHQUFHO0lBQ3BCLFlBQVksUUFBUSxFQUFFLE1BQU07SUFDNUIsU0FBUyxDQUFDLENBQUMsSUFBSSxDQUFDLFNBQVMsSUFBSSxFQUFFO0lBQy9CLFlBQVksSUFBSSxZQUFZLEdBQUcsQ0FBQyxDQUFDLE9BQU8sR0FBRyxJQUFJLEdBQUcsUUFBUSxDQUFDLENBQUM7SUFDNUQsWUFBWSxJQUFJLG1CQUFtQixHQUFHLFlBQVksQ0FBQyxJQUFJLENBQUMsd0JBQXdCLEdBQUcsTUFBTSxHQUFHLElBQUksQ0FBQyxDQUFDLEtBQUssRUFBRSxDQUFDO0FBQzFHO0lBQ0EsWUFBWSxJQUFJLG1CQUFtQixDQUFDLE1BQU0sSUFBSSxDQUFDLEVBQUU7QUFDakQ7SUFDQTtJQUNBO0lBQ0EsZ0JBQWdCLENBQUMsQ0FBQyxHQUFHLEdBQUcsRUFBRSxHQUFHLHlCQUF5QixHQUFHLE1BQU0sR0FBRyxJQUFJLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUM7QUFDbkY7SUFDQSxhQUFhLE1BQU07QUFDbkI7SUFDQTtJQUNBO0lBQ0EsZ0JBQWdCLENBQUMsQ0FBQyxHQUFHLEdBQUcsRUFBRSxHQUFHLHlCQUF5QixHQUFHLE1BQU0sR0FBRyxJQUFJLENBQUMsQ0FBQyxLQUFLLEVBQUU7SUFDL0UscUJBQXFCLFdBQVcsQ0FBQyxtQkFBbUIsQ0FBQyxDQUFDO0FBQ3REO0lBQ0E7SUFDQSxnQkFBZ0IsQ0FBQyxDQUFDLEdBQUcsR0FBRyxFQUFFLEdBQUcseUJBQXlCLEdBQUcsTUFBTSxHQUFHLElBQUksQ0FBQyxDQUFDLEtBQUssRUFBRTtJQUMvRSxxQkFBcUIsS0FBSyxDQUFDLFlBQVksQ0FBQyxJQUFJLENBQUMsZ0NBQWdDLENBQUMsQ0FBQyxDQUFDO0lBQ2hGLGFBQWE7SUFDYixTQUFTLENBQUMsQ0FBQztJQUNYLEtBQUs7SUFDTCxDQUFDOztJQ2xDRCxFQUFFLEdBQUcsRUFBRSxJQUFJLEVBQUUsQ0FBQztJQUNkLEVBQUUsQ0FBQyxFQUFFLEdBQUcsRUFBRSxDQUFDLEVBQUUsSUFBSSxFQUFFLENBQUM7SUFDcEIsRUFBRSxDQUFDLEVBQUUsQ0FBQyxJQUFJLEdBQUcsRUFBRSxDQUFDLEVBQUUsQ0FBQyxJQUFJLElBQUksRUFBRSxDQUFDO0FBQzlCO0lBQ0EsRUFBRSxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsY0FBYyxHQUFHLGNBQWMsQ0FBQyxDQUFDLENBQUM7Ozs7OzsifQ==
