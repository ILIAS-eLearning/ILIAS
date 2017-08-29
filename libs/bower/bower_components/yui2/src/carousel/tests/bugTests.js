(function () {
     var ArrayAssert  = YAHOO.util.ArrayAssert,
         Assert       = YAHOO.util.Assert,
         carousel,
         test2528929;

    YAHOO.namespace("CarouselTests");

    test2528929 = new YAHOO.tool.TestCase({
            name: "Unit test for ticket #2528929",

            setUp: function() {
                var items = [
                        ["One", 0],  ["Two", 0], ["Three", 0], ["Four", 0],
                        ["Five", 0], ["Six", 0], ["Seven", 0], ["Eight", 0]
                ];
                carousel.addItems(items);
            },

            tearDown: function() {
                carousel.clearItems();
            },

            testCircular: function() {
                carousel.set("isCircular", true);
                carousel.scrollPageForward();
                carousel.scrollPageForward();
                carousel.scrollPageForward(); // this goes to first page again
                carousel.scrollPageBackward();
                Assert.areEqual(6, carousel.get("firstVisible"));
            }
    });

    YAHOO.CarouselTests.bugTests = new YAHOO.tool.TestSuite({
            name: "Carousel (bugs) Tests",

            setUp: function () {
                carousel = new YAHOO.widget.Carousel("bug-container");
                carousel.render();
            },

            tearDown : function () {
                delete carousel;
            }
    });

   YAHOO.CarouselTests.bugTests.add(test2528929);
})();
/*
;;  Local variables: **
;;  mode: js2 **
;;  indent-tabs-mode: nil **
;;  End: **
*/
