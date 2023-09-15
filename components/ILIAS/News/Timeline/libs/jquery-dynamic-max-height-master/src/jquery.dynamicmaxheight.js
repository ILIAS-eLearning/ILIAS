/**
 * Dynamic max height plugin for jQuery created by Joan Claret 
 *
 * @copyright Copyright 2015 Joan Claret
 * @license   MIT
 * @author    Joan Claret
 * @version   1.0
 *
 * Licensed under The MIT License (MIT).
 * Copyright (c) 2015 Joan Claret
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */


;(function($, document, window, undefined) {

  'use strict';
  
  var dynamicMaxHeight = 

    $.fn.dynamicMaxHeight = function(selector) {

        // Define variable classes
        var dynamicHeightWrapperClass = 'dynamic-height-wrap',
            dynamicHeightActiveClass = 'dynamic-height-active',
            dynamicHeightButtonClass = 'js-dynamic-show-hide'
        ;

        return this.each(function(i, selector) {
            
            /**
             * Init plugin. Get max height and layer height
             */
            var el = $(selector),
                itemMaxHeight = el.data('maxheight'),
                itemHeight = el.find('.'+dynamicHeightWrapperClass).outerHeight(),
                itemButton = el.find('.'+dynamicHeightButtonClass)
            ;

            el.attr("data-itemheight", itemHeight ); // store layer height as a data attribute

            
            /**
             * Apply max height if necessary
             */
            if (itemHeight > itemMaxHeight){
                updateHeight(el, itemMaxHeight);
                el.toggleClass(dynamicHeightActiveClass);
                showDynamicMaxHeightButton(el, itemButton);
            } 

            /**
             * Setup "show more / show less" button
             */
            itemButton.click(function(){
                if(el.hasClass(dynamicHeightActiveClass)){
                   updateHeight(el, itemHeight);
                }
                else{
                    updateHeight(el, itemMaxHeight);
                }
                updateTextButton(el, itemButton);
                el.toggleClass(dynamicHeightActiveClass);                
            }); 
        });

        function updateTextButton(el, itemButton){
            var buttonText;
            if(el.hasClass(dynamicHeightActiveClass)){
                buttonText = itemButton.data( 'replace-text' );
            }
            else{
                buttonText = itemButton.attr( 'title' );
            }
            itemButton.text(buttonText);
        }

        function updateHeight(el, height){
            el.find('.'+dynamicHeightWrapperClass).css('max-height', height);
        }

        function showDynamicMaxHeightButton(el, itemButton){
            itemButton.css('display','inline-block');
        }
    };
})(window.jQuery || window.$, document, window);

    
/**
 * Export as a CommonJS module
 */
if (typeof module !== 'undefined' && module.exports) {
    module.exports = dynamicMaxHeight;
}