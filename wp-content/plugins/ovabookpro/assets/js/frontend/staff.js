(function ($) {

	OBP_Frontend_Staff = {
		init: function() {
            // Avatar staff
            this.avatar_staff();

            // Save staff
			this.save_staff();

            this.edit_dayoff();

            this.show_dayoff();

            // Hide card
            this.hide_card_custom_time();

            this.show_calendar();

            this.delete_staff();

            this.filter();

            this.pagination();
		},

        filter: function(){
            const that = this;
            $(".obp-search-name").off().on("keypress",function(e){
                if( e.which == 13 ) {
                    $(".obp_search_staff").trigger("click");
                }
            });


            $(".obp_search_staff").off().on("click",function(e){
                e.preventDefault();

                const name      = $(".obp-search-name").val();
                const sortby    = $("#user_orderby").val();
                const page      = $(".staff-pagination.current_page").attr("data-page");
                const data      = {
                    'action': 'obp_staff_load_data',
                    'nonce': ajax_object.nonce,
                    'name': name,
                    'sortby': sortby,
                    'page': page,
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
                        $(".obp_staff_list_container").html('');
                        $(".obp-pagination-wrap").html('');

                        $(".obp_staff_list_container").html( data?.staff_html );
                        $(".obp-pagination-wrap").html( data?.pagination_html );

                        that.init_events();
                    }
                });

            });

        },
        pagination: function(){
            const that = this;
            $(".staff-pagination .page_item").off().on("click", function(e){
                e.preventDefault();

                const page = parseInt( $(this).attr("data-page") );
                const name = $(".obp-search-name").val();
                const sortby = $("#user_orderby").val();
                const data = {
                    'action': 'obp_staff_load_data',
                    'nonce': ajax_object.nonce,
                    'name': name,
                    'sortby': sortby,
                    'page': page,
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
                        $(".obp_staff_list_container").html('');
                        $(".obp-pagination-wrap").html('');

                        $(".obp_staff_list_container").html( data?.staff_html );
                        $(".obp-pagination-wrap").html( data?.pagination_html );

                        that.init_events();
                    }
                });
            });
        },


        init_events: function(){

            this.edit_dayoff();

            this.show_dayoff();

            this.show_calendar();

            this.delete_staff();

            this.filter();

            this.pagination();
        },

        show_calendar(){
            $('.show_calendar').off().on('click', function(e) {
                e.preventDefault();

                const $this = $(this);
                const action_wrap = $this.closest('.obp-data-action');
             
                const user_id = action_wrap.attr('data-user-id');

                $('.staff-schedule-wrapper[data-user-id="'+user_id+'"]').slideToggle();

                $this.toggleClass("is-active");

                var calendarEl = document.getElementById('obp_staff_calendar_' + user_id);

                var checkRTL     = $("body").hasClass("rtl") ? 'rtl' : 'ltr';

                var dataCalendar = JSON.parse( $(calendarEl).attr('data-calendar') );
                var timeStep = $(calendarEl).attr('data-timestep');
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

                var DAY_NAMES = obp_fullcalendar?.weekdays;
                var initDate = $('.staff-list-wrapper').attr("data-date-init");

                var calendar = new FullCalendar.Calendar(calendarEl,{
                    initialDate: initDate,
                    initialView: 'timeGridWeek',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'timeGridWeek,timeGridDay,listWeek' // user can switch between the two
                    },
                    buttonText: obp_fullcalendar?.button_text,
                    weekText: obp_fullcalendar?.week,
                    allDayText: obp_fullcalendar?.all_day,
                    moreLinkText: function(n) {
                        return '+ '+obp_fullcalendar?.more+' ' + n;
                    },
                    noEventsText: obp_fullcalendar?.no_events,
                    direction: checkRTL,
                    firstDay: firstDay,
                    eventSources: dataCalendar,
                    slotLabelFormat: [
                        {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: checkHour12
                        }
                    ],
                    nowIndicator: true,
                    navLinks: true,
                    editable: true,
                    selectable: true,
                    selectMirror: true,
                    dayMaxEvents: true,
                    allDaySlot: true,
                    slotDuration: timeStep,
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
            });
        },

        hide_card_custom_time: function(){
            $(document).on("click", function(e) {

                var off_hours_field = $(".day-off-form .off_hours_field");
                var choose_service_field = $(".day-off-form .choose_service_field");
                var ui_timepicker       = $(".ui-timepicker-wrapper");

                if (! ui_timepicker.is(e.target) && ui_timepicker.has(e.target).length === 0 &&
                    !off_hours_field.is(e.target) && off_hours_field.has(e.target).length === 0){
                    $(".day-off-form #off_hours_type").removeClass('is-active');
                    $(".day-off-form .off_hours_card").fadeOut();
                }

                if ( ! choose_service_field.is(e.target) && choose_service_field.has(e.target).length === 0 ) {
                    $(".day-off-form #service_label").removeClass('is-active');
                    $(".day-off-form .service_ids_card").fadeOut();
                }

            });
        },

        show_dayoff: function(){
            const that = this;
            $(document).find('.obp-content-staff .show_holidays').off().on('click', function(e) {
                e.preventDefault();

                var $this = $(this);
                var action_wrap = $this.closest('.obp-data-action');
                var user_id = action_wrap.attr('data-user-id');
                $('.obp_staff_day_off[data-user-id="'+user_id+'"]').slideToggle();
                $this.toggleClass('is-active');
                
                that.show_add_dayoff();
                that.remove_day_off();
            });
        },


        update_dayoff: function(){
            const that = this;
            $('input[name="obp_update_day_off"]').off().on("click",function(e){
                e.preventDefault();
                const $this = $(this);
                const form = $this.closest(".day-off-form");
                const dayOffId  = form.find('input[name="day_off_id"]').val();
                const startDate = form.find('input[name="day_off_start"]').val();
                const endDate   = form.find('input[name="day_off_end"]').val();
                var offType     = $('input[name="off_time"]:checked').val();
                const staffId   = $this.closest(".obp_staff_day_off").attr('data-user-id');
                var offHours    = [];
                var mess        = [];
                var messHTML    = '';

                $('.obp_day_off_messages').html('');

                if ( startDate == "" || endDate == "" ) {
                    mess.push( obp_staff_obj.empty_date );
                }

                var checkOffTimeEmpty  = true;
                var checkOffTimeValid  = true;
                var checkOffTimeDay    = true;

                var startTimeArr = [];
                var endTimeArr = [];
                var offTimeArr = [];

                if ( offType !== 'full_time' ) {

                    var customTimeItem = $(".off_hours_card .custom_time_item");
                    if ( customTimeItem.length > 0 ) {
                        customTimeItem.each( function(i,el){
                            var startTime   = parseInt( $(el).find(".start_time").attr("data-time") );
                            var endTime     = parseInt( $(el).find(".end_time").attr("data-time") );

                            offHours.push( { 'start_hour': startTime, 'end_hour': endTime } );

                        } );
                    }

                    if ( offHours.length ) {
                        for ( var i = 0; i < offHours.length; i++ ) {
                            var offTime = offHours[i];
                            var startTime = offTime['start_hour'];
                            var endTime = offTime['end_hour'];

                            offTimeArr.push( {'start_hour': startTime, 'end_hour': endTime} );
                            startTimeArr.push( startTime );
                            endTimeArr.push( endTime );

                        }
                    } else {
                        checkOffTimeEmpty = false;
                    }


                    if ( startTimeArr.length > 0 ) {
                        for (var i = 0; i < startTimeArr.length; i++) {
                            var startItem   = startTimeArr[i];
                            var endItem     = endTimeArr[i];
                            var j = i + 1;
                            var nextStartItem = startTimeArr[j];

                            if ( startItem == 0 && endItem == 0 ) {
                                checkOffTimeEmpty = false;
                            } else {
                                if ( startItem >= endItem ) {
                                    checkOffTimeValid = false;
                                }

                                if ( nextStartItem ) {
                                    if ( endItem > nextStartItem ) {
                                        checkOffTimeDay = false;
                                    }
                                }
                            }
                        }
                    }

                }


                if ( ! checkOffTimeEmpty ) {
                    mess.push( obp_staff_obj.empty_time );
                }

                if ( ! checkOffTimeValid ) {
                    mess.push( obp_staff_obj.invalid_time );
                }

                if ( ! checkOffTimeDay ) {
                    mess.push( obp_staff_obj.invalid_time_day );
                }


                if ( mess.length > 0 ) {
                    for (var i = 0; i < mess.length; i++) {
                        messHTML += '<div class="obp_alert_danger">'+ mess[i] + '</div>';
                    }

                    $('.obp_day_off_messages').html( messHTML );

                    var offsetTop = $this.closest(".day-off-wrapper").offset().top - 50;

                    $('html, body').animate({
                        scrollTop: offsetTop
                    }, 1000);

                    return false;
                }


                const data = {
                    'action': 'obp_save_day_off',
                    'nonce': ajax_object.nonce,
                    'start_date': startDate,
                    'end_date': endDate,
                    'staff_id': staffId,
                    'time': offType,
                    'hour_off': offTimeArr,
                    'day_off_id': dayOffId,
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
                        window.location.reload(true)
                    }
                });


            });
        },

        remove_day_off_form: function(){
            $(".obp_remove_form_day_off").off().on("click",function(e){
                e.preventDefault();
                $(this).closest(".day-off-form").html('');
            });
        },

        timepicker_init( wrapper, dayOffId = '' ){

            const dataDate = JSON.parse( wrapper.find('input[name="all_dayoff"]').val() );
            const startDate = wrapper.find('input[name="day_off_start"]').val();
            const endDate = wrapper.find('input[name="day_off_end"]').val();
            var disableDate = [];

            if ( ! $.isEmptyObject( dataDate ) ) {
                for (var i = 0; i < dataDate.length; i++) {
                    const item = dataDate[i];
                    if ( item['id'] != dayOffId ) {
                        disableDate.push({
                            'from': item['from'],
                            'to': item['to'],
                        });
                    }
                }
            }


            $('input[name="day_off_start"]').flatpickr({
                'dateFormat': "Y-m-d",
                'locale': obp_flatpickr_obj?.lang,
                "plugins": [new rangePlugin({ input: "input[name='day_off_end']"})],
                'disableMobile': true,
                'disable': disableDate,
            });
        },

        custom_time_init(){
            $('.off_hours_field input[name="off_time"]').off().on('change',function(){

                if ( $(this).val() == 'custom_time' ) {
                    $(".custome_time_card").fadeIn();
                } else {
                    $(".custome_time_card").fadeOut();
                }

                var dataLabel = $(this).attr("data-label");
                $(".off_hours_field #off_hours_type").val( dataLabel );

            });

            var offTime = $('.off_hours_field input[name="off_time"]:checked').val();

            if ( offTime == 'custom_time' ) {
                $(".custome_time_card").fadeIn();
            } else {
                $(".custome_time_card").fadeOut();
            }

            $(".off_hours_field #off_hours_type").off().on('click', function(e){
                e.preventDefault();

                if ( $(this).hasClass('is-active') ) {
                    $(this).removeClass('is-active');
                    $(".off_hours_field .off_hours_card").fadeOut();
                } else {
                    $(this).addClass('is-active');
                    $(".off_hours_field .off_hours_card").fadeIn();
                }

            });
        },

        show_add_dayoff: function(){
            const that = this;
            $('input[name="obp_add_day_off"]').off().on("click",function(e){
                e.preventDefault();
                const $this = $(this);
                const wrapper = $this.closest(".day-off-wrapper");
                const data = {
                    'action': 'obp_add_day_off',
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
                        wrapper.find('.day-off-form').html('');
                        wrapper.find('.day-off-form').html(data);

                       
                        that.timepicker_init( wrapper );

                        that.custom_time_init();

                        that.off_timepicker_init();

                        that.add_custom_time();

                        that.remove_custome_time();

                        that.update_dayoff();

                        that.remove_day_off_form();
                    }
                });
            });
        },

        off_timepicker_init: function(){
            var timeFormat = calendar_object.time_format;
            // Business Time
            var isRTL = $("body").hasClass("rtl");

            var orientation = isRTL ? 'r' : 'l';

            $('.off_hours_card .off_custom_time').timepicker({
                    'timeFormat': timeFormat,
                    'noneOption': true,
                    'orientation': orientation,
                    'step': 15,
                    'disableTextInput': true,
                    'minTime': '6:00am',
                    'maxTime': '5:45am',
                });

            $('.off_hours_card .off_custom_time').on('changeTime',function(){
                var secondTime = $(this).timepicker('getSecondsFromMidnight');
                $(this).attr("data-time", secondTime );
            });
        },

        add_custom_time: function(){
            const that = this;


            $(".obp_add_off_time").off().on("click",function(e){
                e.preventDefault();

                var data = {
                    'action': 'obp_add_off_time',
                    'nonce': ajax_object.nonce,
                };

                $(".off_hours_card").block({
                    message: null,
                    overlayCSS:  { 
                        backgroundColor: '#fff', 
                        opacity: 0.3, 
                        cursor: null 
                    },
                });

                $.post( ajax_object.ajax_url, data, function(res){
                    $(".off_hours_field .custom_time_items").append(res);
                    that.off_timepicker_init();
                    that.remove_custome_time();

                    $(".off_hours_card").unblock();
                } );

            });
        },

        remove_day_off: function(){
            const that = this;
            $(".obp_action_delete_day_off").off().on("click",function(e){
                e.preventDefault();

                const wrapper = $(this).closest(".day_off_action_wrap");
                const dayOffId = wrapper.find('input[name="dayoff_id"]').val();

                const data = {
                    'action': 'obp_delete_day_off',
                    'nonce': ajax_object.nonce,
                    'day_off_id': dayOffId
                };


                new $.Zebra_Dialog(obp_staff_obj.confirm_delete,
                    {
                        type: "question",
                        title: null,
                        buttons: [
                            {caption: obp_staff_obj.yes, callback: function() {
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
                            {caption: obp_staff_obj.no, callback: function() {

                            }},
                        ]
                    }
                );

               

            });
        },

        remove_custome_time: function(){
            $(".off_hours_card .obp_remove_custom_time").off().on("click",function(e){
                e.preventDefault();

                var targetItem = $(this).closest(".custom_time_item");
                targetItem.remove();
                return false;
            });
        },

        edit_dayoff: function(){
            const that = this;
            $('.obp_action_edit_day_off').off().on('click', function(e) {
                e.preventDefault();
                var $this = $(this);
                const wrapper = $this.closest(".day-off-wrapper");

                $(".day-off-form").html('');

                const dayOffId = $this.closest('.day_off_action_wrap').find('input[name="dayoff_id"]').val();

                const data = {
                    'action': 'obp_edit_day_off',
                    'nonce': ajax_object.nonce,
                    'day_off_id': dayOffId
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
                        wrapper.find(".day-off-form").html('');
                        wrapper.find(".day-off-form").html(data);

                        that.timepicker_init( wrapper, dayOffId );

                        that.custom_time_init();

                        that.off_timepicker_init();

                        that.add_custom_time();

                        that.remove_custome_time();

                        that.update_dayoff();

                        that.remove_day_off_form();
                    }
                });
           

            });
        },

        avatar_staff: function() {
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

        save_staff: function(){
            const that = this;
            $(document).find('.obp-content-staff .obp-form-submit .obp_button').off().on('click', function(e) {
                e.preventDefault();
                const container = $('.obp-content-staff');


                $(".obp_message_wrapper").html('');

                const avatar      = container.find('input[name="staff_avatar"]').val();
                const user_id     = container.find('#user_id').val();
                const username    = container.find('input[name="username"]').val();

                const email   	  = container.find('input[name="email"]').val();
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

                if ( user_id && nickname == '' ) {
                    messages += messageErrorHTML.replace( "[message]", obp_staff_obj.nickname_req );
                }

                if ( password == '' && container.find('input[name="password"]:required').length ) {
                    messages += messageErrorHTML.replace( "[message]", obp_staff_obj.password_req );
                }

                if ( ! role ) {
                    messages += messageErrorHTML.replace( "[message]", obp_staff_obj.role_req );
                }

                const data = {
                    'action': 'obp_save_edit_staff',
                    'nonce': ajax_object.nonce,
                    'id': user_id,
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
                    $(".obp_message_wrapper").append( messages );

                    $('html, body').animate({
                        scrollTop: $(".obp_message_wrapper").offset().top - 100
                    }, 1000);
                    return false;
                }


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

        delete_staff: function(){
            const that = this;
            $(".obp_action_delete_staff").off().on("click", function(e){
                e.preventDefault();
                
                const wrapper = $(this).closest(".obp-data-action");
                const staffId = wrapper.find('input[name="user_id"]').val();
                const data = {
                    'action': 'obp_delete_staff',
                    'nonce': ajax_object.nonce,
                    'staff_id': staffId,
                };

                new $.Zebra_Dialog(obp_staff_obj.confirm_delete,
                    {
                        type: "question",
                        title: null,
                        buttons: [
                            {caption: obp_staff_obj.yes, callback: function() {
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
                            {caption: obp_staff_obj.no, callback: function() {

                            }},
                        ]
                    }
                );
            });
        },

        show_loader( flag = true, container = '.obp-content-staff' ){
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
	
    OBP_Frontend_Staff.init();
    
})(jQuery);