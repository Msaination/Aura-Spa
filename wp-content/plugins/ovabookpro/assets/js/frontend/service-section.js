(function ($) {

	window.OBP_Frontend_Service_Section = {
		init: function() {
            this.part_services();
            this.section_toggle();
            this.search_service_handler();
		},
        search_service_handler: function(){
            const that = this;
            $(".search-name-wrapper .bookproicon-search").off().on("click",function(){

                const $this = $(this);
                const vendorId = $this.closest(".service-wrap").find('input[name="service_vendor_id"]').val();
                const keyword = $('input.obp-search-name').val();
                const data = {
                    'action': 'obp_section_service_search',
                    'nonce': ajax_object.nonce,
                    'keyword': keyword,
                    'vendor_id': vendorId,
                };

                that.show_loader();

                $.ajax({
                    url: ajax_object.ajax_url,
                    complete: function( jqXHR, textStatus){
                        that.show_loader(false);
                    },
                    data: data,
                    method: 'POST',
                    type: 'POST',
                    error: function( jqXHR, textStatus, errorThrown){
                        console.error( errorThrown );
                    },
                    success: function(data){

                        $(".service-results").html('');
                        $(".service-results").html(data);
                        
                        that.section_toggle();
                        window.OBP_Booking.init();
                    }
                });
            });
        },

        section_toggle: function(){
            $(document).find('.service-wrap .service-section').on('click', function(e) {
                var that = $(this);
                that.toggleClass('toggled');
                that.next().toggleClass('hide-items');
            });
        },

        part_services: function() { 
            const that = this;

            $(document).find('.single-business-part.service-wrap').each( function(){
                const that  = $(this);
                const input = that.find('.obp-search-name');

                const wrap_results = that.find('.service-results');
                const vendor_id = that.find('input[name="service_vendor_id"]').val();

                input.on('keypress', function(e) {
                    const key = e.which;

                    if ( key == 13 ) {
                        $(".search-name-wrapper .bookproicon-search").trigger("click");
                    }

                });
            });
        },

        show_loader( flag = true, container = '.service-results' ){
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
	};


    OBP_Frontend_Service_Section.init();
 
    
})(jQuery);