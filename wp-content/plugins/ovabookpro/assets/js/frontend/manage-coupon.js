(function ($) {

	window.OBP_Manage_Coupon = {

		init: function(){
			this.apply_services();
			this.date_init();
			this.submit_handler();
			this.regex_input_price();
			this.delete_coupon();
			this.discount_type_change();
			this.pagination();
			this.clear_date();
		},
		clear_date: function(){
			$('.obp_clear_date_coupon').off().on('click', function(e){
				e.preventDefault();
				coupon_date.clear();
				$('#from_time').val('');
				$('#to_time').val('');
			} );
		},
		apply_services: function(){
			const applyTo = $('input[name="apply_to"]:checked').val();

			if ( applyTo == 'custom_services' ) {
				$(".obp_service_container").slideDown();
			}

			$('input[name="apply_to"]').on('change',function(){
				const val = $(this).val();

				if ( val == 'custom_services' ) {
					$(".obp_service_container").slideDown();
				} else {
					$(".obp_service_container").slideUp();
				}
			});
		},
		discount_type_change: function(){
			$('select[name="discount_type"]').on('change', function(){
				$('input[name="amount"]').val('');
			});
		},
		regex_input_price: function(){

			$('input[name="amount"]').on("change",function(){
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

                const discountType = $('select[name="discount_type"]').val();

                if ( discountType == 'percent' && parseFloat( newvalue ) > 100 ) {
                	$( this ).val('');
                	return false;
                }

                if ( value !== newvalue ) {
                    $( this ).val( newvalue );
                }
            });

            $('input[name="order_from"]').on("change",function(){
                var decimailPoint = currency_object.decimal_separator;
                var regex = new RegExp(
                    '[^-0-9%\\' + decimailPoint + ']+',
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
		init_events: function(){
			this.pagination();
			this.delete_coupon();
		},
		date_init: function(){

			var defaultDate = [];

			if ( $("#start_date").val() != '' ) {
				defaultDate.push( $("#start_date").val() );
			}

			if ( $("#end_date").val() != '' ) {
				defaultDate.push( $("#end_date").val() );
			}

			window.coupon_date = $("input.coupon_date").flatpickr({
				mode: "range",
			    altInput: true,
			    locale: obp_flatpickr_obj?.lang,
			    altFormat: calendar_object?.date_format,
			    dateFormat: "Y-m-d",
			    defaultDate: defaultDate,
			    onChange: [function(selectedDates){
			        const dateArr = selectedDates.map(date => this.formatDate(date, "Y-m-d"));
			        var [startDate, endDate] = dateArr;
			        $("#start_date").val( startDate );
			        $("#end_date").val( endDate );
			    }]
			});

			var timeFormat = calendar_object?.time_format;
			// Business Time
			var isRTL = $("body").hasClass("rtl");

			var orientation = isRTL ? 'r' : 'l';


			$('#from_time').timepicker({
				'timeFormat': timeFormat,
				'noneOption': true,
				'orientation': orientation,
				'step': 15,
				'disableTextInput': true,
			});

			$('#to_time').timepicker({
				'timeFormat': timeFormat,
				'noneOption': true,
				'orientation': orientation,
				'step': 15,
				'disableTextInput': true,
			});
		},
		show_loader( flag = true, container = '.obp-content-coupon' ){
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
        	$(".coupon-pagination .page_item").off().on("click", function(e){
        		e.preventDefault();
        		const page = $(this).attr("data-page");

        		const data = {
        			'action': 'obp_load_data_coupon',
        			'nonce': ajax_object.nonce,
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
	                    $(".obp_coupon_body").html('');
	                    $(".obp-pagination-wrap").html('');

	                    $(".obp_coupon_body").html( data?.coupon_html );
	                    $(".obp-pagination-wrap").html( data?.pagination_html );

	                    that.init_events();
	                }
	            });        		
        	});
        },
        delete_coupon: function(){
        	const that = this;
        	$(".obp_delete_coupon").off().on("click",function(e){
        		e.preventDefault();

        		const action_wrap = $(this).closest(".obp_coupon_action_wrap");
        		const couponId = action_wrap.find('input[name="coupon_id"]').val();

        		const data = {
        			'action': 'obp_delete_coupon',
        			'nonce': ajax_object.nonce,
        			'coupon_id': couponId,
        		};
        		
        		new $.Zebra_Dialog(obp_coupon_obj.confirm_delete,
                    {
                        type: "question",
                        title: null,
                        buttons: [
                            {caption: obp_coupon_obj.yes, callback: function() {
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
                            {caption: obp_coupon_obj.no, callback: function() {

                            }},
                        ]
                    }
                );

        	});
        },
		submit_handler: function(){
			const that = this;
			$(".obp_edit_coupon_form").off().on("submit", function(e){
				e.preventDefault();

				const couponId = $(this).find('input[name="coupon_id"]').val();
				const couponCode = $(this).find('input[name="coupon_code"]').val();
				const visibility = $(this).find('select[name="visibility"]').val();
				const description = $(this).find('textarea[name="description"]').val();
				const discountType = $(this).find('select[name="discount_type"]').val();
				const couponAmount = $(this).find('input[name="amount"]').val();
				const couponQty = $(this).find('input[name="quantity"]').val();
				const orderFrom = $(this).find('input[name="order_from"]').val();
				const applyTo = $(this).find('input[name="apply_to"]:checked').val();
				const applyServices = $(this).find('select[name="apply_services"]').val();
				const startDate = $(this).find("#start_date").val();
				const endDate = $(this).find('#end_date').val();
				const fromTime = $(this).find('#from_time').val();
				const toTime = $(this).find('#to_time').val();
				const useOn = $(this).find('select[name="use_on"]').val();

				$(".obp_message_wrapper").html('');

				var messages = '';
                const messageErrorHTML = '<div class="obp_alert_danger" role="alert">[message]</div>';

                if ( couponCode == '' ) {
                    messages += messageErrorHTML.replace( "[message]", obp_coupon_obj.code_req );
                }

                if ( couponAmount == '' ) {
                	messages += messageErrorHTML.replace( "[message]", obp_coupon_obj.amount_req );
                }

                if ( couponQty == '' ) {
                	messages += messageErrorHTML.replace( "[message]", obp_coupon_obj.quantity_req );
                }

                if ( messages != '' ) {
                    $(".obp_message_wrapper").append( messages );

                    $('html, body').animate({
                        scrollTop: $(".obp_message_wrapper").offset().top - 100
                    }, 1000);
                    return false;
                }

                that.show_loader();

				const data = {
					'action': 'obp_save_coupon',
					'nonce': ajax_object.nonce,
					'coupon_id': couponId,
					'coupon_code': couponCode,
					'visibility': visibility,
					'description': description,
					'discount_type': discountType,
					'coupon_amount': couponAmount,
					'coupon_qty': couponQty,
					'order_from': orderFrom,
					'apply_to': applyTo,
					'apply_services': applyServices,
					'start_date': startDate,
					'end_date': endDate,
					'from_time': fromTime,
					'to_time': toTime,
					'use_on': useOn,
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
	                    if ( data?.redirect == true ) {
	                    	window.location.replace( data?.url );
	                    } else {
	                    	window.location.reload( true );
	                    }
	                }
	            });
			});
		}
	};


	OBP_Manage_Coupon.init();
})(jQuery);