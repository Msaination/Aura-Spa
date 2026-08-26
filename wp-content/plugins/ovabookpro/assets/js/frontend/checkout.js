(function ($) {

	window.obp_checkout_form = function( result = null ){
		const order_id = result?.order_id;
		const data = {
			'action': 'obp_checkout_form',
			'nonce': ajax_object.nonce,
			'order_id': order_id,
		};
        
        OBP_Booking_Dialog.close();

		$.ajax({
            url: ajax_object.ajax_url,
            complete: function( jqXHR, textStatus){
                OBP_Booking.show_loader(false);
            },
            data: data,
            method: 'POST',
            type: 'POST',
            error: function( jqXHR, textStatus, errorThrown){
                console.error( errorThrown );
            },
            success: function( data ){
                window.obp_checkout = new $.Zebra_Dialog(data,
                    {
                        custom_class: "obp_checkout_dialog",
                        type: false,
                        keyboard: false,
                        buttons: false,
                        width: 750,
                        backdrop_close: false,
                    }
                );
                obp_checkout_submit();
                obp_checkout_off_close();
                // init recaptcha
                if ( typeof obp_recaptcha !== 'undefined' && ! $.isEmptyObject( obp_recaptcha ) ) {
                    var callbackName = obp_recaptcha.type == 'v2' ? 'obpRecaptchaV2' : 'obpRecaptchaV3';
                    window[callbackName]();
                }
                // init countdown
                obp_countdown.init();
            }
        });
	}

    window.obp_checkout_off_close = function(){
        $('.obp_checkout_dialog .ZebraDialog_Close').off().on('click', function(e){
            e.preventDefault();
            new $.Zebra_Dialog(obp_checkout_object.discard_message,{
                type: "question",
                custom_class: "obp_booking_discard",
                title: obp_checkout_object.discard_title,
                buttons: [
                    {
                        caption: obp_checkout_object.continue_booking,
                        callback: function() {
                        }
                    },
                    {
                        caption: obp_checkout_object.yes_discard,
                        callback: function() {
                            // empty cart
                            var data = {
                                'action': 'obp_booking_empty_cart',
                                'nonce': ajax_object.nonce,
                            };

                            $('.obp_checkout_wrapper').block({
                                message: null,
                                overlayCSS:  { 
                                    backgroundColor: '#fff', 
                                    opacity: 0.3, 
                                    cursor: null 
                                },
                            });

                            $.post( ajax_object.ajax_url, data, function(res){
                                obp_checkout.close();
                                location.reload();
                            } );
                            
                        }
                    },
                ],
            });
        });
    }

    window.obp_checkout_submit = function(){
        $('.obp_checkout_form').off().on('submit', function(e){
            e.preventDefault();
            const $this = $(this);
            const order_id = $this.find('input[name="order_id"]').val();
            const full_name = $this.find('input[name="full_name"]').val();
            const phone_number = $this.find('input[name="phone_number"]').val();
            const email = $this.find('input[name="customer_email"]').val();
            const customer_note = $this.find('textarea[name="customer_note"]').val();
            const payment = $this.find('input[name="payment"]:checked').val();

            var phoneRegex  = XRegExp(/^\+?\s?\(?(\d{1,4})?\)?\s?\-?\.?(\d{1,5})?\s?\-?\.?(\d{1,4})$/);
            var emailRegex  = XRegExp(/^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i);

            $('.obp_checkout_message').html('');

            var messages = '';
            const messageErrorHTML = '<div class="obp_alert_danger" role="alert">[message]</div>';

            if ( full_name == '' ) {
                messages += messageErrorHTML.replace( "[message]", obp_checkout_object.full_name_req );
            }

            if ( phone_number == '' ) {
                messages += messageErrorHTML.replace( "[message]", obp_checkout_object.phone_req );
            } else if( ! XRegExp.exec( phone_number, phoneRegex ) ) {
                messages += messageErrorHTML.replace( "[message]", obp_checkout_object.phone_invalid );
            }

            if ( email == '' ) {
                messages += messageErrorHTML.replace( "[message]", obp_checkout_object.email_req );
            } else if( ! XRegExp.exec( email, emailRegex ) ) {
                messages += messageErrorHTML.replace( "[message]", obp_checkout_object.email_invalid );
            }

            if ( $('.obp-recaptcha-wrapper').length ) {
                var response = grecaptcha.getResponse();
                if( response.length == 0 ){
                    messages += messageErrorHTML.replace( "[message]", obp_checkout_object.recaptcha_invalid );
                }
            } else if ( $('input[name="g-recaptcha-response"]').length ) {
                if ( ! $('input[name="g-recaptcha-response"]').val() ) {
                    messages += messageErrorHTML.replace( "[message]", obp_checkout_object.recaptcha_invalid );
                }
            }
            

            if ( messages != '' ) {

                $(".obp_checkout_message").append( messages );

                $('html, body').animate({
                    scrollTop: $(".obp_checkout_message").offset().top - 100
                }, 1000);
                return false;
            }

            const data = {
                'action': 'obp_checkout_submit',
                'nonce': ajax_object.nonce,
                'order_id': order_id,
                'full_name': full_name,
                'phone_number': phone_number,
                'email': email,
                'customer_note': customer_note,
                'payment': payment,
            };

            OBP_Booking.show_loader( true, '.obp_checkout_form' );

            $.ajax({
                url: ajax_object.ajax_url,
                complete: function( jqXHR, textStatus){
                    OBP_Booking.show_loader( false, '.obp_checkout_form' );
                },
                data: data,
                method: 'POST',
                type: 'POST',
                error: function( jqXHR, textStatus, errorThrown){
                    console.error( errorThrown );
                },
                success: function( res ){

                    if ( res.status == 'error' ) {
                        $('.obp_checkout_form').block({
                            message: res.message,
                            overlayCSS:  { 
                                backgroundColor: '#eee', 
                                opacity: 0.3,
                                cursor: null
                            },
                        });

                        setInterval(function() {
                            location.reload();
                        }, 3000);

                        return false;
                    } else {
                        $('.obp_checkout_form').unblock();
                    }

                    if ( res?.callback ) {
                        window[res.callback](res?.data);
                        return false;
                    }

                    if ( res?.url ) {
                        window.location.href = res.url;
                    }
                    return false;
                }
            });

        });   
    }

})(jQuery);