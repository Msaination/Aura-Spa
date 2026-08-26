(function ($) {


	class OBP_Plan {

		constructor(){
			this.init();
		}

		init(){
			this.add_plan();
			this.calendar_plan();
			this.save_plan();
			this.edit_plan();
			this.remove_plan();
			this.add_custom_time();
			this.show_info();
		}

		show_loader( flag = true, container = '.obp-content-plan' ){
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
        }

        remove_service_time(){
        	$('.obp_remove_service_time').off().on('click', function(e){
        		e.preventDefault();
        		$(this).closest('.obp_service_time_item').remove();
        	});
        }

        init_service_time(){
        	var timeFormat = calendar_object.time_format;
			// Business Time
			var isRTL = $("body").hasClass("rtl");

			var orientation = isRTL ? 'r' : 'l';

			$('.obp_service_time_item .service_time').timepicker({
				'timeFormat': timeFormat,
				'noneOption': true,
				'orientation': orientation,
				'step': 15,
				'disableTextInput': true,
				'minTime': '6:00am',
				'maxTime': '5:45am',
			});

			$('.obp_service_time_item .service_time').on('changeTime',function(){
				var secondTime = $(this).timepicker('getSecondsFromMidnight');
				$(this).attr("data-time", secondTime );
			});
        }

        add_service_time(){
        	var that = this;

        	$('.obp_add_service_time').off().on('click', function(e){
        		e.preventDefault();
        		var service_times_wrap = $(this).closest('.obp_special_service_item').find('.service_times .list_service_time');
        		var data = {
        			'nonce': ajax_object.nonce,
        			'action': 'obp_add_service_time'
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
                    	service_times_wrap.append( data );

                    	that.init_service_time();

						that.remove_service_time();
                    }
                });


        	});
        }

        add_service_special(){
        	var that = this;
        	$('#services_id').on('change', function(){

        		var service_ids = $(this).val();
        		var cur_service_ids = [];
        		$('.obp_service_special .obp_special_service_item').each( function( i,el ){
        			cur_service_ids.push( $(el).attr('data-service-id') );
        		});

        		that.show_loader();

        		var data = {
        			'action': 'obp_special_service_time',
        			'nonce': ajax_object.nonce,
        			'service_ids': service_ids,
        			'cur_service_ids': cur_service_ids,
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
                    	var count_sv_ids = service_ids.length;
                    	var count_cur_sv_ids = cur_service_ids.length;
                    	if ( count_sv_ids > count_cur_sv_ids ) {
                    		// add service
                    		$('.obp_service_special').append(data);
                    	} else {
                    		// remove service
                    		var difference = cur_service_ids.filter(x => !service_ids.includes(x));
                    		for (var i = difference.length - 1; i >= 0; i--) {
                    			$('.obp_service_special .obp_special_service_item[data-service-id="'+difference[i]+'"]').remove();
                    		}
                    	}
                    	
                    	that.init_service_time();
                    	that.add_service_time();
                    }
                });
        	});
        }

		calendar_plan(){
			var that = this;

			var firstDay = 1;

			switch( calendar_object.first_day ) {
				case 'monday':
					firstDay = 1;
				break;
				case 'tuesday':
					firstDay = 2;
				break;
				case 'wednesday':
					firstDay = 3;
				break;
				case 'thursday':
					firstDay = 4;
				break;
				case 'friday':
					firstDay = 5;
				break;
				case 'saturday':
					firstDay = 6;
				break;
				case 'sunday':
					firstDay = 0;
				break;
			default:
				break;
				}

			var timeFormatRegex = new RegExp("[Aa]");
			var checkRTL = $("body").hasClass("rtl") ? 'rtl' : 'ltr';
			var calendarEl = document.getElementById('obp_plan_calendar');
			var checkHour12 = timeFormatRegex.test(calendar_object.time_format);
			var dataCalendar = JSON.parse( $(calendarEl).attr('data-calendar') );
			var eventSources = [];
			if ( dataCalendar ) {
				for (let key in dataCalendar) {

					var data = {
						'events': [],
						'color': '#5BBB7B',
					};

					var events = [];

					for (var i = 0; i < dataCalendar[key].length; i++) {
						events.push( { 'id': dataCalendar[key][i]['id'], 'start': dataCalendar[key][i]['start_date'], 'end': dataCalendar[key][i]['end_date'] } );
					}

					data['events'] = events;
					
					switch( key ) {
					case 'all':
						data['color'] = '#5BBB7B';
						break;

					case 'some':
						data['color'] = '#E78A00';
						break;
					case 'closed':
						data['color'] = '#AFAFAF';
						break;
					case 'some_closed':
						data['color'] = '#cdcf00';
						break;
					default:
						break;
					}

					eventSources.push( data );

				}
			}

			var DAY_NAMES = obp_fullcalendar?.weekdays;

			var calendar = new FullCalendar.Calendar(calendarEl,{
				initialView: 'dayGridMonth',
				headerToolbar: {
                    right: 'prev,next today',
                    left: 'title',
                },
                dayMaxEvents: false,
				buttonText: obp_fullcalendar?.button_text,
				weekText: obp_fullcalendar?.week,
				allDayText: obp_fullcalendar?.all_day,
				moreLinkText: function(n) {
			        return '+ '+obp_fullcalendar?.more+' ' + n;
			    },
			    noEventsText: obp_fullcalendar?.no_events,
				direction: checkRTL,
				firstDay: firstDay,
				eventSources: eventSources,
				slotLabelFormat: [
			        {
			            hour: '2-digit',
			            minute: '2-digit',
			            hour12: checkHour12
			        }
		        ],
		        eventClick: function( eventClickInfo ){

		        	if (typeof OBP_Plan_Info !== 'undefined') {
		        		OBP_Plan_Info.close();
		        	}

		        	var id = eventClickInfo?.event?.id;
		        	var data = {
		        		'action': 'obp_show_plan_info',
		        		'id': id,
		        		'nonce': ajax_object.nonce
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
	                        window.OBP_Plan_Info = new $.Zebra_Dialog(data, {
	                            buttons: false,
	                            type: false,
	                            width: 600,
	                            custom_class: 'obp_show_plan_info'
	                        });

	                    }
	                });
		        },
				nowIndicator: true,
                navLinks: true,
                editable: true,
                selectable: true,
                selectMirror: true,
                allDaySlot: false,
                eventTimeFormat: {
				    hour: '2-digit',
				    minute: '2-digit',
				    hour12: checkHour12
				},
                titleFormat: function(date) {
		        	var day = date?.date?.day;
		        	var month = date?.date?.month;
		        	var year = date?.date?.year;
				    return OBP_Frontend.obp_get_date( year, month, day );
				},
		        dayHeaderContent: function(arg) {
		        	var theDay = arg.date.getDay();
			    	return DAY_NAMES[theDay];
			    }

			});
			calendar.render();

			
		}

		choose_service(){
			$(".obp_save_plan_form #service_label").off().on("click",function(e){
				e.preventDefault();

				if ( $(this).hasClass('is-active') ) {
					$(this).removeClass('is-active');
					$(".obp_save_plan_form .service_ids_card").fadeOut();
				} else {
					$(this).addClass('is-active');
					$(".obp_save_plan_form .service_ids_card").fadeIn();
				}

			});

			$('.obp_save_plan_form #special_service').off().on("change",function(){
				if ( $(this).is(":checked") ) {
					$(".custom_service_card").fadeIn();
				} else {
					$(".custom_service_card").fadeOut();
				}
			});

			var special_service = $('.obp_save_plan_form input[name="special_service"]:checked');
			
			if ( special_service.length ) {
				$(".custom_service_card").fadeIn();
			} else {
				$(".custom_service_card").fadeOut();
			}
		}

		business_hours(){
			var that = this;
			$('.obp_save_plan_form input[name="business_time"]').off().on('change',function(){

				if ( $(this).val() == 'custom_time' ) {
					$(".custome_time_card").fadeIn();
				} else {
					$(".custome_time_card").fadeOut();
				}

				var dataLabel = $(this).attr("data-label");
				$(".obp_save_plan_form #business_hours_type").val( dataLabel );

			});

			var businessTime = $('.obp_save_plan_form input[name="business_time"]:checked').val();

			if ( businessTime == 'custom_time' ) {
				$(".custome_time_card").fadeIn();
			} else {
				$(".custome_time_card").fadeOut();
			}

			$(".obp_save_plan_form #business_hours_type").off().on('click', function(e){
				e.preventDefault();

				if ( $(this).hasClass('is-active') ) {
					$(this).removeClass('is-active');
					$(".obp_save_plan_form .business_hours_card").fadeOut();
				} else {
					$(this).addClass('is-active');
					$(".obp_save_plan_form .business_hours_card").fadeIn();
				}

			});

			that.business_timepicker_init();

			that.add_custom_time();

			that.remove_custome_time();
		}

		remove_custome_time(){

			$(".business_hours_card .obp_remove_custom_time").off().on("click",function(e){
				e.preventDefault();

				var targetItem = $(this).closest(".custom_time_item");
				targetItem.remove();
				return false;
			});
		}

		business_timepicker_init(){
			var timeFormat = calendar_object.time_format;
			// Business Time
			var isRTL = $("body").hasClass("rtl");

			var orientation = isRTL ? 'r' : 'l';

			$('.obp_save_plan_form .business_custom_time').timepicker({
				'timeFormat': timeFormat,
				'noneOption': true,
				'orientation': orientation,
				'step': 15,
				'disableTextInput': true,
				'minTime': '6:00am',
				'maxTime': '5:45am',
			});

			$('.obp_save_plan_form .business_custom_time').on('changeTime',function(){
				var secondTime = $(this).timepicker('getSecondsFromMidnight');
				$(this).attr("data-time", secondTime );
			});
		}

		add_plan_form_init(id = null){

			var that = this;
			var checkRTL = $("body").hasClass("rtl") ? 'rtl' : 'ltr';

			$(".obp_save_plan_form #status").select2({
				'dir': checkRTL,
			});
			$(".obp_save_plan_form #services_id").select2({
				'allowClear': true,
				'dir': checkRTL,
				'multiple': true,
			});

			var defaultDate = [];

			if ( $("#start_date").val() != '' ) {
				defaultDate.push( $("#start_date").val() );
			}

			if ( $("#end_date").val() != '' ) {
				defaultDate.push( $("#end_date").val() );
			}

			var disableDate = [];

			var dataTimeSlot = JSON.parse( $(".obp_plan_list_items").attr("data-time-slots") );


			if ( dataTimeSlot.length > 0 ) {

				for (var i = 0; i < dataTimeSlot.length; i++) {
					var object = {
						'from': null,
						'to': null,
					};

					if ( id != dataTimeSlot[i]['id'] ) {
						object.from = dataTimeSlot[i]['start_date'];
						object.to = dataTimeSlot[i]['end_date'];

						disableDate.push( object );
					}
					
				}

			}
			

			var planDateTime = $("#obp_plan_date_time").flatpickr({
			    mode: "range",
			    altInput: true,
			    locale: obp_flatpickr_obj?.lang,
			    altFormat: calendar_object.date_format,
			    dateFormat: "Y-m-d",
			    disable: disableDate,
			    defaultDate: defaultDate,
			    onChange: [function(selectedDates){
			        const dateArr = selectedDates.map(date => this.formatDate(date, "Y-m-d"));
			        var [startDate, endDate] = dateArr;
			        $("#start_date").val( startDate );
			        $("#end_date").val( endDate );
			    }]
			});

			$(".obp_save_plan_form .obp_date_time i").off().on("click",function(){
				planDateTime.open();
			});

			// Business Time
			that.business_hours();

			// Choose Service
			that.choose_service();

			// Hide card

			$(document).on("click", function(e) {

				var business_hours_field = $(".obp_save_plan_form .business_hours_field");
				var choose_service_field = $(".obp_save_plan_form .choose_service_field");
				var ui_timepicker 		= $(".ui-timepicker-wrapper");

			    if (! ui_timepicker.is(e.target) && ui_timepicker.has(e.target).length === 0 &&
			    	!business_hours_field.is(e.target) && business_hours_field.has(e.target).length === 0){
			        $(".obp_save_plan_form #business_hours_type").removeClass('is-active');
					$(".obp_save_plan_form .business_hours_card").fadeOut();
			    }

			    // if ( ! choose_service_field.is(e.target) && choose_service_field.has(e.target).length === 0 ) {
			    // 	$(".obp_save_plan_form #service_label").removeClass('is-active');
				// 	$(".obp_save_plan_form .service_ids_card").fadeOut();
			    // }

			});

		}

		remove_plan(){
			var that = this;
			$(".obp_remove_plan").off().on("click",function(e){
				e.preventDefault();

				var $this = $(this);
				var id 			= $this.attr('data-id');
				var nonce 		= $this.attr('data-nonce');
				var dataMess 	= JSON.parse( $(".obp_plan_list_items").attr('data-mess') );
				var dataBtn 	= JSON.parse( $(".obp_plan_list_items").attr('data-btn') );

				new $.Zebra_Dialog(dataMess.confirm_remove,
				    {
				        type: "question",
				        buttons: [
				        	{
				        		caption: dataBtn.yes,
				        		callback: function(){
				        			var data = {
				        				'action': 'obp_remove_plan',
				        				'nonce': nonce,
				        				'id': id,
				        			};

				        			$(".obp_manage_plan_wrap").block({
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
				        		}
				        	},
				        	{
				        		caption: dataBtn.no,
				        	}
			        	],
				    }
				);
			});
		}

		edit_plan(){
			var that = this;
			$(".obp_edit_plan").off().on('click',function(e){
				e.preventDefault();
				var $this = $(this);
				var id = $this.attr('data-id');
				var nonce = $this.attr('data-nonce');
				var $wrap = $this.closest('.obp_plan_item');

				var data = {
					'action': 'obp_edit_plan',
					'nonce': nonce,
					'id': id,
				};

				$(".obp-content").block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url, data, function(res){

					$(".add-plan-wrapper").html('');
					$(".edit-plan-wrapper").html('');

					$wrap.find('.edit-plan-wrapper').html(res);
					that.save_plan();
					that.add_plan_form_init( id );
					that.close_edit_form();
					that.add_service_special();
					that.remove_service_time();
					that.add_service_time();
					that.init_service_time();

					$('.obp-content').unblock();
				} );

			});
		}

		show_info(){
			var that = this;
			$('.obp_plan_item .date-column').off().on('click', function(){
				if (typeof OBP_Plan_Info !== 'undefined') {
	        		OBP_Plan_Info.close();
	        	}

	        	var id = $(this).attr('data-plan-id');
	        	var data = {
	        		'action': 'obp_show_plan_info',
	        		'id': id,
	        		'nonce': ajax_object.nonce
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
	                    window.OBP_Plan_Info = new $.Zebra_Dialog(data, {
	                        buttons: false,
	                        type: false,
	                        width: 600,
	                        custom_class: 'obp_show_plan_info'
	                    });

	                }
	            });
			});
		}


		add_custom_time(){

			var that = this;

			$(".obp_add_business_time").off().on("click",function(e){
				e.preventDefault();

				var nonce = $(this).attr("data-nonce");
				var data = {
					'action': 'obp_add_business_time',
					'nonce': nonce,
				};

				$(".business_hours_card").block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url, data, function(res){
					$(".business_hours_field .custom_time_items").append(res);
					that.business_timepicker_init();
					that.remove_custome_time();

					$(".business_hours_card").unblock();
				} );

			});
		}

		add_plan(){
			var that = this;
			$('input[name="obp_add_plan"]').off().on( 'click', function(e){
				e.preventDefault();
				var $this = $(this);
				var nonce = $this.attr('data-nonce');

				var data = {
					'action': 'obp_add_plan',
					'nonce': nonce,
				};


				$(".obp-content").block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});


				$.post( ajax_object.ajax_url, data, function(res){
					$(".add-plan-wrapper").html('');
					$(".edit-plan-wrapper").html('');
					$(".add-plan-wrapper").html(res);
					that.save_plan();
					that.add_plan_form_init();
					that.close_add_form();
					that.add_service_special();
					that.remove_service_time();
					that.add_service_time();
					that.init_service_time();
					
					$('.obp-content').unblock();
				} );


			} );
		}


		close_add_form(){
			var that = this;
			$(".obp_remove_form").off().on("click",function(e){
				e.preventDefault();
				const wrapper = $(this).closest(".add-plan-wrapper");
				wrapper.html('');
			});
		}

		close_edit_form(){
			var that = this;
			$(".obp_remove_form").off().on("click",function(e){
				e.preventDefault();
				const wrapper = $(this).closest(".edit-plan-wrapper");
				wrapper.html('');
			});
		}

		save_plan(){
			var that = this;
			$('.obp_save_plan_form').off().on("submit",function(e){
				e.preventDefault();
				var $this = $(this);
				var nonce = $this.find("#obp_save_plan_nonce").val();
				var startDate 	= $this.find("#start_date").val();
				var endDate 	= $this.find("#end_date").val();
				var serviceType = $this.find('input[name="service_type"]:checked').val();
				var special_service = $this.find('input[name="special_service"]:checked').val();
				var status = $this.find("#status").val();
				var planID = $this.find('#plan_id').val();
				var serviceIDs 		= $this.find('#services_id').val();
				var businessType 	= $('input[name="business_time"]:checked').val();
				var businessHours 	= [];
				var dataError 	= JSON.parse( $('.save_plan_errors').attr('data-error') );
				var mess 		= [];
				var messHTML 	= '';


				if ( startDate == "" || endDate == "" ) {
					mess.push( dataError.empty_date );
				}

				var checkBusinessTimeEmpty 	= true;
				var checkBusinessTimeValid 	= true;
				var checkBusinessTimeDay 	= true;

				var startTimeArr = [];
				var endTimeArr = [];
				var businessTimeArr = [];

				var data_special_services = [];
				


				$('.obp_special_service_item').each( function(i,el){
					var service_id = $(el).attr('data-service-id');
					var svStartTimeArr = [];
					var svEndTimeArr = [];
					var serviceTimeArr = [];

					$(el).find('.obp_service_time_item').each( function(j,ele){
						var startTime = $(ele).find('.start_time').attr('data-time');
						var endTime = $(ele).find('.end_time').attr('data-time');

						serviceTimeArr.push( { 'start_hour': startTime, 'end_hour': endTime } );
						svStartTimeArr.push( startTime );
						svEndTimeArr.push( endTime );

					} );

					data_special_services.push( {
						'id': service_id,
						'time': serviceTimeArr
					} );

					if ( svStartTimeArr.length > 0 ) {
						for (var i = 0; i < svStartTimeArr.length; i++) {
							var startItem 	= svStartTimeArr[i];
							var endItem 	= svEndTimeArr[i];
							var j = i + 1;
							var nextStartItem = svStartTimeArr[j];

							if ( startItem == 0 && endItem == 0 ) {
								checkBusinessTimeEmpty = false;
							} else {
								if ( startItem >= endItem ) {
									checkBusinessTimeValid = false;
								}

								if ( nextStartItem ) {
									if ( endItem > nextStartItem ) {
										checkBusinessTimeDay = false;
									}
								}
							}
						}
					}

				} );


				if ( businessType !== 'full_time' ) {

					var customTimeItem = $(".business_hours_card .custom_time_item");
					if ( customTimeItem.length > 0 ) {
						customTimeItem.each( function(i,el){
							var startTime 	= parseInt( $(el).find(".start_time").attr("data-time") );
							var endTime 	= parseInt( $(el).find(".end_time").attr("data-time") );

							businessHours.push( { 'start_hour': startTime, 'end_hour': endTime } );

						} );
					}

					if ( businessHours.length ) {
						for ( var i = 0; i < businessHours.length; i++ ) {
							var businessTime = businessHours[i];
							var startTime = businessTime['start_hour'];
							var endTime = businessTime['end_hour'];

							businessTimeArr.push( {'start_hour': startTime, 'end_hour': endTime} );
							startTimeArr.push( startTime );
							endTimeArr.push( endTime );

						}
					}

					if ( startTimeArr.length > 0 ) {
						for (var i = 0; i < startTimeArr.length; i++) {
							var startItem 	= startTimeArr[i];
							var endItem 	= endTimeArr[i];
							var j = i + 1;
							var nextStartItem = startTimeArr[j];

							if ( startItem == 0 && endItem == 0 ) {
								checkBusinessTimeEmpty = false;
							} else {
								if ( startItem >= endItem ) {
									checkBusinessTimeValid = false;
								}

								if ( nextStartItem ) {
									if ( endItem > nextStartItem ) {
										checkBusinessTimeDay = false;
									}
								}
							}
						}
					}

				}


				if ( ! checkBusinessTimeEmpty ) {
					mess.push( dataError.empty_time );
				}

				if ( ! checkBusinessTimeValid ) {
					mess.push( dataError.invalid_time );
				}

				if ( ! checkBusinessTimeDay ) {
					mess.push( dataError.invalid_time_day );
				}


				if ( mess.length > 0 ) {
					for (var i = 0; i < mess.length; i++) {
						messHTML += '<div class="obp_alert_danger">'+ mess[i] + '</div>';
					}

					$('.obp_save_plan_notice').html( messHTML );

					var offsetTop = $this.offset().top - 50;

					$('html, body').animate({
				        scrollTop: offsetTop
				    }, 1000);

					return false;
				}


				var data = {
					'action': 'obp_save_plan',
					'nonce': nonce,
					'plan_id': planID,
					'start_date': startDate,
					'end_date': endDate,
					'status': status,
					'service_ids': serviceIDs,
					'service_type': serviceType,
					'business_type': businessType,
					'business_hours': businessTimeArr,
					'data_special_services': data_special_services,
					'special_service': special_service,
				};


				$this.block({
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
	
	window.OBP_Plan = new OBP_Plan();

})(jQuery);