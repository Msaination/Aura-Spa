(function ($) {

	OBP_Frontend_Wishlist = {
		init: function() {
			this.remove_wishlist();
		},

        show_loader( flag = true, container = '.obp-content-my-wishlist' ){
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
		
        remove_wishlist: function() {
            const that = this; 
            $(document).find('.obp-content-my-wishlist .remove-wishlist').off().on('click', function(e) {
                e.preventDefault();

                var business_id = $(this).attr('data-id');

                const data = {
                    'action': 'obp_remove_wishlist',
                    'nonce': ajax_object.nonce,
                    'business_id': business_id,
                };

                new $.Zebra_Dialog(obp_wishlist_obj.confirm_delete,
                    {
                        type: "question",
                        title: null,
                        buttons: [
                            {caption: obp_wishlist_obj.yes, callback: function() {
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
                                        window.location.reload( true );
                                    }
                                });
                            }},
                            {caption: obp_wishlist_obj.no, callback: function() {

                            }},
                        ]
                    }
                );

                
            });
        }
	};

	OBP_Frontend_Wishlist.init();
    
})(jQuery);