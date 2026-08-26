(function ($) {

	window.OBP_Frontend_Type = {
		init: function() {

            this.add_new();

            this.show_edit_type();

            this.delete_type();

		},

        remove_edit_type: function(){
            $('.obp_close_edit_type').off().on('click', function(e){
                e.preventDefault();
                const wrapper = $(this).closest('.obp_edit_type_wrapper');
                $('.obp_edit_type_row').removeClass('is-active');
                $('.obp_edit_type_info').removeClass( 'border-0' );
                wrapper.html('');
            });
        },

        add_new: function(){

            const that = this;

            $('input[name="obp_add_type"]').off().on("click",function(e){
                e.preventDefault();

                const data = {
                    'action': 'obp_add_type',
                    'nonce': ajax_object.nonce,
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
                        new $.Zebra_Dialog(data, {
                            buttons: false,
                            type: false,
                            width: 600,
                            custom_class: 'obp_add_type_modal'
                        });
                        that.save_add_new();

                    }
                });

            });
        },

        save_edit_type: function(){
            const that = this;
            $(".obp_edit_type_form").off().on("submit",function(e){
                e.preventDefault();

                const $this = $(this);
                const typeId = $this.find('input[name="type_id"]').val();
                const type_name = $this.find('#name_type').val();


                $(".obp_message_wrapper").html('');

                var messages = '';
                const messageErrorHTML = '<div class="obp_alert_danger" role="alert">[message]</div>';

                if ( type_name == '' ) {
                    messages += messageErrorHTML.replace( "[message]", obp_type_obj.name_req );
                }

                if ( messages != '' ) {
                    $(".obp_message_wrapper").append( messages );

                    $('html, body').animate({
                        scrollTop: $(".obp_message_wrapper").offset().top - 100
                    }, 1000);
                    return false;
                }

                that.show_loader(true);

                const data = {
                    'action': 'obp_save_edit_type',
                    'nonce': ajax_object.nonce,
                    'type_id': typeId,
                    'type_name': type_name,
                };

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

            });
        },

        save_add_new: function(){
            const that = this;
            $(".obp_add_type_form").off().on("submit", function(e){
                e.preventDefault();
                const $this = $(this);
                const type_name = $this.find('#name_type').val();
                

                $(".obp_form_messages").html('');

                var messages = '';
                const messageErrorHTML = '<div class="obp_alert_danger" role="alert">[message]</div>';

                if ( type_name == '' ) {
                    messages += messageErrorHTML.replace( "[message]", obp_type_obj.name_req );
                }

                if ( messages != '' ) {
                    $(".obp_form_messages").append( messages );
                    return false;
                }

                const data = {
                    'action': 'obp_save_new_type',
                    'nonce': ajax_object.nonce,
                    'type_name': type_name,

                };

                that.show_loader(true, '.obp_add_type_form');

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
            });
        },

        show_edit_type: function(){
            const that = this;

            $(".obp_show_edit_type").off().on("click", function(e){
                e.preventDefault();
                const $this = $(this)
                const action_wrap = $this.closest(".obp_type_action_wrapper");
                const typeId = action_wrap.find('input[name="type_id"]').val();


                $(".obp_edit_type_wrapper").html('');

                const data = {
                    'action': 'obp_show_edit_type',
                    'nonce': ajax_object.nonce,
                    'type_id': typeId,
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
                        $('.obp_edit_type_info[data-id="'+typeId+'"]').addClass( 'border-0' );
                        $('.obp_edit_type_row[data-id="'+typeId+'"]').addClass( 'is-active' );
                        $('.obp_edit_type_wrapper[data-id="'+typeId+'"]').html(data);
                        that.save_edit_type();
                        that.remove_edit_type();
                    }
                });

            });
        },

        delete_type: function(){
            const that = this;

            $(".obp_delete_type").off().on("click",function(e){
                e.preventDefault();
                const $this = $(this);
                const action_wrap = $this.closest(".obp_type_action_wrapper");

                const typeId = action_wrap.find('input[name="type_id"]').val();
                

                const data = {
                    'action': 'obp_delete_type',
                    'nonce': ajax_object.nonce,
                    'type_id': typeId,
                };


                new $.Zebra_Dialog(obp_type_obj.confirm_delete,
                    {
                        type: "question",
                        title: null,
                        buttons: [
                            {caption: obp_type_obj.yes, callback: function() {
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
                                        window.location.reload(true);
                                    }
                                });
                            }},
                            {caption: obp_type_obj.no, callback: function() {

                            }},
                        ]
                    }
                );

                
            });
        },

        show_loader( flag = true, container = '.obp-content-type' ){
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

    
    OBP_Frontend_Type.init();
    
})(jQuery);