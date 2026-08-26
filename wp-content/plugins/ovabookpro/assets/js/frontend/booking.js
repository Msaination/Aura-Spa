(function ($) {

	class OBP_Booking {

		constructor(){
			this.init();
		}

		init(){
			this.booking_service();
			this.calendar_loader();
		}

		init_booking(){
			OBP_Frontend.obp_select2();
			this.calendar_slider();
			this.time_loader();
			this.remove_cart_item();
			this.booking_continue();
			this.change_time();
			this.change_calendar();
			this.sort_order_item();
			this.change_staff();
			this.add_another_service();
			
			this.come_back();
			this.booking_form_off_submit();
			this.booking_popup_close_off();
			this.apply_coupon();
			this.add_package_option();
			this.remove_package();
		}

		close_service_popup(){
			$('.obp_booking_service_close').off().on('click',function(e){
				e.preventDefault();
				OBP_Service_Dialog.close();
			});
		}

		remove_package(){
			const that = this;
			$('.obp_remove_package').off().on('click', function(e){
				e.preventDefault();

				const $this = $(this);
				const package_id = $this.closest('.package-item').find('input').val();
				const service_id = $this.closest('.service-packages').attr('data-service-id');

				const data = {
					'action': 'obp_booking_remove_package',
					'nonce': ajax_object.nonce,
					'package_id': package_id,
					'service_id': service_id,
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
                    	$(".obp_booking_form .obp-calendar-content").html(data);
						that.init_booking();
                    }
                });
			});
		}

		add_package_option(){
			const that = this;

			$('.package_input').on('change', function(){
				const $this = $(this);
				const container 	= $this.closest('.service-packages');
				const service_id 	= container.attr("data-service-id");
				var package_ids 	= [];
				var val = '';

				container.find('.package_group').each( function(i,el){
					const type = $(el).attr("data-type");
					switch( type ) {
						case 'radio':
							var val = $(el).find('.package_input:checked').val();
							if ( val ) {
								package_ids.push( val );
							}
						break;

						case 'select':
							var val = $(el).find('.package_input').val();
							if ( val ) {
								package_ids.push( val );
							}
						break;

						case 'checkbox':
							$(el).find('.package_input:checked').each(function( j,elm ){
								var val = $(elm).val();
								if ( val ) {
									package_ids.push( val );
								}
							});
						break;

						default:
						break;
					}
				} );

				that.show_loader();

				const data = {
					'action': 'obp_booking_add_package',
					'nonce': ajax_object.nonce,
					'package_ids': package_ids,
					'package_id': val,
					'service_id': service_id,
				}

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
                    	$(".obp_booking_form .obp-calendar-content").html(data);
						that.init_booking();
                    }
                });
			});
		}

		apply_coupon(){
			const that = this;

			$('input[name="coupon_code"]').on('keypress', function(e){

				if ( e.which == 13 ) {
					$(".coupon_code_apply").trigger("click");
				}
			});

			$(".coupon_code_apply").off().on("click", function(){
				const vendorId = $(".obp_booking_form").attr("data-vendor-id");
				var couponCode = $('input[name="coupon_code"]').val();
				couponCode = couponCode.replace(/ /g,"");
				that.show_loader();

				const data = {
					'action': 'obp_booking_apply_coupon',
					'nonce': ajax_object.nonce,
					'coupon_code': couponCode,
					'vendor_id': vendorId,
				}

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

                    	if ( data ) {
							$(".obp_booking_form .obp-calendar-content").html(data);
							that.init_booking();
						}
                    }
                });
			});
		}

		booking_popup_close_off(){
			$(".obp_booking_dialog .ZebraDialog_Close").off().on("click",function(e){
				e.preventDefault();
				var obp_booking_form = $(".obp_booking_form");
					    		
	    		if ( obp_booking_form.length ) {

	    			var title = obp_booking_form.attr("data-discard-title");
	    			var message = obp_booking_form.attr("data-discard-message");
	    			var continueBooking = obp_booking_form.attr("data-discard-continue");
	    			var discardBooking = obp_booking_form.attr("data-discard-agree");

		    		new $.Zebra_Dialog(message,{
		    			type: "question",
		    			custom_class: "obp_booking_discard",
		    			title: title,
		    			buttons: [
		    				{
		    					caption: continueBooking,
		    					callback: function() {
					            }
					        },
					        {
		    					caption: discardBooking,
		    					callback: function() {
		    						// empty cart
		    						var data = {
		    							'action': 'obp_booking_empty_cart',
		    							'nonce': ajax_object.nonce
		    						};

		    						$('.obp_booking_form').block({
										message: null,
										overlayCSS:  { 
									        backgroundColor: '#fff', 
									        opacity: 0.3, 
									        cursor: null 
									    },
									});

		    						$.post( ajax_object.ajax_url, data, function(res){
		    							OBP_Booking_Dialog.close();
		    							location.reload();
		    						} );
				                	
					            }
					        },
	    				],
		    		});
	    		} else {
	    			OBP_Booking_Dialog.close();
	    		}
			});

		}

		filter_service(){
			var that = this;
			$("#obp_search_service").off().on("change",function(e){
				e.preventDefault();

				var serviceName = $(this).val();
				var vendorID 	= $(".obp_booking_service_container").attr("data-vendor-id");
				var nonce 		= $(".obp_booking_service_container").attr("data-nonce");

				var data = {
					'action': 'obp_booking_filter_service',
					'nonce': nonce,
					'service_name': serviceName,
					'vendor_id': vendorID,
				};

				$('.obp_booking_services').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url, data, function(res){
					$('.obp_booking_services').unblock();
					$(".obp_booking_services").html(res);
					that.filter_service();
				} );

			});

			$(".obp_booking_services").collapse({
				query: 'div h4',
			});

			$(".obp_booking_services").bind("opened", function(e, section) {
				section.$summary.find("i").addClass("is-active");
			});

			$(".obp_booking_services").bind("closed", function(e, section) {
				section.$summary.find("i").removeClass("is-active");
			});
			// Open all
			$(".obp_booking_services").trigger("open");
		}

		booking_form_off_submit(){
			$(".obp_booking_form").off().on("submit",function(e){
				e.preventDefault();
			});
		}

		save_another_service(){
			var that = this;

			$(".obp_booking_service_container .obp_add_service").off().on("click",function(e){
				e.preventDefault();
				var $this = $(this);

				var serviceID 	= $this.attr("data-id");
				var businessID 	= $(".obp_booking_service_container").attr("data-business-id");
				var vendorID 	= $(".obp_booking_service_container").attr("data-vendor-id");
				var nonce 		= $(".obp_booking_service_container").attr("data-nonce");

				var data = {
					'action': 'obp_booking_save_another_service',
					'nonce': nonce,
					'business_id': businessID,
					'vendor_id': vendorID,
					'service_id': serviceID,
				};

				OBP_Service_Dialog.close();

				$('.obp_booking_form').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url, data, function(res){
					$('.obp_booking_form').unblock();
					if ( res ) {
						$('.obp-calendar-content').html(res);
						that.init_booking();
					}
				} );

			});
		}

		add_another_service(){
			var that = this;

			$(".obp_booking_form .obp_add_another_service").off().on("click",function(e){
				e.preventDefault();

				var nonce = $(".obp_booking_form").attr("data-nonce");
				var vendorID = $(".obp_booking_form").attr("data-vendor-id");
				var businessID = $(".obp_booking_form").attr("data-business-id");
				var data = {
					'action': 'obp_booking_another_service',
					'nonce': nonce,
					'vendor_id': vendorID,
					'business_id': businessID,
				};

				$('.obp_booking_form').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url, data, function(res){
					$('.obp_booking_form').unblock();
					if ( res ) {
						window.OBP_Service_Dialog = new $.Zebra_Dialog(res,
						    {
						    	type: false,
						    	buttons: false,
						    	show_close_button: false,
						    	width: 750,
						    	height: 500,
						    }
						);
						that.save_another_service();
						that.filter_service();
						that.close_service_popup();
						// For tooltip
						OBP_Frontend.tooltip_init();
					}
				} );


			});
		}

		change_staff(){
			var that = this;
			$(".obp_booking_form .edit-staff").off().on("click",function(e){
				e.preventDefault();

				var $this = $(this);
				var serviceID = $this.attr("data-service-id");
				var nonce = $(".obp_booking_form").attr("data-nonce");

				var data = {
					'action': 'obp_booking_change_staff',
					'nonce': nonce,
					'service_id': serviceID,
				};

				$('.obp_booking_form').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url, data, function(res){
					$('.obp_booking_form').unblock();
					if ( res ) {
						window.OBP_Staff_Dialog = new $.Zebra_Dialog(res,
						    {
						    	type: false,
						    	buttons: false,
						    	width: 350,
						    }
						);
						that.update_staff();
					}

				} );

			});
		}

		update_staff(){
			var that = this;
			$(".obp_booking_staff_container .staff-card").off().on("click",function(e){
				e.preventDefault();
				var $this = $(this);

				if ( $this.hasClass("is-active") ) {
					return false;
				}

				var staffID = $this.attr("data-id");
				var nonce = $(".obp_booking_staff_container").attr("data-nonce");
				var serviceID = $(".obp_booking_staff_container").attr("data-service-id");

				var data = {
					'action': 'obp_booking_update_staff',
					'nonce': nonce,
					'staff_id': staffID,
					'service_id': serviceID,
				};

				$('.obp_booking_form').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				OBP_Staff_Dialog.close();

				$.post( ajax_object.ajax_url, data, function(res){
					$('.obp_booking_form').unblock();
					if ( res ) {
						$('.obp-calendar-content').html(res);
						that.init_booking();
					}
				} );
			});
		}

		come_back(){
			var that = this;
			$('.obp_booking_back').off().on("click",function(e){
				e.preventDefault();

				var data = {
					'action': 'obp_booking_come_back',
					'nonce': ajax_object.nonce,
				};

				$('.obp_booking_form').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url, data, function(res){
					$('.obp_booking_form').unblock();
					if ( res ) {
						$(".obp_booking_form_popup").html(res);
						that.init_booking();
					}
				} );

			});
		}

		sort_order_item(){
			var that = this;
			var oldIndex = null;

			$(".obp_booking_form .obp-order-container").sortable({
				placeholder: "ui-state-highlight",
				handle: ".sort_item",
				start: function( event, ui ){
					oldIndex = ui.item.index();
				},
				update: function(event, ui) { 
		            var newIndex = ui.item.index();
		            var nonce = $(".obp_booking_form").attr("data-nonce");
		            var data = {
		            	'action': 'obp_booking_sort_item',
		            	'nonce': nonce,
		            	'old_index': oldIndex,
		            	'new_index': newIndex,
		            };

		            $('.obp_booking_form').block({
						message: null,
						overlayCSS:  { 
					        backgroundColor: '#fff', 
					        opacity: 0.3, 
					        cursor: null 
					    },
					});

		            $.post( ajax_object.ajax_url, data, function(res){
		            	$('.obp_booking_form').unblock();
		            	if ( res ) {
							$('.obp-calendar-content').html(res);
							that.init_booking();
						}
		            } );
		        },
			});

			$(".obp_booking_form .obp-order-container").disableSelection();

		}

		show_loader( flag = true, container = '.obp_booking_form' ){
			if ( flag ) {
				$(container).block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3,
				        cursor: null 
				    },
				});
			} else {
				$(container).unblock();
			}
		}

		booking_continue(){
			const that = this;
			$(".obp_booking_form .obp_booking_continue").off().on("click",function(e){
				e.preventDefault();
				var nonce = $(".obp_booking_form").attr("data-nonce");

				var data = {
					'action': 'obp_booking_continue',
					'nonce': nonce,
				};

				that.show_loader();

				$.ajax({
                    url: ajax_object.ajax_url,
                    data: data,
                    method: 'POST',
                    type: 'POST',
                    error: function( jqXHR, textStatus, errorThrown){
                        console.error( errorThrown );
                    },
                    success: function(res){

                    	if ( res.status == 'error' ) {
							$('.obp_booking_form').block({
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
							that.show_loader(false);
						}

						if ( res?.callback ) {
							window[res.callback](res.data);
							OBP_Booking_Dialog.close();
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

		change_calendar(){
			var that = this;
			$(".obp_booking_form .date-card").off().on("click",function(e){
				e.preventDefault();

				var $this = $(this);

				if ( $this.hasClass("is-active") ) {
					return false;
				}

				var dataDate = $this.attr("data-date");
				var nonce = $(".obp_booking_form").attr("data-nonce");

				var data = {
					'action': 'obp_booking_change_calendar',
					'nonce': nonce,
					'date': dataDate,
				};

				$(".obp_booking_form .date-card").removeClass("is-active");
				$this.addClass("is-active");


				$('.obp_booking_form').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url, data, function(res){
					$('.obp_booking_form').unblock();
					if ( res?.html ) {
						$(".obp-calendar-content").html(res?.html);
						that.init_booking();
					}

					if ( res?.month_year ) {
						$(".obp_booking_form .month-year").html(res?.month_year);
					}
				} );

			});
		}

		remove_cart_item(){
			var that = this;
			$(".obp_booking_form .obp-order-item .remove_item").off().on("click",function(e){
				e.preventDefault();
				var $this = $(this);
				var serviceID = $this.attr("data-service-id");
				var nonce = $(".obp_booking_form").attr("data-nonce");
				var data = {
					'action': 'obp_booking_remove_item',
					'nonce': nonce,
					'service_id': serviceID,
				};

				$('.obp_booking_form').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post(ajax_object.ajax_url,data,function(res){
					$('.obp_booking_form').unblock();
					if ( res ) {
						$('.obp-calendar-content').html(res);
						that.init_booking();
						
					} else {
						OBP_Booking_Dialog.close();
					}
				});
			});
		}

		change_time(){
			var that = this;
			$(".obp_booking_form .time-card").off().on("click",function(e){
				e.preventDefault();
				var $this = $(this);

				if ( $this.hasClass("is-active") ) {
					return false;
				}
				var nonce = $(".obp_booking_form").attr("data-nonce");
				var dataTime = $this.attr("data-time");

				$(".obp_booking_form .time-card").removeClass("is-active");
				$this.addClass("is-active");

				var data = {
					'action': 'obp_booking_change_time',
					'nonce': nonce,
					'time': dataTime,
				};

				$('.obp_booking_form').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url, data, function(res){

					$('.obp_booking_form').unblock();
					$(".obp_booking_form .obp-calendar-content").html(res);

					that.init_booking();
				} );
			});
		}

		booking_service(){
			var that = this;
			$('.obp_booking_popup').off().on('click',function(e){
				e.preventDefault();
				var $this = $(this);
				var serviceID 	= $this.attr('data-id');
				var vendorID 	= $this.attr("data-vendor-id");
				var businessID 	= $this.attr("data-business-id");
				var nonce 		= $this.attr('data-nonce');


				var data = {
					'action': 'obp_booking_popup',
					'nonce': nonce,
					'service_id': serviceID,
					'vendor_id': vendorID,
					'business_id': businessID,
				};

				$('body').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null
				    },
				});

				$.post( ajax_object.ajax_url, data, function(res){

					$('body').unblock();

					if ( res ) {
						window.OBP_Booking_Dialog = new $.Zebra_Dialog(res,
						    {
						    	type: false,
						    	custom_class: "obp_booking_dialog",
						    	keyboard: false,
						    	buttons: false,
						    	width: 750,
						    	height: 850,
						    	backdrop_close: false,
						    }
						);

						that.init_booking();
					}
				} );

			});
		}

		calendar_slider( pos = null ){

			var isRTL = $("body").hasClass("rtl");

			var navText = [
		    	'<i class="bookproicon-left"></i>',
		    	'<i class="bookproicon-arrow-right"></i>',
	    	]

			if ( isRTL ) {
				navText.reverse();
			}

			window.calendarCarousel = $('.obp-calendar-slider .owl-carousel').owlCarousel({
			    loop:false,
			    margin:10,
			    nav:true,
			    dots: false,
			    items: 7,
			    rtl: isRTL,
			    responsive: {
			    	0: {
			    		items: 3,
			    	},
			    	400: {
			    		items: 4,
			    	},
			    	480: {
			    		items: 4,
			    	},
			    	600: {
			    		items: 5,
			    	},
			    	768: {
			    		items: 7,
			    	},
			    	1200: {
			    		items: 7,
			    	}
			    },
			    navText: navText,
			});

			if ( pos !== null ) {
				calendarCarousel.trigger("to.owl.carousel", [pos, 0]);
			}

			if ( $(".obp_booking_form .obp-calendar-slider .date-card").length ) {
				$(".obp_booking_form .obp-calendar-slider .date-card").each(function(i,el){
					if ( $(el).hasClass("is-active") ) {
						calendarCarousel.trigger("to.owl.carousel", [i, 300]);
					}
				});
			}
			
		}

		calendar_loader(){
			var that = this;
			// Next calendar
			$(document).off('click','.obp_booking_form .obp-calendar-slider .owl-nav .owl-next.disabled').on('click','.obp_booking_form .obp-calendar-slider .owl-nav .owl-next.disabled',function(){
				var calendar = $('.obp-calendar-slider');
				var owlCarousel = calendar.find('.owl-carousel');
				var endDate = owlCarousel.attr('data-end-date');
				var targetDate = calendar.attr('data-target-date');
				var business_id = calendar.closest('.obp_booking_form').attr('data-business-id');
				var data = {
					'action': 'obp_booking_next_calendar',
					'end_date': endDate,
					'target_date': targetDate,
					'nonce': ajax_object?.nonce,
					'business_id': business_id,
				};

				$('.obp-calendar-slider').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url, data, function(res){
					$('.obp-calendar-slider').unblock();
					if ( res ) {
						calendar.html( res );
						var monthYear = $(".obp-calendar-slider .owl-carousel").attr("data-month-year");
						$(".obp_booking_form .month-year").html(monthYear);
						that.change_calendar();
						that.calendar_slider();
					}
				} );
			});

			// Prev calendar
			$(document).off( 'click', '.obp_booking_form .obp-calendar-slider .owl-nav .owl-prev.disabled' ).on('click','.obp_booking_form .obp-calendar-slider .owl-nav .owl-prev.disabled',function(){
				var calendar 	= $('.obp-calendar-slider');
				var owlCarousel = calendar.find('.owl-carousel');
				var targetDate 	= calendar.attr('data-target-date');
				var startDate 	= owlCarousel.attr('data-start-date');
				var dataPrev 	= owlCarousel.attr('data-prev');
				var business_id = calendar.closest('.obp_booking_form').attr('data-business-id');
				if ( dataPrev == 'false' ) {
					return false;
				}

				var data = {
					'action': 'obp_booking_prev_calendar',
					'start_date': startDate,
					'target_date': targetDate,
					'nonce': ajax_object?.nonce,
					'business_id': business_id,
				};

				$('.obp-calendar-slider').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url, data, function(res){
					$('.obp-calendar-slider').unblock();
					if ( res ) {
						calendar.html( res );
						var monthYear = $(".obp-calendar-slider .owl-carousel").attr("data-month-year");
						$(".obp_booking_form .month-year").html(monthYear);
						var pos = $('.obp-calendar-slider .item').length - 1;
						that.change_calendar();
						that.calendar_slider( pos );
					}
				} );
			});
		}


		time_loader(){
			var that = this;

			var isRTL = $("body").hasClass("rtl");

			var navText = [
		    	'<i class="bookproicon-left"></i>',
		    	'<i class="bookproicon-arrow-right"></i>',
	    	]

			if ( isRTL ) {
				navText.reverse();
			}

			window.timeCarousel = $('.obp-time-slider .owl-carousel').owlCarousel({
			    loop:false,
			    margin:10,
			    nav:true,
			    dots: false,
			    navText: navText,
			    items: 5,
			    rtl: isRTL,
			    responsive: {
			    	0: {
			    		items: 3,
			    		nav:false,
			    	},
			    	480: {
			    		items: 3,
			    	},
			    	600: {
			    		items: 4,
			    	},
			    	700: {
			    		items: 5,
			    	},
			    },
			});

			// Go to position time.
			if ( $(".obp_booking_form .obp-time-slider .time-card").length ) {
				$(".obp_booking_form .obp-time-slider .time-card").each( function(i,el){

					if ( $(el).hasClass("is-active") ) {

						timeCarousel.trigger("to.owl.carousel", [i, 300]);
					}
				} );
			}

			$('.obp_booking_form .times_day .time').off().on('click',function(e){
				e.preventDefault();

				var $this = $(this);
				var workHours = JSON.parse( $this.attr('data-work-hour') );

				if ( workHours ) {
					var { start, end } = workHours;

					$('.obp_booking_form .times_day .time').removeClass('is-active');
					$this.addClass('is-active');

					if ( $('.obp-time-slider .time-card').length ) {

						$('.obp-time-slider .time-card').each( function(i,el){
							var dataTime = parseInt( $(el).attr('data-time') );

							if ( dataTime >= start && dataTime <= end ) {

								timeCarousel.trigger("to.owl.carousel", [i, 300]);
								return false;
							}
						} );
					}
					
				}

			});
		}
	}


	window.OBP_Booking = new OBP_Booking();

})(jQuery);