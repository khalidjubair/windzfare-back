 /*
Theme Name: 
Version: 
Author: 
Author URI: 
Description: 
*/
/*	IE 10 Fix*/

(function ($) {
	'use strict';
	
	jQuery(document).ready(function () {
        
        // Causes Carousel
        $('.windzfare_causes_carousel').owlCarousel({
            items: 3,
            loop: true,
            margin: 30,
            autoplay: false,
            dots: false,
            nav: true,
            navText: ['<i class="ion-ios-arrow-back"></i>', '<i class="ion-ios-arrow-forward"></i>'],
            center: false,
            responsiveClass: true,
            responsive: {
                0: {
                    items: 1,
                    nav: false,
                    dots: true,
                },
                768: {
                    items: 2,
                    nav: false,
                    dots: true,
                },
                992: {
                    items: 2,
                    nav: true,
                    dots: false,
                },
                1200: {
                    items: 3,
                    nav: true,
                    dots: false,
                }
            }
        })

        // Urgent Cause Carousel
        $('.urgent_cause_carousel').owlCarousel({
            items: 1,
            loop: true,
            margin: 0,
            autoplay: false,
            dots: false,
            nav: true,
            navText: ['<i class="ion-ios-arrow-back"></i>', '<i class="ion-ios-arrow-forward"></i>'],
            center: false,
            responsiveClass: true,
            responsive: {
                0: {
                    items: 1,
                    nav: false,
                    dots: true,
                },
                768: {
                    items: 1,
                    nav: false,
                    dots: true,
                },
                992: {
                    items: 1,
                    nav: true,
                    dots: false,
                },
                1200: {
                    items: 1,
                    nav: true,
                    dots: false,
                }
            }
        })

        // Select2 JS
        $(".select_dropdown_value").select2();

		$(".select_dropdown_value-limit1").select2({
		  	maximumSelectionLength: 1
		});
		$(".select_dropdown_value-limit2").select2({
		  	maximumSelectionLength: 2
		});

        // Active Donate value tab
        $(function(){
            $('.select_amount_box').on('click','.select_amount',function(){
                $('.select_amount.active').removeClass('active');
                $(this).addClass('active');
            });
        });


        $("input:radio[name=donation_level_amount]").change(function() {
            
            var val = $(this).val();

            alert(val);


            if(val == 'custom'){
                $("#xs-donate-name-modal").val('');
            }else{
                $("#xs-donate-name-modal").val(val);
            }
        });


 	}); //end document ready function
	
})(jQuery);
