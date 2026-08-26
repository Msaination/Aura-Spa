(function ($) {


	class OBP_Staff_Schedule {

		constructor(){
			// this.filter_schedule();
			this.calendar_staff_schedule();
		}


		calendar_staff_schedule(){

			var firstDay = 1;

			var timeFormatRegex = new RegExp("[Aa]");
			var checkHour12 = timeFormatRegex.test(calendar_object.time_format);


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

			var calendarEl = document.getElementById('staff_schedule_calendar');
			var checkRTL = $("body").hasClass("rtl") ? 'rtl' : 'ltr';
			var initDate = $(calendarEl).attr("data-init-date");
			var timeStep = $(calendarEl).attr('data-timestep');
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
				navLinks: true, // can click day/week names to navigate views
				editable: true,
				selectable: true,
				selectMirror: true,
				dayMaxEvents: true, // allow "more" link when too many events
				eventSources: eventSources,
				slotDuration: timeStep,
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
			    }
			});

			calendar.render();
		}

		filter_schedule(){
			var that = this;
			$(".obp_staff_schedule_filter .filter_main").off().on("submit",function(e){
				e.preventDefault();

				var $wrap = $(".obp_staff_schedule_filter");
				var nonce = $wrap.attr("data-nonce");
				var customerName = $("#obp_customer_name").val();

				var data = {
					'action': 'obp_filter_staff_schedule',
					'nonce': nonce,
					'customer_name': customerName,
				};

				$(".obp_staff_schedule_wrap").block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url, data, function(res){

					if ( res ) {
						$(".obp_staff_schedule_calendar_content").html( res );
						that.calendar_staff_schedule();
					}
					
					$(".obp_staff_schedule_wrap").unblock();
				} );

			});
		}

	}

	window.OBP_Staff_Schedule = new OBP_Staff_Schedule();

})(jQuery);