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
        // $('.windzfare_causes_carousel').owlCarousel({
        //     items: 3,
        //     loop: true,
        //     margin: 30,
        //     autoplay: false,
        //     dots: false,
        //     nav: true,
        //     navText: ['<i class="ion-ios-arrow-back"></i>', '<i class="ion-ios-arrow-forward"></i>'],
        //     center: false,
        //     responsiveClass: true,
        //     responsive: {
        //         0: {
        //             items: 1,
        //             nav: false,
        //             dots: true,
        //         },
        //         768: {
        //             items: 2,
        //             nav: false,
        //             dots: true,
        //         },
        //         992: {
        //             items: 2,
        //             nav: true,
        //             dots: false,
        //         },
        //         1200: {
        //             items: 3,
        //             nav: true,
        //             dots: false,
        //         }
        //     }
        // })

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

        // Active Donate value tab
        $(function(){
            $('.select_amount_box').on('click','.select_amount',function(){
                $('.select_amount.active').removeClass('active');
                $(this).addClass('active');
            });
        });


        $("input:radio[name=wp_donate_amount_field]").change(function() {
            let val = $(this).val();
            if(val == 'custom'){
                $(".wp_fare_amount").val('');
                $('.wp_fare_amount').focus();
            }else{
                $(".wp_fare_amount").val(val);
            }
        });


 	}); //end document ready function
	
})(jQuery);
