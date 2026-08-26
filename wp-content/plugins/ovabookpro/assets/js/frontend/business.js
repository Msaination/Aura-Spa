(function ($) {
	window.OBP_Frontend_Business = {
		init: function() {
            // Avatar business
            this.avatar_business();

            this.load_google_map();

            // Socials
            this.social_business();

            // Work hours
            this.work_hours();

            // Business hours
            this.business_hours();

            // Media Gallery ( main images, our works images)
            this.add_main_images();
            this.add_work_images();
            this.remove_media();

			// Save Business
			this.save_business();

            this.show_hide_map();

            this.inputTags();
		},

        load_google_map: function(){
            let map;
            let marker;
            let infoWindow;

            function getAddressComponent(address_components, key) {
                var value='';
                var postalCodeType = address_components.filter(aComp =>
                    aComp.types.some(typesItem => typesItem === key))
                if (postalCodeType != null && postalCodeType.length > 0)
                    value = postalCodeType[0]?.long_name ? postalCodeType[0]?.long_name : postalCodeType[0]?.longText
                return value;
            }

            function geocodePosition(pos){
               geocoder = new google.maps.Geocoder();
               geocoder.geocode
                ({
                    latLng: pos
                }, 
                    function(results, status){
                        if (status == google.maps.GeocoderStatus.OK){
                            const full_address = results[0].formatted_address;
                            const addressComponents = results[0].address_components;
                            const postcode = getAddressComponent( addressComponents, 'postal_code' );
                            const country_code = getAddressComponent( addressComponents, 'country' );
                            const state = getAddressComponent( addressComponents, 'administrative_area_level_1' );
                            const city = getAddressComponent( addressComponents, 'administrative_area_level_2' );

                            $('.obp_map input[name="country_code"]').val( country_code );
                            $('.obp_map input[name="state"]').val( state );
                            $('.obp_map input[name="postcode"]').val( postcode );
                            $('.obp_map input[name="city"]').val( city );
                            $('input[name="full_address"]').val( full_address );
                        }
                    }
                );
            }

            if ( map_object?.map_platform == 'google_map' && map_object?.enable_map == 'yes' ) {

            async function initMap() {

                // Request needed libraries.
                const { Map, InfoWindow } = await google.maps.importLibrary("maps");
                const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

                var lat = $('input[name="map_latitude"]').val();
                var lng = $('input[name="map_longitude"]').val();
                var placeAddress = $('input[name="full_address"]').val();
                if ( ! lat ) {
                    lat = parseFloat( '40.730610' );
                } else {
                    lat = parseFloat( lat );
                }

                if ( ! lng ) {
                    lng = parseFloat( '-73.935242' );
                } else {
                    lng = parseFloat( lng );
                }

                const position = {
                    'lat': lat, 'lng': lng
                };

                // Initialize the map.
                map = new google.maps.Map( document.getElementById("obp_enable_map"), {
                    center: position,
                    zoom: 13,
                    mapId: "BUSINESS",
                    mapTypeControl: false,
                });

                var options = {};

                if ( map_object.bounds ) {
                    options['componentRestrictions'] = { 'country': map_object.restrictions.map(v => v.toLowerCase()) };
                    options['locationBias'] = {
                        'radius': parseInt( map_object.radius ),
                        'center': position
                    }
                }

                const placeAutocomplete = new google.maps.places.PlaceAutocompleteElement( options );


                placeAutocomplete.id = "place-autocomplete-input";

                const card = document.getElementById("place-autocomplete-card");

                card.appendChild( placeAutocomplete );
                map.controls[google.maps.ControlPosition.TOP_LEFT].push( card );
                // Create the marker and infowindow
                infoWindow = new InfoWindow();

                marker = new google.maps.marker.AdvancedMarkerElement({
                    map,
                    position: position,
                    gmpDraggable: true,
                });

                marker.addListener("dragend", (event) => {
                    const position = marker.position;

                    infoWindow.close();
                    infoWindow.setContent(`Pin dropped at: ${position.lat}, ${position.lng}`);
                    infoWindow.open( marker.map, marker );
                    $('input[name="map_latitude"]').val( position.lat );
                    $('input[name="map_longitude"]').val( position.lng );

                    geocodePosition( position );
                });

                // Add the gmp-placeselect listener, and display the results on the map.

                placeAutocomplete.addEventListener("gmp-select", async ({ placePrediction }) => {
                    var place = placePrediction.toPlace();
                    await place.fetchFields({
                        fields: ["displayName", "formattedAddress", "location", "addressComponents"],
                    });
                    // If the place has a geometry, then present it on a map.
                    if ( place.viewport ) {
                        map.fitBounds( place.viewport );
                    } else {
                        map.setCenter( place.location );
                        map.setZoom(17);
                    }
                    place = place.toJSON();
                    const addressComponents = place?.addressComponents;


                    const postcode = getAddressComponent( addressComponents, 'postal_code' );
                    const country_code = getAddressComponent( addressComponents, 'country' );
                    const state = getAddressComponent( addressComponents, 'administrative_area_level_1' );
                    const city = getAddressComponent( addressComponents, 'administrative_area_level_2' );

                    var content =
                    '<div id="infowindow-content">' +
                    '<span id="place-displayname" class="title">' +
                    place.displayName +
                    "</span><br />" +
                    '<span id="place-address">' +
                    place.formattedAddress +
                    "</span>" +
                    "</div>";

                    $('input[name="full_address"]').val( place.formattedAddress );
                    $('input[name="map_latitude"]').val( place.location.lat );
                    $('input[name="map_longitude"]').val( place.location.lng );
                    $('.obp_map input[name="country_code"]').val( country_code );
                    $('.obp_map input[name="state"]').val( state );
                    $('.obp_map input[name="postcode"]').val( postcode );
                    $('.obp_map input[name="city"]').val( city );

                    updateInfoWindow( content, place.location );
                    marker.position = place.location;
                });
            }

                // Helper function to create an info window.
                function updateInfoWindow(content, center) {
                    infoWindow.setContent(content);
                    infoWindow.setPosition(center);
                    infoWindow.open({
                        map,
                        anchor: marker,
                        shouldFocus: false,
                    });
                }

                initMap();
            }
            
        },

        show_loader: function( flag = true, container = '.obp-content-business' ){
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

        inputTags: function(){
            const that = this;

            // Off submit form
            $(window).on('keypress', function(e){
                if ( e.which == 13 ) {
                    e.preventDefault();
                }
            });

            $('#business_tags').inputTags();

            $('.inputTags-field').on('keyup', function(){
    
                const $val = $(this).val();
                if ( $val != '' ) {
                    const data = {
                        'keyword': $val,
                        'action': 'obp_business_tags_complete',
                        'nonce': ajax_object.nonce,
                    };

                    that.show_loader( true, '.obp_business_tags_ajax' );

                    $.ajax({
                        url: ajax_object.ajax_url,
                        complete: function( jqXHR, textStatus){
                            that.show_loader( false, '.obp_business_tags_ajax' );
                        },
                        data: data,
                        method: 'POST',
                        type: 'POST',
                        error: function( jqXHR, textStatus, errorThrown){
                            console.error( errorThrown );
                        },
                        success: function(data){
                            $('.obp_business_tags_ajax').html('');
                            $('.obp_business_tags_ajax').html(data);
                            that.select_tag_complete();
                        }
                    });
                } else {
                    $('.obp_business_tags_ajax').html('');
                }
            });
        },

        select_tag_complete: function(){
            $('.obp_business_tag_complete .item').off().on('click', function(e){
                e.preventDefault();
                const name = $(this).attr('data-name');
                $('.inputTags-field').val( name );
                $('.inputTags-field').focus();

                $('.obp_business_tags_ajax').html('');
            });
        },

        show_hide_map: function(){
            const current = $('input[name="enable_map"]:checked').val();
            const container = $('.obp_map_container');
            if ( current == 'yes' ) {
                container.slideDown();
            }

            $('input[name="enable_map"]').on('change', function(){
                const val = $(this).val();
                const container = $('.obp_map_container');
                if ( val == 'yes' ) {
                    container.slideDown();
                } else {
                    container.slideUp();
                }
            });
        },

        avatar_business: function() {
            var obj = this;
            var business_avatar;
            $('.business_avatar .opb_button_add_media').on('click', function(e) {
                e.preventDefault();

                if (typeof business_avatar != 'undefined') {
                    business_avatar.close();
                }

                var that = $(this);

                business_avatar = wp.media({
                    title: $(this).data('uploader-title'),
                    button: {
                        text: $(this).data('button-text'),
                    },
                    library: {
                        type: ['image']
                    },
                    multiple: false
                });

                business_avatar.on('select', function() {
                    var selection = business_avatar.state().get('selection');

                    selection.map(function(attachment, i) {
                        attachment = attachment.toJSON();
                        that.closest('.business_avatar').find('.profile-image').html('<img src="' + attachment.sizes.full.url + '"><a href="#" class="remove_image"><i class="icon-close bookproicon-close"></i></a>');
                        that.closest('.business_avatar').find('input').val(attachment.id);
                    });

                    obj.remove_business_avatar();
                });

                business_avatar.on('open',function() {
                    var selection = business_avatar.state().get('selection');
                    var id = $('.business_avatar input[name="business_avatar"]').val();
                   
                    attachment = wp.media.attachment(id);
                    attachment.fetch();
                    selection.add(attachment ? [attachment] : []);
                });

                business_avatar.open();

            });

            /* Remove */
            $('.business_avatar .remove_image').on('click', function(e) {
                e.preventDefault();
                $(this).closest('.business_avatar').find('input').val('');
                $(this).parent().empty();
            });
        },

        remove_business_avatar: function(){
            /* Remove */
            $('.business_avatar .remove_image').on('click', function(e) {
                e.preventDefault();
                $(this).closest('.business_avatar').find('input').val('');
                $(this).parent().empty();
            });
        },

        social_business: function() {
            $(document).on('click', '.obp-content-business .add_social', function(e) {
                e.preventDefault();

                $.post( ajax_object.ajax_url, {
                    action: 'obp_add_social',
                }, function(response) {
                    $('.obp-content-business .social_list').append(response);
                });
            });

            $(document).on('click', '.obp-content-business .remove_social', function(e) {
                e.preventDefault();

                $(this).parents('.social_item').animate({ opacity: 0 }, 300, function() {
                    $(this).remove();
                });
            });
        },

        work_hours: function() {
            function work_hours_timepicker() {

                $('.obp-content-business input.work_hour').timepicker({
                    timeFormat: obp_business_obj.time_format,
                    step: obp_business_obj.time_step,
                    minTime : obp_business_obj.min_time,
                    listWidth: 1,
                    disableTextInput: true,
                    noneOption: true,
                });
            }

            work_hours_timepicker();

            $(document).on('click', '.obp-content-business .add_work_hour', function(e) {
                e.preventDefault();

                $.post( ajax_object.ajax_url, {
                    action: 'obp_add_work_hour',
                }, function(response) {
                    $('.obp-content-business .work-hours-wrapper').append(response);
                    work_hours_timepicker();
                });
            });

            $(document).on('click', '.obp-content-business .remove_work_hour', function(e) {
                e.preventDefault();

                $(this).parents('.work_hours_field').animate({ opacity: 0 }, 300, function() {
                    $(this).remove();
                });
            });
        },

        business_hours: function() {
            function business_hours_timepicker() {

                $('.obp-content-business input.business_hour').timepicker({
                    timeFormat: obp_business_obj.time_format,
                    step: obp_business_obj.time_step,
                    minTime : obp_business_obj.min_time,
                    listWidth: 1,
                    disableTextInput: true,
                    noneOption: true,
                });
            }

            business_hours_timepicker();

            $(document).on('click', '.obp-content-business .add_hour', function(e) {
                e.preventDefault();
                var that = $(this);

                $.post( ajax_object.ajax_url, {
                    action: 'obp_add_hour',
                }, function(response) {
                    that.closest('.business-hours-field-wrapper').find('.business_hours').append(response);
                    business_hours_timepicker();
                });
            });

            $(document).on('click', '.obp-content-business .remove_business_hour', function(e) {
                e.preventDefault();

                $(this).parents('.business-hour').animate({ opacity: 0 }, 300, function() {
                    $(this).remove();
                });
            });
                      
        },

        add_main_images: function() {

            $('.main_images .opb_button_add_media').on('click', function(e) {
                e.preventDefault();
                //create a new Library, base on defaults
                //you can put your attributes in
                var insertImage = wp.media.controller.Library.extend({
                    defaults :  _.defaults({
                        id: 'insert-image',
                        title: obp_business_obj.media_title,
                        allowLocalEdits: true,
                        displaySettings: true,
                        displayUserSettings: true,
                        multiple : true,
                        type : 'image'//audio, video, application/pdf, ... etc
                    }, wp.media.controller.Library.prototype.defaults )
                });

                //Setup media frame
                var frame = wp.media({
                    button : { text : obp_business_obj.media_button },
                    state : 'insert-image',
                    states : [
                        new insertImage()
                    ]
                });

                //on close, if there is no select files, remove all the files already selected in your main frame
                frame.on('close',function() {
                    var selection = frame.state('insert-image').get('selection');
                    if(!selection.length){
                        // #remove file nodes
                        $(".main_images .gallery_list").html('');
                    }
                });

                frame.on('select', function() {
                    var state = frame.state('insert-image');
                    var selection = state.get('selection');
                    var imageArray = [];

                    if ( ! selection ) return;

                    // #remove file nodes
                    $(".main_images .gallery_list").html('');

                    selection.each(function(attachment) {
                        var display = state.display( attachment ).toJSON();
                        var obj_attachment = attachment.toJSON()
                        var caption = obj_attachment.caption, options, html;
                        
                        // If captions are disabled, clear the caption.
                        if ( ! wp.media.view.settings.captions )
                            delete obj_attachment.caption;

                        display = wp.media.string.props( display, obj_attachment );

                        options = {
                            id: obj_attachment.id,
                            post_content: obj_attachment.description,
                            post_excerpt: caption
                        };

                        if ( display.linkUrl )
                            options.url = display.linkUrl;

                        if ( 'image' === obj_attachment.type ) {
                            html = wp.media.string.image( display );
                            _.each({
                            align: 'align',
                            size:  'image-size',
                            alt:   'image_alt'
                            }, function( option, prop ) {
                            if ( display[ prop ] )
                                options[ option ] = display[ prop ];
                            });
                        } else if ( 'video' === obj_attachment.type ) {
                            html = wp.media.string.video( display, obj_attachment );
                        } else if ( 'audio' === obj_attachment.type ) {
                            html = wp.media.string.audio( display, obj_attachment );
                        } else {
                            html = wp.media.string.link( display );
                            options.post_title = display.title;
                        }


                        let item = '<div class="gallery_item">';
                        item += '<img src="'+attachment.attributes['url']+'">';
                        item += '<a href="#" class="remove_image">';
                        item += '<i class="icon-close bookproicon-close"></i>'
                        item += '</a>';
                        item += '<input type="hidden" class="gallery_id" name="main_images" value="'+attachment.attributes['id']+'">';
                        item += '</div>';

                        $(".main_images .gallery_list").append( item );
                    });
                });

                //reset selection in popup, when open the popup
                frame.on('open',function() {
                    var selection = frame.state('insert-image').get('selection');

                    //remove all the selection first
                    selection.each(function(image) {
                        var attachment = wp.media.attachment( image.attributes.id );
                        attachment.fetch();
                        selection.remove( attachment ? [ attachment ] : [] );
                    });

                    //add back current selection, in here let us assume you attach all the [id] to <div id="my_file_group_field">...<input type="hidden" id="file_1" .../>...<input type="hidden" id="file_2" .../>
                    $(".main_images .gallery_list").find('input[type="hidden"]').each(function(){
                        var input_id = $(this);
                        if( input_id.val() ){
                            var attachment = wp.media.attachment( input_id.val() );
                            attachment.fetch();
                            selection.add( attachment ? [ attachment ] : [] );
                        }
                    });
                });

                //now open the popup
                frame.open();
            });
        },
        add_work_images: function(){

            $('.our_works_images .opb_button_add_media').on('click', function(e) {
                e.preventDefault();
                //create a new Library, base on defaults
                //you can put your attributes in
                var insertImage = wp.media.controller.Library.extend({
                    defaults :  _.defaults({
                            id: 'insert-image',
                            title: obp_business_obj.media_title,
                            allowLocalEdits: true,
                            displaySettings: true,
                            displayUserSettings: true,
                            multiple : true,
                            type : 'image'//audio, video, application/pdf, ... etc
                      }, wp.media.controller.Library.prototype.defaults )
                });

                //Setup media frame
                var frame = wp.media({
                    button : { text : obp_business_obj.media_button },
                    state : 'insert-image',
                    states : [
                        new insertImage()
                    ]
                });

                //on close, if there is no select files, remove all the files already selected in your main frame
                frame.on('close',function() {
                    var selection = frame.state('insert-image').get('selection');
                    if(!selection.length){
                        // #remove file nodes
                        $(".our_works_images .gallery_list").html('');
                        // #...
                    }
                });

                frame.on('select', function() {
                    var state = frame.state('insert-image');
                    var selection = state.get('selection');
                    var imageArray = [];

                    if ( ! selection ) return;

                    // #remove file nodes
                    $(".our_works_images .gallery_list").html('');

                    selection.each(function(attachment) {
                        var display = state.display( attachment ).toJSON();
                        var obj_attachment = attachment.toJSON()
                        var caption = obj_attachment.caption, options, html;
                        
                        // If captions are disabled, clear the caption.
                        if ( ! wp.media.view.settings.captions )
                            delete obj_attachment.caption;

                        display = wp.media.string.props( display, obj_attachment );

                        options = {
                            id: obj_attachment.id,
                            post_content: obj_attachment.description,
                            post_excerpt: caption
                        };

                        if ( display.linkUrl )
                            options.url = display.linkUrl;

                        if ( 'image' === obj_attachment.type ) {
                            html = wp.media.string.image( display );
                            _.each({
                            align: 'align',
                            size:  'image-size',
                            alt:   'image_alt'
                            }, function( option, prop ) {
                            if ( display[ prop ] )
                                options[ option ] = display[ prop ];
                            });
                        } else if ( 'video' === obj_attachment.type ) {
                            html = wp.media.string.video( display, obj_attachment );
                        } else if ( 'audio' === obj_attachment.type ) {
                            html = wp.media.string.audio( display, obj_attachment );
                        } else {
                            html = wp.media.string.link( display );
                            options.post_title = display.title;
                        }


                        let item = '<div class="gallery_item">';
                        item += '<img src="'+attachment.attributes['url']+'">';
                        item += '<a href="#" class="remove_image">';
                        item += '<i class="icon-close bookproicon-close"></i>'
                        item += '</a>';
                        item += '<input type="hidden" class="gallery_id" name="works_images" value="'+attachment.attributes['id']+'">';
                        item += '</div>';

                        $(".our_works_images .gallery_list").append( item );
                    });
                });

                //reset selection in popup, when open the popup
                frame.on('open',function() {
                    var selection = frame.state('insert-image').get('selection');

                    //remove all the selection first
                    selection.each(function(image) {
                        var attachment = wp.media.attachment( image.attributes.id );
                        attachment.fetch();
                        selection.remove( attachment ? [ attachment ] : [] );
                    });

                    //add back current selection, in here let us assume you attach all the [id] to <div id="my_file_group_field">...<input type="hidden" id="file_1" .../>...<input type="hidden" id="file_2" .../>
                    $(".our_works_images .gallery_list").find('input[type="hidden"]').each(function(){
                        var input_id = $(this);
                        if( input_id.val() ){
                            attachment = wp.media.attachment( input_id.val() );
                            attachment.fetch();
                            selection.add( attachment ? [ attachment ] : [] );
                        }
                    });
                });

                //now open the popup
                frame.open();
            });
        },
        remove_media: function() {
            $(document).on('click', '.business_images .remove_image', function(e) {
                e.preventDefault();
                $(this).closest('.gallery_item').find('input').val('');
                $(this).parent().remove();
            });
        },

        save_business: function() {

            const that = this;

            $(document).find('.obp-content-business .obp-form-submit .obp_button').on('click', function(e) {
                e.preventDefault();

                // Remove messages
                $(".obp_message_wrapper").html('');

                var obp_edit_business_nonce = $('.obp-content-business #obp_edit_business_nonce').val();

                var post_id         = $('.obp-content-business #post_id').val();

                var business_avatar = $('.obp-content-business input[name="business_avatar"]').val();
                var business_name   = $('.obp-content-business input[name="business_name"]').val();
                var phone           = $('.obp-content-business input[name="business_phone"]').val();
                var email           = $('.obp-content-business input[name="business_email"]').val();
                var tags            = $('.obp-content-business input[name="business_tags"]').val();
                var categories      = $('.obp-content-business #business_categories').val();
                var amenities       = $('.obp-content-business #business_amenities').val();
                
                var description = tinyMCE.get('business_description') ? tinyMCE.get('business_description').getContent() : $('#business_description').val();

                const enable_map = $('.obp-content-business input[name="enable_map"]:checked').val();

                const latitude      = $('.obp-content-business input[name="map_latitude"]').val();
                const longitude     = $('.obp-content-business input[name="map_longitude"]').val();
                const country_code  = $('.obp-content-business input[name="country_code"]').val();
                const state         = $('.obp-content-business input[name="state"]').val();
                const postcode      = $('.obp-content-business input[name="postcode"]').val();
                const city          = $('.obp-content-business input[name="city"]').val();
                const full_address  = $('.obp-content-business input[name="full_address"]').val();
                const current_language = $('.obp-content-business input[name="current_language"]').val();

                var socials = [];
                $('.obp-content-business').find('.business_socials .social_item').each(function() {
                    var social_arr = [];

                    var social_arr = {
                        'name_social': $(this).find('.name_social').val(),
                        'link_social': $(this).find('.link_social').val()
                    };
                    socials.push(social_arr);
                });

                /* work hours */
                var work_hours = [];

                $('.obp-content-business').find('.work_hours_field').each(function() {
                    var work_hour_arr = {
                        'label': $(this).find('input[name="work_hour_label"]').val(),
                        'start_hour': $(this).find('input[name="start_hour"]').val(),
                        'end_hour': $(this).find('input[name="end_hour"]').val()
                    };
                    work_hours.push(work_hour_arr);
                });

                // check is valid work hours
                var is_valid_work_hours = true;
                var prevTime = null;

                $('.obp-content-business').find('.work_hours_field .work_hour').each(function() {
                    var currentTime = $(this).timepicker('getTime');
                    if (currentTime) {
                        if (prevTime && currentTime < prevTime) {
                            is_valid_work_hours = false;
                            return false; // Breaks the each loop
                        }
                        prevTime = currentTime;
                    }
                });
                
                // business hours
                var monday, tuesday, wednesday, thursday, friday, saturday, sunday;
                monday = [];
                tuesday = [];
                wednesday = [];
                thursday = [];
                friday = [];
                saturday = [];
                sunday = [];
                var checkEmptyBusinessHours = true;

                $('.obp-content-business').find('.business_hours_monday .business-hour').each(function() {
                    const start_hour = $(this).find('input[name="start_hour"]').val();
                    const end_hour = $(this).find('input[name="end_hour"]').val();
                    const monday_arr = {
                        'start_hour': start_hour,
                        'end_hour': end_hour
                    };

                    if ( start_hour && end_hour ) {
                        monday.push(monday_arr);
                    }
                    
                });

                $('.obp-content-business').find('.business_hours_tuesday .business-hour').each(function() {
                    const start_hour = $(this).find('input[name="start_hour"]').val();
                    const end_hour = $(this).find('input[name="end_hour"]').val();
                    const tuesday_arr = {
                        'start_hour': start_hour,
                        'end_hour': end_hour
                    };
                    if ( start_hour && end_hour ) {
                        tuesday.push(tuesday_arr);
                    }
                    
                });

                $('.obp-content-business').find('.business_hours_wednesday .business-hour').each(function() {
                    const start_hour = $(this).find('input[name="start_hour"]').val();
                    const end_hour = $(this).find('input[name="end_hour"]').val();
                    const wednesday_arr = {
                        'start_hour': start_hour,
                        'end_hour': end_hour
                    };
                    
                    if ( start_hour && end_hour ) {
                        wednesday.push(wednesday_arr);
                    }

                });

                $('.obp-content-business').find('.business_hours_thursday .business-hour').each(function() {
                    const start_hour = $(this).find('input[name="start_hour"]').val();
                    const end_hour = $(this).find('input[name="end_hour"]').val();
                    const thursday_arr = {
                        'start_hour': start_hour,
                        'end_hour': end_hour
                    };
                    
                    if ( start_hour && end_hour ) {
                        thursday.push(thursday_arr);
                    }
                });

                $('.obp-content-business').find('.business_hours_friday .business-hour').each(function() {
                    const start_hour = $(this).find('input[name="start_hour"]').val();
                    const end_hour = $(this).find('input[name="end_hour"]').val();
                    const friday_arr = {
                        'start_hour': start_hour,
                        'end_hour': end_hour
                    };
                    
                    if ( start_hour && end_hour ) {
                        friday.push(friday_arr);
                    }
                });

                $('.obp-content-business').find('.business_hours_saturday .business-hour').each(function() {
                    const start_hour = $(this).find('input[name="start_hour"]').val();
                    const end_hour = $(this).find('input[name="end_hour"]').val();
                    const saturday_arr = {
                        'start_hour': start_hour,
                        'end_hour': end_hour
                    };

                    if ( start_hour && end_hour ) {
                        saturday.push(saturday_arr);
                    }
                });

                $('.obp-content-business').find('.business_hours_sunday .business-hour').each(function() {
                    const start_hour = $(this).find('input[name="start_hour"]').val();
                    const end_hour = $(this).find('input[name="end_hour"]').val();
                    var sunday_arr = {
                        'start_hour': start_hour,
                        'end_hour': end_hour
                    };
                    
                    if ( start_hour && end_hour ) {
                        sunday.push(sunday_arr);
                    }                });

                var business_hours = {
                    monday,
                    tuesday,
                    wednesday,
                    thursday,
                    friday,
                    saturday,
                    sunday,
                };
                // end business hours
                var checkBusinessHours = true;

                if ( $.isEmptyObject( monday ) && $.isEmptyObject( tuesday ) &&
                    $.isEmptyObject( wednesday ) && $.isEmptyObject( thursday ) &&
                    $.isEmptyObject( thursday ) && $.isEmptyObject( friday ) &&
                    $.isEmptyObject( saturday ) && $.isEmptyObject( sunday ) ) {
                    checkBusinessHours = false;
                }

                var main_images = [];
                $(this).closest('.obp-content-business').find('.main_images .gallery_item').each(function() {
                    var gallery_id = $(this).find('.gallery_id').val();
                    main_images.push(gallery_id);
                });

                var our_works_images = [];
                $(this).closest('.obp-content-business').find('.our_works_images .gallery_item').each(function() {
                    var gallery_id = $(this).find('.gallery_id').val();
                    our_works_images.push(gallery_id);
                });

                var video_url = $('.obp-content-business input[name="video_url"]').val();

                var phoneRegex  = XRegExp(/^\+?\s?\(?(\d{1,4})?\)?\s?\-?\.?(\d{1,5})?\s?\-?\.?(\d{1,4})$/);
                var emailRegex  = XRegExp(/^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i);

                var messages = '';
                const messageErrorHTML = '<div class="obp_alert_danger" role="alert">[message]</div>';

                if ( checkBusinessHours == false ) {
                    messages += messageErrorHTML.replace( "[message]", obp_business_obj.business_hours_req );
                }

                if ( business_name == '' ) {
                    messages += messageErrorHTML.replace( "[message]", obp_business_obj.business_req );
                }

                if ( phone == '' ) {
                    messages += messageErrorHTML.replace( "[message]", obp_business_obj.phone_req );
                } else if( ! XRegExp.exec( phone, phoneRegex ) ) {
                    messages += messageErrorHTML.replace( "[message]", obp_business_obj.phone_invalid );
                }

                if ( email == '' ) {
                    messages += messageErrorHTML.replace( "[message]", obp_business_obj.email_req );
                } else if( ! XRegExp.exec( email, emailRegex ) ) {
                    messages += messageErrorHTML.replace( "[message]", obp_business_obj.email_invalid );
                }

                if ( categories == '' ) {
                    messages += messageErrorHTML.replace( "[message]", obp_business_obj.category_req );
                }

                var empty_work_hours = false;
                var work_hours_field = $('.obp-content-business').find('.work_hours_field input:required');
                for( var i = 0; i < work_hours_field.length; i++ ) {
                    if( $(work_hours_field[i]).val() == '' ) {
                        empty_work_hours = true;
                    }
                }

                if( is_valid_work_hours == false ) {
                    messages += messageErrorHTML.replace( "[message]", obp_business_obj.work_hours_invalid );
                }

                if ( messages != '' ) {

                    $(".obp_message_wrapper").append( messages );

                    $('html, body').animate({
                        scrollTop: $(".obp_message_wrapper").offset().top - 100
                    }, 1000);
                    return false;
                }

                const data = {
                    'action': 'obp_save_edit_business',
                    'nonce': ajax_object.nonce,
                    'obp_edit_business_nonce': obp_edit_business_nonce,
                    'id': post_id,
                    'business_avatar': business_avatar,
                    'business_name': business_name,
                    'phone': phone,
                    'email': email,
                    'tags': tags,
                    'categories': categories,
                    'amenities': amenities,
                    'description': description,
                    'country_code': country_code,
                    'state': state,
                    'postcode': postcode,
                    'city': city,
                    'full_address': full_address,
                    'latitude': latitude,
                    'longitude': longitude,
                    'socials': socials,
                    'work_hours': work_hours,
                    'business_hours': business_hours,
                    'main_images': main_images,
                    'our_works_images': our_works_images,
                    'video_url': video_url,
                    'enable_map': enable_map,
                    'current_language': current_language,
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
                    success: function(res){

                        window.location.reload( true );
                    }
                });

            });
        },
        
	};

    OBP_Frontend_Business.init();
    
})(jQuery);