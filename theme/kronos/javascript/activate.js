jQuery(document).ready(function() {

    /**
     * Handle transition in and out of elements within the slide.
     */
    function animate_slider_elements($slide, action) {
        // Clean up lingering animation classes
        clean_up_animations($slide);
        // Check if even or odd and apply appropriate transitions.
        var even = $slide.index()%2 == 0;
        if (action == "show") {
            // Handle animations to show elements.
            $slide.removeClass("previous");
            if (even) {
                animate_element($slide.find("h1"), "fadeInLeft");
                animate_element($slide.find("h2"), "fadeInLeft");
                animate_element($slide.find("p"), "fadeInRight");
            } else {
                animate_element($slide.find("h1"), "fadeInRight");
                animate_element($slide.find("h2"), "fadeInRight");
                animate_element($slide.find("p"), "fadeInLeft");
            }
        } else if (action == "hide") {
            // Handle animations to hide elements.
            $slide.addClass("previous");
            if (even) {
                animate_element($slide.find("h1"), "fadeOutLeft");
                animate_element($slide.find("h2"), "fadeOutLeft");
                animate_element($slide.find("p"), "fadeOutRight");
            } else {
                animate_element($slide.find("h1"), "fadeOutRight");
                animate_element($slide.find("h2"), "fadeOutRight");
                animate_element($slide.find("p"), "fadeOutLeft");
            }
        }
    }

    /**
     * Handle individual animations and remove classes when complete.
     */
    function animate_element($element, transition) {
        $element.addClass("animated "+transition);
        $element.one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend',
        function() {
            //$element.removeClass("animated "+transition);
            setTimeout(function() {
                //$element.removeClass("animated "+transition);
            }, 1000);
        });
    }

    /**
     * Remove any lingering animations classes.
     */
    var alltransitions = "fadeInLeft fadeInRight fadeOutLeft fadeOutRight";
    function clean_up_animations($element) {
        $element.find(".animated").removeClass("animated "+alltransitions);
    }

    /**
     * Initialize leanSlider instance.
     */
    var slideDuration = 9000;
    var slider = $('#slider').leanSlider({
        pauseTime: slideDuration,
        directionNav: '#slider-direction-nav',
        controlNav: '#slider-control-nav',
        pauseOnHover: false,
        beforeChange: function(currentSlide) {
            $slide = $(this).find(".current");
            animate_slider_elements($slide, "hide");
        },
        afterChange: function(currentSlide) {
            $slide = $(this).find(".current");
            animate_slider_elements($slide, "show");
        }
    });

    /**
     * Handle animations for the first slide.
     */
    animate_slider_elements($('#slider').find(".current"));

});