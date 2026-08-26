(function ($) {
    
	window.OBP_Frontend_Single_Business = {
		init: function() {
            this.single_business();
            this.load_google_map();

            this.pagination();
		},
        load_google_map: function(){
            let map;
            let marker;
            let infoWindow;
            if ( map_object.map_platform == 'google_map' && map_object.enable_map == 'yes' ) {
                async function initMap() {
                    // Request needed libraries.
                    const { Map } = await google.maps.importLibrary("maps");
                    const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

                    var lat = $('input[name="map_latitude"]').val();
                    var lng = $('input[name="map_longitude"]').val();
                    var placeAddress = $('input[name="business_google_map"]').val();
                    if ( ! lat ) {
                        lat = 40.730610;
                    } else {
                        lat = parseFloat( lat );
                    }

                    if ( ! lng ) {
                        lng = -73.935242;
                    } else {
                        lng = parseFloat( lng );
                    }

                    const position = {
                        'lat': lat, 'lng': lng
                    };

                    const map = new Map(document.getElementById("obp_enable_map"), {
                        center: position,
                        zoom: 15,
                        mapId: "SINGLE_BUSINESS",
                    });

                    const marker = new AdvancedMarkerElement({
                        map,
                        position: position,
                    });
                }

            initMap();
            }
        },

        show_loader( flag = true, container = 'body' ){
            if ( flag ) {
                $(container).block({
                    message: null,
                    overlayCSS: {
                        backgroundColor: '#fff',
                        opacity: '0.5',
                        cursor: null,
                    }
                });
            } else {
                $(container).unblock();
            }
        },

        pagination: function(){
            const that = this;
            $(document).find('.obp-single-business-wrap .obp_pagination_ajax .page-numbers').on('click', function(e) {
                e.preventDefault();
                var $this    = $(this);

                var current = $('.obp-single-business-wrap .obp_pagination_ajax .current').attr('data-paged');
                var paged   = $this.attr('data-paged');

                var business_id = $this.closest('.business-reviews-wrap').data('business_id');

                if ( current != paged ) {
                    $('.obp-single-business-wrap .obp_pagination_ajax .page-numbers').removeClass('current');
                    $this.addClass('current');

                    // scroll top
                    $('html, body').animate({
                        scrollTop: $('.obp-single-business-wrap .reviews-content').offset().top - 160
                    }, 600);
                }
            });
        },

        single_business: function() {
            const that = this;
            // Video
            $(document).find( '.obp-single-business-wrap .btn-video' ).each( function() {
                Fancybox.bind( '.btn-video', {} );
            });

            // Update Wishlist
            $(document).find('.obp-single-business-wrap .business-add-to-wishlist').on('click', function(e) {
                e.preventDefault();

                var status      = $(this).attr("data-status");
                var login_url   = $(this).attr("data-url");
                const $this     = $(this);

                if ( $this.hasClass("loading") ) {
                    return false;
                }

                if( status == 'logged-in' ) {
                    var business_id = $(this).attr("data-id");

                    $this.addClass("loading");

                    if ( $this.find('i').hasClass('bookproicon-like') ) {

                        $.post( ajax_object.ajax_url, {
                            'action': 'obp_remove_wishlist',
                            'nonce': ajax_object.nonce,
                            'business_id':  business_id,
                        }, function(response) {
                            $this.html( response );
                            $this.removeClass("loading");
                        });

                    } else {

                        $.post( ajax_object.ajax_url, {
                            'action': 'obp_add_wishlist',
                            'nonce': ajax_object.nonce,
                            'business_id': business_id,
                        }, function(response) {
                            $this.html( response );
                            $this.removeClass("loading");
                            OBP_Frontend.tooltip_init();
                        });
                    }

                } else {
                    window.location.href = login_url;
                }
            });

            // Gallery: main images carousel
            $(document).find('.obp-single-business-wrap .main-images-gallery').each( function() {
                var that    = $(this);
                var options = that.data('options') ? that.data('options') : {};

                if ( $('body').hasClass('rtl') ) {
                    options.rtl = true;
                }

                that.owlCarousel({
                    'autoWidth': options.autoWidth,
                    'margin': options.margin,
                    'items': options.items,
                    'loop': options.loop,
                    'autoplay': options.autoplay,
                    'autoplayTimeout': options.autoplayTimeout,
                    'center': options.center,
                    'lazyLoad': options.lazyLoad,
                    'nav': options.nav,
                    'dots': options.dots,
                    'autoplayHoverPause': options.autoplayHoverPause,
                    'slideBy': options.slideBy,
                    'smartSpeed': options.smartSpeed,
                    'rtl': options.rtl,
                    'navText':[
                        '<i aria-hidden="true" class="'+ options.nav_left +'"></i>',
                        '<i aria-hidden="true" class="'+ options.nav_right +'"></i>'
                    ],
                });

            });

            // Our works images
            $(document).find('.obp-single-business-wrap .works-images-gallery').each( function() {
                Fancybox.bind('.works-images-item', {
                    Image: {
                        zoom: false,
                    },
                });
            });

            // All our works images: click load more button
            $(document).find('.business-all-our-works-wrap .obp_button').on('click', function(e) {
                e.preventDefault();

                var that = $(this);

                var key              = Number( that.data('key') );
                var no_data          = that.data('no_data');
                var our_works_images = that.data('our_works_images');

                $.post( ajax_object.ajax_url, {
                    action: 'obp_our_works_load_more',
                    data: {
                        key: key,
                        our_works_images: our_works_images,
                    },
                }, function( response ) {
                    if( response.success ) {
                        $('.business-all-our-works-wrap .works-images-gallery').append( response['data'].html );
                        that.data('key', key + 1);
                    } else {
                        that.text( no_data );
                    }
                });
            });


            // Business description: show more
            $('.single-business-sidebar .description-wrap').each(function() {
                $(this).showMoreDescription();
            });
        },
	};

    OBP_Frontend_Single_Business.init();

})(jQuery);