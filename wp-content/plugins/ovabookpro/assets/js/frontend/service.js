(function ($) {

	OBP_Frontend_Service = {
		init: function() {
			// Color
            this.color_picker();

            this.add_new_type();

            // Save service
			this.save_service();

			// Delete service
			this.delete_service();

            // Input price change
            this.input_price_change();
            this.input_date();
            this.check_all_staff();

            this.filter();
            this.pagination();

            this.show_add_staff();

            this.change_price_type();

            this.add_extra_option();

            this.obp_remmove_package_group();

            this.add_package_option();

            this.remove_package_item();

            this.package_item_init();

            this.clear_service_sale();
		},

        save_staff: function(){
            const that = this;
            $(document).find('.obp_add_staff_wrapper .obp_update_staff').off().on('click', function(e) {
                e.preventDefault();

                const container = $('.obp_add_staff_wrapper');

                container.find(".obp_messages").html('');

                var staff_ids = [];
                // add staff id to staff ids
                $('.service_staff_wrapper input[name="staff_id"]:checked').each( function(i,el){
                    staff_ids.push( $(el).val() );
                } );


                const avatar      = container.find('input[name="staff_avatar"]').val();
        
                const username    = container.find('input[name="username"]').val();

                const email       = container.find('input[name="email"]').val();
                const first_name  = container.find('input[name="first_name"]').val();
                const last_name   = container.find('input[name="last_name"]').val();
                const nickname    = container.find('input[name="nickname"]').val();
                const position    = container.find('input[name="position"]').val();
                const role        = container.find('select[name="staff_role"]').val();
                const description = container.find('textarea[name="description"]').val();

                const password = container.find('input[name="password"]').val();

                const phoneRegex  = XRegExp(/^\+?\s?\(?(\d{1,4})?\)?\s?\-?\.?(\d{1,5})?\s?\-?\.?(\d{1,4})$/);
                const emailRegex  = XRegExp(/^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i);

                var messages = '';
                const messageErrorHTML = '<div class="obp_alert_danger" role="alert">[message]</div>';
   
                if ( username == '' ) {
                    messages += messageErrorHTML.replace( "[message]", obp_staff_obj.username_req );
                }

                if ( email == '' ) {
                    messages += messageErrorHTML.replace( "[message]", obp_staff_obj.email_req );

                } else if( ! XRegExp.exec( email, emailRegex ) ) {
                    messages += messageErrorHTML.replace( "[message]", obp_staff_obj.email_invalid );
                }

                if ( nickname == '' ) {
                    messages += messageErrorHTML.replace( "[message]", obp_staff_obj.nickname_req );
                }

                if ( password == '' && container.find('input[name="password"]:required').length ) {
                    messages += messageErrorHTML.replace( "[message]", obp_staff_obj.password_req );
                }

                if ( ! role ) {
                    messages += messageErrorHTML.replace( "[message]", obp_staff_obj.role_req );
                }

                const data = {
                    'action': 'obp_save_staff_service',
                    'nonce': ajax_object.nonce,
                    'staff_ids': staff_ids,
                    'username': username,
                    'avatar': avatar,
                    'email': email,
                    'first_name': first_name,
                    'last_name': last_name,
                    'nickname': nickname,
                    'position': position,
                    'description': description,
                    'role': role,
                    'password': password,
                };

                if ( messages != '' ) {
                    container.find(".obp_messages").append( messages );

                    $('html, body').animate({
                        scrollTop: container.find(".obp_messages").offset().top - 100
                    }, 1000);
                    return false;
                }

                that.show_loader( true, '.obp_add_staff_wrapper' );

                $.ajax({
                    url: ajax_object.ajax_url,
                    complete: function( jqXHR, textStatus){
                        that.show_loader(false, '.obp_add_staff_wrapper');
                    },
                    data: data,
                    method: 'POST',
                    type: 'POST',
                    error: function( jqXHR, textStatus, errorThrown){
                        console.error( errorThrown );
                    },
                    success: function(data){
                        var data = JSON.parse( data );

                        if ( data?.mess ) {
                            container.find(".obp_messages").html(`<div class="obp_alert_danger" role="alert">${data?.mess}</div>`);
                            return false;
                        }

                        if ( data?.html ) {
                            $('.service_staff_wrapper').html('');
                            $('.service_staff_wrapper').html( data?.html );
                        }
                        // init events
                        that.check_all_staff();
                        // close modal
                        obp_add_staff_modal.close();
                    }
                });
    
            });
        },

        avatar_staff: function(){
            var staff_avatar;
            $(document).on('click', '.staff_avatar .opb_button_add_media', function(e) {
                e.preventDefault();

                if (typeof staff_avatar != 'undefined') {
                    staff_avatar.close();
                }

                var that = $(this);

                staff_avatar = wp.media({
                    title: $(this).data('uploader-title'),
                    button: {
                        text: $(this).data('button-text'),
                    },
                    library: {
                        type: ['image']
                    },
                    multiple: false
                });

                staff_avatar.on('select', function() {
                    var selection = staff_avatar.state().get('selection');

                    selection.map(function(attachment, i) {
                        attachment = attachment.toJSON();
                        that.closest('.staff_avatar').find('.profile-image').html('<img src="' + attachment.sizes.full.url + '"><a href="#" class="remove_image"><i class="icon-close bookproicon-close"></i></a>');
                        that.closest('.staff_avatar').find('input').val(attachment.id);
                    });
                });

                staff_avatar.on('open',function() {
                    var selection = staff_avatar.state().get('selection');
                    var id = $('.staff_avatar input[name="staff_avatar"]').val();
                   
                    attachment = wp.media.attachment(id);
                    attachment.fetch();
                    selection.add(attachment ? [attachment] : []);
                });

                staff_avatar.open();
            });

            /* Remove */
            $(document).on('click', '.staff_avatar .remove_image', function(e) {
                e.preventDefault();
                $(this).closest('.staff_avatar').find('input').val('');
                $(this).parent().empty();
            });
        },

        show_add_staff: function(){
            var that = this;

            $('.obp_add_staff').off().on('click', function(e){
                e.preventDefault();

                var data = {
                    'action': 'obp_show_add_staff',
                    'nonce': ajax_object.nonce
                };

                that.show_loader();

                $.ajax({
                    url: ajax_object.ajax_url,
                    complete: function( jqXHR, textStatus ){
                        that.show_loader( false );
                    },
                    data: data,
                    method: 'POST',
                    type: 'POST',
                    error: function( jqXHR, textStatus, errorThrown ){
                        console.error( errorThrown );
                    },
                    success: function( data ){

                        window.obp_add_staff_modal = $.Zebra_Dialog(data, {
                            buttons: false,
                            type: false,
                            width: 800,
                            height: 800,
                            custom_class: 'obp_add_staff_modal'
                        });

                        // add avatar staff
                        that.avatar_staff();
                        // show hide password
                        OBP_Frontend.obp_show_hide_passsword();

                        that.save_staff();
                    }
                });

            });
        },

        reload_type_select_box: function( type_id ){
            var that = this;

            var data = {
                'action': 'obp_reload_type_select_box',
                'nonce': ajax_object.nonce,
                'type_id': type_id,
            };

            that.show_loader();

            $.ajax({
                url: ajax_object.ajax_url,
                complete: function( jqXHR, textStatus ){
                    that.show_loader( false );
                },
                data: data,
                method: 'POST',
                type: 'POST',
                error: function( jqXHR, textStatus, errorThrown){
                    console.error( errorThrown );
                },
                success: function( data ){
                    $('#service_type').html('');
                    $('#service_type').html( data );
                }
            });

        },

        save_type: function(){
            const that = this;
            $(".obp_add_type_form").off().on("submit", function(e){
                e.preventDefault();
                const $this = $(this);
                const type_name = $this.find('#name_type').val();
                

                $(".obp_form_messages").html('');

                var messages = '';
                const messageErrorHTML = '<div class="obp_alert_danger" role="alert">[message]</div>';

                if ( type_name == '' ) {
                    messages += messageErrorHTML.replace( "[message]", obp_service_obj.name_req );
                }

                if ( messages != '' ) {
                    $(".obp_form_messages").append( messages );
                    return false;
                }

                const data = {
                    'action': 'obp_save_type_service',
                    'nonce': ajax_object.nonce,
                    'type_name': type_name,
                };

                that.show_loader(true, '.obp_add_type_form');

                $.ajax({
                    url: ajax_object.ajax_url,
                    complete: function( jqXHR, textStatus){
                        that.show_loader(false, '.obp_add_type_form');
                    },
                    data: data,
                    method: 'POST',
                    type: 'POST',
                    error: function( jqXHR, textStatus, errorThrown){
                        console.error( errorThrown );
                    },
                    success: function( type_id ){
                        // close modal
                        obp_type_modal.close();
                        // reload service type
                        that.reload_type_select_box( type_id );
                    }
                });
            });
        },

        add_new_type: function(){
            const that = this;

            $('.add_new_type').off().on("click",function(e){
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
                    success: function( data ){

                        window.obp_type_modal = $.Zebra_Dialog(data, {
                            buttons: false,
                            type: false,
                            width: 600,
                            custom_class: 'obp_add_type_modal'
                        });

                        that.save_type();
                    }
                });

            });
        },

        clear_service_sale: function(){
            $('.obp_clear_service_sale').off().on('click', function(e){
                e.preventDefault();

                $('#service_sale_price').val('');
                service_flatpickr.clear();
                $('#service_sale_off_from').val('');
                $('#service_sale_off_to').val('');
            });
        },

        package_item_init: function(){
            $('input[name="package_price"]').off().on("change",function(){
                var decimailPoint = currency_object.decimal_separator;
                var regex = new RegExp(
                    '[^-0-9%\\' + decimailPoint + ']+',
                    'gi'
                );
                var decimalRegex = new RegExp(
                    '\\' + decimailPoint + '+',
                    'gi'
                );
                var value = $(this).val();
                var newvalue = value
                    .replace( regex, '' )
                    .replace( decimalRegex, decimailPoint );

                if ( value !== newvalue ) {
                    $( this ).val( newvalue );
                }
            });
        },

        remove_package_item: function(){
            const that = this;
            $('.obp_remove_package').off().on('click', function(e){
                e.preventDefault();
                const $this = $(this);
                const item = $this.closest('.obp_package_item');
                item.remove();
            });
        },

        add_package_option: function(){
            const that = this;
            $('.obp_add_option').off().on('click', function(){
                const $this = $(this);
                const container = $this.closest('.obp_service_package_group').find('.obp_body_container');

                const data = {
                    'action': 'obp_add_package_option',
                    'nonce': ajax_object.nonce
                };

                that.show_loader();

                $.ajax({
                    url: ajax_object.ajax_url,
                    complete: function( jqXHR, textStatus ){
                        that.show_loader( false );
                    },
                    data: data,
                    method: 'POST',
                    type: 'POST',
                    error: function( jqXHR, textStatus, errorThrown ){
                        console.error( errorThrown );
                    },
                    success: function( data ){
                        container.append( data );
                        that.remove_package_item();
                        that.package_item_init();
                    }
                });
            });
        },

        add_extra_option: function(){
            const that = this;
            $('#obp_add_extra_option').off().on("click", function(e){
                
                const data = {
                    'action': 'obp_add_extra_option',
                    'nonce': ajax_object.nonce
                };

                that.show_loader();

                $.ajax({
                    url: ajax_object.ajax_url,
                    complete: function( jqXHR, textStatus ){
                        that.show_loader( false );
                    },
                    data: data,
                    method: 'POST',
                    type: 'POST',
                    error: function( jqXHR, textStatus, errorThrown){
                        console.error( errorThrown );
                    },
                    success: function( data ){
                        $('.obp_service_package_group_container').append( data );
                        that.obp_remmove_package_group();
                        that.add_package_option();
                    }
                });
            });
        },

        obp_remmove_package_group: function(){
            $('.obp_remmove_package_group').off().on('click', function(e){
                e.preventDefault();
                const item = $(this).closest('.obp_service_package_group');
                item.remove();
            });
        },

        change_price_type: function(){
            $('select[name="service_price_type"]').on('change', function(){
                const val = $(this).val();
                const price_input   = $('input[name="service_price"]');
                const note_price    = $('.note_price_wrapper');
                const package_container = $('.obp_service_extra_option');
                switch( val ) {
                    case 'free':
                        price_input.prop( 'disabled', true );
                        price_input.val('0');
                        note_price.slideUp();
                        package_container.slideUp();
                    break;
                    case 'fixed':
                        price_input.prop( 'disabled', false );
                        note_price.slideUp();
                        package_container.slideUp();
                    break;
                    case 'start_at':
                        price_input.prop( 'disabled', false );
                        note_price.slideUp();
                        package_container.slideDown();
                        // code block
                    break;
                    case 'varies':
                        price_input.prop( 'disabled', true );
                        price_input.val('');
                        note_price.slideDown();
                        package_container.slideUp();
                        // code block
                    break;
                    case 'not_show':
                        price_input.prop( 'disabled', false );
                        note_price.slideUp();
                        package_container.slideUp();
                    break;
                default:
                }
            });

            const current_val = $('select[name="service_price_type"]').val();
            const price_input   = $('input[name="service_price"]');
            const note_price    = $('.note_price_wrapper');
            const package_container = $('.obp_service_extra_option');

            switch( current_val ) {
                case 'free':
                    price_input.prop( 'disabled', true );
                    price_input.val('0');
                    note_price.slideUp();
                    package_container.slideUp();
                break;
                case 'fixed':
                    price_input.prop( 'disabled', false );
                    note_price.slideUp();
                    package_container.slideUp();
                break;
                case 'start_at':
                    price_input.prop( 'disabled', false );
                    note_price.slideUp();
                    package_container.slideDown();
                    // code block
                break;
                case 'varies':
                    price_input.prop( 'disabled', true );
                    price_input.val('');
                    note_price.slideDown();
                    package_container.slideUp();
                    // code block
                break;
                case 'not_show':
                    price_input.prop( 'disabled', false );
                    note_price.slideUp();
                    package_container.slideUp();
                break;
            default:
            }
        },

        color_picker: function() {
            if ( $('.obp-content-service input[name="service_color"]').length > 0 ) {
                $('.obp-content-service input[name="service_color"]').wpColorPicker();
            }
        },

        save_service: function() {
            const that = this;
            $(document).find('input[name="obp_update_service"]').off().on('click', function(e) {
                e.preventDefault();

                $(".obp_message_wrapper").html('');

                const post_id        = $('.obp-content-service #post_id').val();
                const service_name   = $('.obp-content-service input[name="service_name"]').val();

                const type          = $('.obp-content-service select[name="service_type"]').val();
                const hour   		= $('.obp-content-service select[name="service_hour"]').val();
                const minute 		= $('.obp-content-service select[name="service_minute"]').val();
                const price_type  = $('.obp-content-service select[name="service_price_type"]').val();
                const price       = $('.obp-content-service input[name="service_price"]').val();
                const color       = $('.obp-content-service input[name="service_color"]').val();
                const sale_price  = $('.obp-content-service input[name="service_sale_price"]').val();
                const sale_off_start_date     = $('.obp-content-service input[name="service_sale_off_start_date"]').val();
                const sale_off_end_date       = $('.obp-content-service input[name="service_sale_off_end_date"]').val();
                const sale_off_from           = $('.obp-content-service input[name="service_sale_off_from"]').val();
                const sale_off_to             = $('.obp-content-service input[name="service_sale_off_to"]').val();

                var description = tinyMCE.get('service_description') ? tinyMCE.get('service_description').getContent() : $('#service_description').val();



                const staff_ids   = [];
                const use_on = $('.obp-content-service select[name="use_on"]').val();
                var check_service_time = true;
                var check_package_name = true;
                var check_package_time = true;
                var check_package_label = true;
                var packages = [];
                const note_price = $('.obp-content-service textarea[name="note_price"]').val();
                const tax_class = $('.obp-content-service select[name="tax_class"]').val();



                $('.obp-content-service').find('.obp-check-box-list-wrapper .check-box-list').each(function() {
                    var staff_id = $(this).find('input[type="checkbox"]:checked').val() || '';
                    if( staff_id != '') {
                        staff_ids.push( staff_id );
                    }
                });

                if ( minute == '0' && hour == '0' ) {
                    check_service_time = false;
                }

                if ( price_type == 'start_at' ) {
                    $('.obp_service_package_group').each( function(i,el){
                        const package_type = $(el).find('select[name="package_type"]').val();
                        const package_label = $(el).find('input[name="package_label"]').val();
                        let package_item = {};
                        let data_package = [];
                        if ( package_label == '' ) {
                            check_package_label = false;
                        }
                        package_item['type'] = package_type;
                        package_item['label'] = package_label;

                        $(el).find('.obp_package_item').each( function(j,elm){
                            const package_id = $(elm).find('input[name="package_id"]').val();
                            const package_name = $(elm).find('input[name="package_name"]').val();
                            
                            const package_hours = $(elm).find('select[name="package_hours"]').val();
                            const package_minutes = $(elm).find('select[name="package_minutes"]').val();
                            const package_price = $(elm).find('input[name="package_price"]').val();
                            let data_package_item = {};
                            if ( package_name == '' ) {
                                check_package_name = false;
                            }

                            if ( package_hours == '0' && package_minutes == '0' ) {
                                check_package_time = false;
                            }
                            data_package_item['id'] = package_id;
                            data_package_item['name'] = package_name;
                            data_package_item['label'] = package_label;
                            data_package_item['hours'] = package_hours;
                            data_package_item['minutes'] = package_minutes;
                            data_package_item['price'] = package_price;
                            data_package.push( data_package_item );
                        } );
                        package_item['data'] = data_package;
                        packages.push( package_item );
                    } );
                }

                var messages = '';
                const messageErrorHTML = '<div class="obp_alert_danger" role="alert">[message]</div>';

                if ( service_name == '' ) {
                    messages += messageErrorHTML.replace( "[message]", obp_service_obj.name_req );
                }

                if ( check_service_time == false ) {
                    messages += messageErrorHTML.replace( "[message]", obp_service_obj.service_time_req );
                }

                if ( check_package_name == false ) {
                    messages += messageErrorHTML.replace( "[message]", obp_service_obj.package_name_req );
                }

                if ( check_package_label == false ) {
                    messages += messageErrorHTML.replace( "[message]", obp_service_obj.package_label_req );
                }

                if ( check_package_time == false ) {
                    messages += messageErrorHTML.replace( "[message]", obp_service_obj.package_time_req );
                }

                if ( price && sale_price && parseFloat( price ) - parseFloat( sale_price ) < 0 ) {
                    messages += messageErrorHTML.replace( "[message]", obp_service_obj.price_invalid );
                }

                if ( messages != '' ) {
                    $(".obp_message_wrapper").append( messages );

                    $('html, body').animate({
                        scrollTop: $(".obp_message_wrapper").offset().top - 100
                    }, 1000);
                    return false;
                }

                const data = {
                    'action': 'obp_save_edit_service',
                    'nonce': ajax_object.nonce,
                    'id': post_id,
                    'service_name': service_name,
                    'type': type,
                    'hour': hour,
                    'minute': minute,
                    'price_type': price_type,
                    'price': price,
                    'sale_price': sale_price,
                    'sale_off_start_date': sale_off_start_date,
                    'sale_off_end_date': sale_off_end_date,
                    'sale_off_from': sale_off_from,
                    'sale_off_to': sale_off_to,
                    'description': description,
                    'color': color,
                    'staff_ids': staff_ids,
                    'use_on': use_on,
                    'packages': packages,
                    'note_price': note_price,
                    'tax_class': tax_class,
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

                        if ( data.redirect == true ) {
                            window.location.replace( data.url );
                        } else {
                            window.location.reload( true );
                        }
                    }
                });
            });
        },

        check_all_staff: function(){
            $('.check_all_staff').on('change', function(){
                $(this).closest('.staff_group_item').find('input:checkbox').not(this).prop('checked', this.checked);
            } );
        },

        filter: function(){
            const that = this;
            $(".search-name-wrapper .bookproicon-search").off().on("click",function(){

                const name = $('.search-name-wrapper input.obp-search-name').val();
                const sortby = $('.obp-order #post_orderby').val();

                const data = {
                    'action': 'obp_filter_service',
                    'nonce': ajax_object.nonce,
                    'name': name,
                    'sortby': sortby
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
                        $(".obp_service_table_container").html('');
                        $(".obp-pagination-wrap").html('');

                        $(".obp_service_table_container").html(data?.service_html);
                        $(".obp-pagination-wrap").html(data?.pagination_html);

                        that.init_events();
                    }
                });

            });


            $(".search-name-wrapper .obp-search-name").on("keypress", function(e){
                if ( e.which == 13 ) {
                    $(".search-name-wrapper .bookproicon-search").trigger("click");
                }
            });
        },

        pagination: function(){
            const that = this;

            $(".service-pagination .page_item").off().on("click",function(e){
                e.preventDefault();

                const name      = $('.search-name-wrapper input.obp-search-name').val();
                const sortby    = $('.obp-order #post_orderby').val();
                const page      = $(this).attr("data-page");

                const data = {
                    'action': 'obp_filter_service',
                    'nonce': ajax_object.nonce,
                    'name': name,
                    'sortby': sortby,
                    'page': page
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
                        $(".obp_service_table_container").html('');
                        $(".obp-pagination-wrap").html('');

                        $(".obp_service_table_container").html(data?.service_html);
                        $(".obp-pagination-wrap").html(data?.pagination_html);

                        that.init_events();
                    }
                });
            });
        },

        init_events: function(){
            this.pagination();
            this.delete_service();
        },

        delete_service: function() {
            const that = this;
            $(".obp_remove_service").off().on("click",function(e){
                e.preventDefault();

                const wrapper = $(this).closest(".service_action_wrapper");
                const service_id = wrapper.find('input[name="service_id"]').val();

                const data = {
                    'action': 'obp_delete_service',
                    'nonce': ajax_object.nonce,
                    'service_id': service_id
                };

                new $.Zebra_Dialog(obp_service_obj.confirm_delete,
                    {
                        type: "question",
                        title: null,
                        buttons: [
                            {caption: obp_service_obj.yes, callback: function() {
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
                            {caption: obp_service_obj.no, callback: function() {

                            }},
                        ]
                    }
                );
            });
        },

        show_loader( flag = true, container = '.obp-content-service' ){
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

        input_price_change: function(){
            $("#service_price").off().on("change",function(){
                var decimailPoint = currency_object.decimal_separator;
                var regex = new RegExp(
                    '[^0-9\\' + decimailPoint + ']+',
                    'gi'
                );
                var decimalRegex = new RegExp(
                    '\\' + decimailPoint + '+',
                    'gi'
                );
                var value = $( this ).val();
                var newvalue = value
                    .replace( regex, '' )
                    .replace( decimalRegex, decimailPoint );

                if ( value !== newvalue ) {
                    $( this ).val( newvalue );
                }
            });

            $("#service_sale_price").off().on("change",function(){
                var decimailPoint = currency_object.decimal_separator;
                var regex = new RegExp(
                    '[^0-9\\' + decimailPoint + ']+',
                    'gi'
                );
                var decimalRegex = new RegExp(
                    '\\' + decimailPoint + '+',
                    'gi'
                );
                var value = $( this ).val();
                var newvalue = value
                    .replace( regex, '' )
                    .replace( decimalRegex, decimailPoint );

                if ( value !== newvalue ) {
                    $( this ).val( newvalue );
                }
            });
        },

        input_date(){

            var isRTL = $("body").hasClass("rtl");

            window.service_flatpickr = $("#service_sale_off_start_date").flatpickr({
                'locale': obp_flatpickr_obj?.lang,
                "plugins": [new rangePlugin({
                    'input': "#service_sale_off_end_date",
                })],
                'disableMobile': true,
                'altInput': true,
                'dateFormat': "Y-m-d",
                'altFormat': calendar_object?.date_format,
                onChange: function(selectedDates) {
                    const dateArr = selectedDates.map(date => this.formatDate(date, calendar_object?.date_format ));
                    var [startDate, endDate] = dateArr;
                    $('#service_sale_off_start_date').val( startDate );
                    $('#service_sale_off_end_date').val( endDate );
                },
            });

            var orientation = isRTL ? 'r' : 'l';

            $('#service_sale_off_from').timepicker({
                'timeFormat': calendar_object.time_format,
                'disableTextInput': true,
                'orientation': orientation,
                'step': 15,
                'noneOption': true,
            });

            $('#service_sale_off_to').timepicker({
                'timeFormat': calendar_object.time_format,
                'disableTextInput': true,
                'orientation': orientation,
                'step': 15,
                'noneOption': true,
            });

        }
        
	};

	$(document).ready( function () {
        OBP_Frontend_Service.init();
    });
    
})(jQuery);