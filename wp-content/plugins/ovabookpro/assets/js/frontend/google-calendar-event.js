(function ($) {

	async function obp_google_calendar_get_events( order_id ){

		return $.post( {
			url: ajax_object.ajax_url,
			data: {
				'action': 'obp_google_calendar_get_events',
				'order_id': order_id,
				'nonce': ajax_object.nonce
			}
		} );
	}

	$("#obp_add_google_calendar").off().on('click', function(e){
		e.preventDefault();
		var order_id = $(this).attr('data-order-id');
		tokenClient.callback = async (resp) => {
			if ( resp.error !== undefined ) {
				throw (resp);
			}
			var events = await obp_google_calendar_get_events( order_id );
			var success_mess = obp_google_calendar?.success_mess;
			const batch = gapi.client.newBatch();

			$("#obp_add_google_calendar").block({
				message: null,
				overlayCSS:  { 
			        backgroundColor: '#fff', 
			        opacity: 0.3, 
			        cursor: null 
			    },
			});

			events.map((r, j) => {
				batch.add(gapi.client.calendar.events.insert({
					'calendarId': 'primary',
					'resource': events[j]
				}))
			});

			batch.then(function(){
				$("#obp_add_google_calendar").unblock();
				$.Zebra_Dialog(success_mess,
	            	{
	            		auto_close: 3000,
			            buttons: false,
			            modal: false,
			            type: "confirmation"
	            	}
        		);
			});
		};

		if ( gapi.client.getToken() === null ) {
			// Prompt the user to select a Google Account and ask for consent to share their data
			// when establishing a new session.
			tokenClient.requestAccessToken({prompt: 'consent'});
		} else {
			// Skip display of account chooser and consent dialog for an existing session.
			tokenClient.requestAccessToken({prompt: ''});
		}
	});


	$("#obp_add_to_calendar_popup").off().on('click', function(e){
		e.preventDefault();


		$("#obp_add_to_calendar_popup").block({
            message: null,
            overlayCSS: {
                backgroundColor: '#fff',
                opacity: '0.5',
                cursor: null
            }
        });

		var data = {
			'action': 'obp_add_to_calendar_popup',
			'nonce': ajax_object.nonce
		};

		$.ajax({
	        url: ajax_object.ajax_url,
	        complete: function( jqXHR, textStatus){
	        	$("#obp_add_to_calendar_popup").unblock();
	        },
	        data: data,
	        method: 'POST',
	        type: 'POST',
	        error: function( jqXHR, textStatus, errorThrown){
	            console.error( errorThrown );
	        },
	        success: function(data){

	        	window.Calendar_Popup = new $.Zebra_Dialog(data, {
                    type: false,
                    custom_class: "obp_calendar_popup",
                    buttons: false,
                    width: 500,
                    height: 230,
                });

	        	// init date input
                $('#calendar_start_date').flatpickr({
                    locale: obp_flatpickr_obj?.lang,
                    plugins: [new rangePlugin({ input: '#calendar_end_date'})],
                    disableMobile: true,
                    appendTo: $('.obp_add_to_calendar_wrapper')[0],
                    onChange: [function(selectedDates){
				        const dateArr = selectedDates.map(date => this.formatDate(date, "Y-m-d"));
				        var [startDate, endDate] = dateArr;
				        $("#cal_start_date").val( startDate );
				        $("#cal_end_date").val( endDate );
				    }]
                });


                calendar_add_events_init();
	        }
	    });

	});


	async function obp_order_calendar_add_events( data ){
		return $.post( {
			url: ajax_object.ajax_url,
			data: data
		} );
	}

	async function obp_order_ical_add_events( data ){
		return $.post( {
			url: ajax_object.ajax_url,
			data: data
		} );
	}

	function calendar_add_events_init(){
		$('#obp_order_calendar_add_events').off().on('click', async function(){

			var startDate = $("#cal_start_date").val();
			var endDate = $("#cal_end_date").val();

			var req_mess = obp_google_calendar?.required;
			var empty_mess = obp_google_calendar?.empty;
			var success_mess = obp_google_calendar?.success_mess;
			$('.obp_cal_mess').html('');

			if ( startDate == '' || endDate == '' ) {
				$('.obp_cal_mess').html( '<p class="obp_cal_err">'+req_mess+'</p>' );
				return false;
			}

			$("#obp_order_calendar_add_events").block({
	            message: null,
	            overlayCSS: {
	                backgroundColor: '#fff',
	                opacity: '0.5',
	                cursor: null
	            }
	        });

			var data = {
				'action': 'obp_order_calendar_add_events',
				'nonce': ajax_object?.nonce,
				'start_date': startDate,
				'end_date': endDate,
			};

			var events = await obp_order_calendar_add_events( data );

			$("#obp_order_calendar_add_events").unblock();

			if ( $.isEmptyObject( events ) ) {
				$('.obp_cal_mess').html( '<p class="obp_cal_err">'+empty_mess+'</p>' );
				return false;
			}

			tokenClient.callback = async (resp) => {
				if ( resp.error !== undefined ) {
					throw (resp);
				}

				
				const batch = gapi.client.newBatch();

				events.map((r, j) => {
					batch.add(gapi.client.calendar.events.insert({
						'calendarId': 'primary',
						'resource': events[j]
					}))
				});

				batch.then(function(){
					$('.obp_cal_mess').html( '<p class="obp_cal_success">'+success_mess+'</p>' );
				});
			};

			if ( gapi.client.getToken() === null ) {
				// Prompt the user to select a Google Account and ask for consent to share their data
				// when establishing a new session.
				tokenClient.requestAccessToken({prompt: 'consent'});
			} else {
				// Skip display of account chooser and consent dialog for an existing session.
				tokenClient.requestAccessToken({prompt: ''});
			}

		});

		$("#obp_order_ical_add_events").off().on("click", async function(){

			var startDate = $("#cal_start_date").val();
			var endDate = $("#cal_end_date").val();

			var req_mess = obp_google_calendar?.required;
			var empty_mess = obp_google_calendar?.empty;
			var success_mess = obp_google_calendar?.success_mess;
			$('.obp_cal_mess').html('');

			if ( startDate == '' || endDate == '' ) {
				$('.obp_cal_mess').html( '<p class="obp_cal_err">'+req_mess+'</p>' );
				return false;
			}

			$("#obp_order_ical_add_events").block({
	            message: null,
	            overlayCSS: {
	                backgroundColor: '#fff',
	                opacity: '0.5',
	                cursor: null
	            }
	        });

	        var data = {
				'action': 'obp_order_ical_add_events',
				'nonce': ajax_object?.nonce,
				'start_date': startDate,
				'end_date': endDate,
			};

	        var events = await obp_order_ical_add_events( data );

	        if ( $.isEmptyObject( events ) ) {
				$('.obp_cal_mess').html( '<p class="obp_cal_err">'+empty_mess+'</p>' );
				$("#obp_order_ical_add_events").unblock();
				return false;
			}

	        var cal = ics();

			for (var i = 0; i < events.length; i++) {
        		cal.addEvent(events[i]['summary'], events[i]['description'], events[i]['location'], events[i]['start'], events[i]['end']);
        	}

			cal.download('Calendar');

			$("#obp_order_ical_add_events").unblock();
		});
	}

	
	$("#obp_add_ical").off().on("click", function(e){
		e.preventDefault();

		var order_id = $(this).attr('data-order-id');

		var data = {
			'action': 'obp_ical_get_events',
			'nonce': ajax_object?.nonce,
			'order_id': order_id,
		};

		$("#obp_add_ical").block({
            message: null,
            overlayCSS: {
                backgroundColor: '#fff',
                opacity: '0.5',
                cursor: null
            }
        });

		$.ajax({
	        url: ajax_object.ajax_url,
	        complete: function( jqXHR, textStatus){
	        	$("#obp_add_ical").unblock();
	        },
	        data: data,
	        method: 'POST',
	        type: 'POST',
	        error: function( jqXHR, textStatus, errorThrown){
	            console.error( errorThrown );
	        },
	        success: function(data){

	        	var cal = ics();

	        	if ( ! $.isEmptyObject( data ) ) {
	        		for (var i = 0; i < data.length; i++) {
		        		cal.addEvent(data[i]['summary'], data[i]['description'], data[i]['location'], data[i]['start'], data[i]['end']);
		        	}
	        	}
	        	
	        	cal.download('Calendar-'+order_id);
	        }
	    });
	});


	

})(jQuery);