(function ($) {


	class OBP_All_Schedules {

		constructor(){
			this.filter_schedule();
			this.calendar_all_schedules();
		}


		calendar_all_schedules(){
			var that = this;
			var firstDay = OBP_Frontend.get_first_day_of_week();

			var timeFormatRegex = new RegExp("[Aa]");
			var checkHour12 = timeFormatRegex.test(calendar_object.time_format);

			var calendarEl = document.getElementById('all_schedules_calendar');
			var checkRTL = $("body").hasClass("rtl") ? 'rtl' : 'ltr';
			var initDate = $(calendarEl).attr("data-init-date");
			var timeStep = $(calendarEl).attr("data-timestep");
			var dataCalendar = JSON.parse( $(calendarEl).attr("data-calendar") );
			var eventSources = [];

			if ( dataCalendar ) {
				for (let key in dataCalendar) {
					var data = {
						'events': dataCalendar[key]['events'],
						'color': dataCalendar[key]['color'],
					};
					eventSources.push( data );
				}
			}

			var DAY_NAMES = obp_fullcalendar?.weekdays;

			var calendar = new FullCalendar.Calendar(calendarEl, {
				initialDate: initDate,
				initialView: 'timeGridWeek',
				buttonText: obp_fullcalendar?.button_text,
				weekText: obp_fullcalendar?.week,
				allDayText: obp_fullcalendar?.all_day,
				moreLinkText: function(n) {
			        return '+ '+obp_fullcalendar?.more+' ' + n;
			    },
			    noEventsText: obp_fullcalendar?.no_events,
				direction: checkRTL,
				firstDay: firstDay,
				nowIndicator: true,
				headerToolbar: {
					left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridWeek,timeGridDay,listWeek'
				},
				slotDuration: timeStep,
				navLinks: true,
				editable: true,
				selectable: true,
				selectMirror: true,
				dayMaxEvents: true,
				eventSources: eventSources,
				slotLabelFormat: [
			        {
			            hour: '2-digit',
			            minute: '2-digit',
			            hour12: checkHour12
			        }
		        ],
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
		        	var theDate = arg.date.getDate();
		        	var theMonth = arg.date.getMonth() + 1;
			    	return DAY_NAMES[theDay]+' ('+theDate+'/'+theMonth+')';
			    },
			    eventClick: function( info ){
			    	var order_id = info.event.extendedProps?.order_id;
			    	var staff_id = info.event.extendedProps?.staff_id;
			    	var service_id = info.event.extendedProps?.service_id;
			    	var start_date = info.event.extendedProps?.start_date;
			    	var end_date = info.event.extendedProps?.end_date;
			    	var data = {
			    		'action': 'obp_info_schedule',
			    		'nonce': ajax_object.nonce,
			    		'order_id': order_id,
			    		'staff_id': staff_id,
			    		'service_id': service_id,
			    		'start_date': start_date,
			    		'end_date': end_date,
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
		                	new $.Zebra_Dialog(
							    data,
							    {
							        type: false,
							        buttons: false,
							    }
							);
		                }
		            });
			    }
			});


			calendar.render();
		}
		get_format_date(){

		}
		show_loader( $flag = true ){
			if ( $flag ) {
				$(".obp_all_schedules_wrap").block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null
				    },
				});
			} else {
				$(".obp_all_schedules_wrap").unblock();
			}
		}

		filter_schedule(){
			var that = this;
			$(".obp_all_schedules_filter .filter_main").off().on("submit",function(e){
				e.preventDefault();

				var $wrap = $(".obp_all_schedules_filter");
				var nonce = $wrap.attr("data-nonce");
				var customerName = $("#obp_customer_name").val();
				var staffID = $("#obp_staff").val();

				var data = {
					'action': 'obp_filter_all_schedule',
					'nonce': nonce,
					'customer_name': customerName,
					'staff_id': staffID,
				};

				that.show_loader();

				$.post( ajax_object.ajax_url, data, function(res){

					if ( res ) {
						$(".obp_all_schedule_calendar_content").html( res );
						that.calendar_all_schedules();
					}
					
					that.show_loader( false );
				} );

			});
		}

	}

	window.OBP_All_Schedules = new OBP_All_Schedules();

})(jQuery);