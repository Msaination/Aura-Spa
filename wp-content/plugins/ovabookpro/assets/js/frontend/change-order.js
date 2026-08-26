(function ($) {

	window.OBP_Change_Order = {

		init: function(){
			this.change_schedule();
			this.calendar_loader();
		},

		popup_init: function(){
			this.calendar_slider();
			this.time_loader();
			this.change_calendar();
			this.change_time();
			this.sort_order_item();
			this.change_order_update();
			this.change_staff();
			this.come_back();
			this.booking_popup_close_off();
		},

		booking_popup_close_off: function(){
			if ( $(".obp_change_order_form").length ) {

				$(".ZebraDialog_Close").off().on("click",function(e){
					e.preventDefault();
					var obp_change_order_form = $(".obp_change_order_form");
						    		
		    		if ( obp_change_order_form.length ) {

		    			var title = obp_change_order_form.attr("data-discard-title");
		    			var message = obp_change_order_form.attr("data-discard-message");
		    			var continueBooking = obp_change_order_form.attr("data-discard-continue");
		    			var discardBooking = obp_change_order_form.attr("data-discard-agree");

			    		new $.Zebra_Dialog(message,{
			    			type: "question",
			    			custom_class: "obp_order_discard",
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
			    						var nonce = obp_change_order_form.attr("data-nonce");
			    						var data = {
			    							'action': 'obp_order_change_empty_cart',
			    							'nonce': nonce
			    						};

			    						$('.obp_change_order_form').block({
											message: null,
											overlayCSS:  { 
										        backgroundColor: '#fff', 
										        opacity: 0.3, 
										        cursor: null 
										    },
										});

			    						$.post( ajax_object.ajax_url, data, function(res){
			    							OBP_Cart_Order_Dialog.close();
			    							window.location.reload();
			    						} );
					                	
						            }
						        },
		    				],
			    		});
		    		}
				});
			}
		},

		change_schedule: function(){
			var that = this;
			$(".order_change").off().on("click",function(e){
				e.preventDefault();
				var $this = $(this);
				var $this_item 	= $this.closest(".order_action_wrapper");
				var ordeID 		= $this_item.find('input[name="order_id"]').val();

				var data = {
					'action': 'obp_order_change_schedule',
					'nonce': ajax_object.nonce,
					'order_id': ordeID,
				};

				$('.obp-content').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url, data, function(res){
					if ( res ) {
						window.OBP_Cart_Order_Dialog = new $.Zebra_Dialog(res,
						    {
						    	type: false,
						    	custom_class: "obp_order_dialog",
						    	keyboard: false,
						    	buttons: false,
						    	width: 750,
						    	height: 850,
						    	backdrop_close: false,
						    }
						);
						that.popup_init();
					}
					$('.obp-content').unblock();
				} );
			});
		},

		update_staff: function(){
			var that = this;
			$(".obp_change_order_staff_container .staff-card").off().on("click",function(e){
				e.preventDefault();
				var $this = $(this);

				if ( $this.hasClass("is-active") ) {
					return false;
				}

				var staffID = $this.attr("data-id");
				var nonce = $(".obp_change_order_staff_container").attr("data-nonce");
				var serviceID = $(".obp_change_order_staff_container").attr("data-service-id");
				var orderID = $(".obp_change_order_staff_container").attr("data-order-id");

				var data = {
					'action': 'obp_order_change_update_staff',
					'nonce': nonce,
					'staff_id': staffID,
					'service_id': serviceID,
					'order_id': orderID,
				};

				$('.obp_change_order_form').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				OBP_Staff_Dialog.close();

				$.post( ajax_object.ajax_url, data, function(res){

					if ( res ) {
						$('.obp-calendar-content').html(res);
						that.popup_init();
					}

					$('.obp_change_order_form').unblock();

				} );
			});
		},

		change_staff: function(){
			var that = this;
			$(".obp_change_order_form .edit-staff").off().on("click",function(e){
				e.preventDefault();

				var $this = $(this);
				var serviceID = $this.attr("data-service-id");
				var nonce = $(".obp_change_order_form").attr("data-nonce");
				var ordeID = $(".obp_change_order_form").attr("data-order-id");

				var data = {
					'action': 'obp_order_change_staff',
					'nonce': nonce,
					'service_id': serviceID,
					'order_id': ordeID,
				};

				$('.obp_change_order_form').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url, data, function(res){

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

					$('.obp_change_order_form').unblock();

				} );

			});
		},

		change_calendar: function(){
			var that = this;
			$(".obp_change_order_form .date-card").off().on("click",function(e){
				e.preventDefault();

				var $this = $(this);

				if ( $this.hasClass("is-active") ) {
					return false;
				}

				var dataDate = $this.attr("data-date");
				var nonce = $(".obp_change_order_form").attr("data-nonce");
				var ordeID = $(".obp_change_order_form").attr("data-order-id");

				var data = {
					'action': 'obp_order_change_calendar',
					'nonce': nonce,
					'date': dataDate,
					'order_id': ordeID,
				};

				$(".obp_change_order_form .date-card").removeClass("is-active");
				$this.addClass("is-active");


				$('.obp_change_order_form').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url, data, function(res){

					if ( res?.html ) {
						$('.obp_change_order_form .obp-calendar-content').html(res?.html);

						that.time_loader();
						that.change_staff();
						that.change_time();
						that.come_back();
						that.sort_order_item();
						that.change_order_update();
					}

					if ( res?.month_year ) {
						$(".obp_change_order_form .month-year").html( res?.month_year );
					}

					$('.obp_change_order_form').unblock();

				} );

			});
		},

		sort_order_item: function(){
			var that = this;
			var oldIndex = null;

			$(".obp_change_order_form .obp-order-container").sortable({
				placeholder: "ui-state-highlight",
				handle: ".sort_item",
				start: function( event, ui ){
					oldIndex = ui.item.index();
				},
				update: function(event, ui) { 
		            var newIndex = ui.item.index();
		            var nonce = $(".obp_change_order_form").attr("data-nonce");
		            var ordeID = $(".obp_change_order_form").attr("data-order-id");
		            var data = {
		            	'action': 'obp_order_change_sort_item',
		            	'nonce': nonce,
		            	'order_id': ordeID,
		            	'old_index': oldIndex,
		            	'new_index': newIndex,
		            };

		            $('.obp_change_order_form').block({
						message: null,
						overlayCSS:  { 
					        backgroundColor: '#fff', 
					        opacity: 0.3, 
					        cursor: null 
					    },
					});

		            $.post( ajax_object.ajax_url, data, function(res){

		            	if ( res ) {
							$('.obp_change_order_form_popup').html(res);
							that.popup_init();
						}

		            } );
		        },
			});

			$(".obp_change_order_form .obp-order-container").disableSelection();

		},

		change_time: function(){
			var that = this;
			$(".obp_change_order_form .time-card").off().on("click",function(e){
				e.preventDefault();
				var $this = $(this);

				if ( $this.hasClass("is-active") ) {
					return false;
				}
				var nonce = $(".obp_change_order_form").attr("data-nonce");
				var dataTime = $this.attr("data-time");
				var orderID = $(".obp_change_order_form").attr("data-order-id");

				$(".obp_change_order_form .time-card").removeClass("is-active");
				$this.addClass("is-active");

				var data = {
					'action': 'obp_order_change_time',
					'nonce': nonce,
					'time': dataTime,
					'order_id': orderID,
				};

				$('.obp_change_order_form').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url, data, function(res){

					$(".obp_change_order_form .obp-order-container").html(res);

					var targetTime = parseInt( $(".obp-time-slider .time-card.is-active").attr("data-time") );

					if ( $(".obp_change_order_form .times_day .time").length ) {
						$(".obp_change_order_form .times_day .time").each(function(i,el){
							var { start, end } = JSON.parse( $(el).attr("data-work-hour") );

							if ( targetTime >= start && targetTime <= end ) {
								$(".obp_change_order_form .times_day .time").removeClass("is-active");
								$(el).addClass("is-active");
								return false;
							}
						});
					}

					that.change_staff();
					that.sort_order_item();
					that.come_back();

					$('.obp_change_order_form').unblock();

				} );
			});
		},

		calendar_loader: function(){
			var that = this;
			// Next calendar
			$(document).on('click','.obp_change_order_form .obp-calendar-slider .owl-nav .owl-next.disabled',function(){
				var calendar 	= $('.obp-calendar-slider');
				var owlCarousel = calendar.find('.owl-carousel');
				var endDate 	= owlCarousel.attr('data-end-date');
				var targetDate 	= calendar.attr('data-target-date');
				var business_id = calendar.closest('.obp_change_order_form').attr('data-business-id');

				var data = {
					'action': 'obp_order_change_next_calendar',
					'end_date': endDate,
					'target_date': targetDate,
					'nonce': ajax_object?.nonce,
					'business_id': business_id
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

					if ( res ) {
						calendar.html( res );
						var monthYear = $(".obp-calendar-slider .owl-carousel").attr("data-month-year");
						$(".obp_change_order_form .month-year").html(monthYear);
						that.change_calendar();
						that.calendar_slider();
					}

					$('.obp-calendar-slider').unblock();

				} );
			});

			// Prev calendar
			$(document).on('click','.obp_change_order_form .obp-calendar-slider .owl-nav .owl-prev.disabled',function(){
				var calendar 	= $('.obp-calendar-slider');
				var owlCarousel = calendar.find('.owl-carousel');
				var targetDate 	= calendar.attr('data-target-date');
				var startDate 	= owlCarousel.attr('data-start-date');
				var dataPrev 	= owlCarousel.attr('data-prev');
				var business_id = calendar.closest('.obp_change_order_form').attr('data-business-id');

				if ( dataPrev == 'false' ) {
					return false;
				}

				var data = {
					'action': 'obp_order_change_prev_calendar',
					'start_date': startDate,
					'target_date': targetDate,
					'business_id': business_id,
					'nonce': ajax_object?.nonce,
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

					if ( res ) {
						calendar.html( res );
						var monthYear = $(".obp-calendar-slider .owl-carousel").attr("data-month-year");
						$(".obp_change_order_form .month-year").html(monthYear);
						var pos = $('.obp-calendar-slider .item').length - 1;
						that.change_calendar();
						that.calendar_slider( pos );
					}

					$('.obp-calendar-slider').unblock();

				} );
			});
		},

		time_loader: function(){
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
			    rtl: isRTL,
			    items: 5,
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
			if ( $(".obp_change_order_form .obp-time-slider .time-card").length ) {
				$(".obp_change_order_form .obp-time-slider .time-card").each( function(i,el){

					if ( $(el).hasClass("is-active") ) {

						timeCarousel.trigger("to.owl.carousel", [i, 300]);
					}
				} );
			}

			$('.obp_change_order_form .times_day .time').off().on('click',function(e){
				e.preventDefault();

				var $this = $(this);
				var workHours = JSON.parse( $this.attr('data-work-hour') );

				if ( workHours ) {
					var { start, end } = workHours;

					$('.obp_change_order_form .times_day .time').removeClass('is-active');
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
		},

		come_back: function(){
			var that = this;
			$(".obp_change_order_form .obp_change_order_back").off().on("click",function(e){
				e.preventDefault();
				
				var nonce = $(".obp_change_order_form").attr("data-nonce");
				var orderID = $(".obp_change_order_form").attr("data-order-id");
				var data = {
					'action': 'obp_order_change_come_back',
					'order_id': orderID,
					'nonce': nonce,
				};

				$('.obp_change_order_form').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url, data, function(res){
					if ( res ) {
						$('.obp_change_order_form_popup').html(res);
						that.popup_init();
					}
				} );

			});
		},

		calendar_slider: function( pos = null ){

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

			if ( $(".obp_change_order_form .obp-calendar-slider .date-card").length ) {
				$(".obp_change_order_form .obp-calendar-slider .date-card").each(function(i,el){
					if ( $(el).hasClass("is-active") ) {
						calendarCarousel.trigger("to.owl.carousel", [i, 300]);
					}
				});
			}
			
		},

		change_order_update: function(){
			$(".obp_change_order_form").off().on("submit",function(e){
				e.preventDefault();
			});

			$(".obp_change_order_form .obp_order_change_update").off().on("click",function(e){
				e.preventDefault();
				var nonce = $(".obp_change_order_form").attr("data-nonce");
				var orderID = $(".obp_change_order_form").attr("data-order-id");

				var data = {
					'action': 'obp_order_change_update',
					'nonce': nonce,
					'order_id': orderID,
				};

				$('.obp_change_order_form').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url, data, function(res){
					window.location.reload(true);
				} );

			});
		}
	}


	OBP_Change_Order.init();
})(jQuery);