(function ($) {
	
	class OBP_My_Profile {

		constructor(){

			this.init();
		}

		init(){
			this.profile_avatar();
			this.update_my_profile();
			this.update_password_profile();
			this.delete_account_profile();
			this.accordion();
		}

		accordion(){
        	$(".obp-accordion-enable").accordion({
				header: ".accordion-title",
				collapsible: true,
				icons: false,
				heightStyle: "content"
			});

			$(".obp-accordion-enable .ui-accordion-content").show();
        }

		profile_avatar(){
			var that = this;
			var profile_avatar;

            $('.profile_avatar .opb_button_add_media').off().on('click', function(e) {
                e.preventDefault();

                if (typeof profile_avatar != 'undefined') {
                    profile_avatar.close();
                }

                var that = $(this);

                profile_avatar = wp.media({
                    title: $(this).data('uploader-title'),
                    button: {
                        text: $(this).data('button-text'),
                    },
                    library: {
                        type: ['image']
                    },
                    multiple: false
                });

                profile_avatar.on('select', function() {
                    var selection = profile_avatar.state().get('selection');

                    selection.map(function(attachment, i) {
                        attachment = attachment.toJSON();
                        that.closest('.profile_avatar').find('.profile-image').html('<img src="' + attachment.sizes.full.url + '"><a href="#" class="remove_image"><i class="icon-close bookproicon-close"></i></a>');
                        that.closest('.profile_avatar').find('input').val(attachment.id);
                    });
                });

                profile_avatar.open();
            });

            /* Remove */
            $(document).on('click', '.profile_avatar .remove_image', function(e) {
                e.preventDefault();
                $(this).closest('.profile_avatar').find('input').val('');
                $(this).parent().empty();
            });

		}

		update_my_profile(){
			// REGEX
			var that = this;
			var phoneRegex = XRegExp(/^\+?\s?\(?(\d{1,4})?\)?\s?\-?\.?(\d{1,5})?\s?\-?\.?(\d{1,4})$/);
			var unicodeWord = XRegExp('^[\\p{Latin}\\p{Letter}\\p{Mark}\\p{Hiragana}\\p{Common}]+$');

			$('.obp_update_profile_form').off().on('submit',function(e){
				e.preventDefault();

				var dataError = JSON.parse( $('.update_profile_errors').attr('data-error') );
				var mess = [];
				var messHTML = '';
				var $this = $(this);
				var nonce = $this.find('#update_profile_nonce').val();
				var avatarID = $this.find('#avatar').val();
				var phoneNumber = $this.find('#phone_number').val();
				var firstName = $this.find('#first_name').val();
				var lastName = $this.find('#last_name').val();
				var nickName = $this.find('#nickname').val();
				var description = $this.find('#description').val();

				// Validation
				$('.obp_update_profile_notice').html('');
				// Phone
				
				if ( firstName == '' ) {
					mess.push( dataError.empty_first_name );
				}

				// First name
				if ( firstName != '' && ! unicodeWord.test( firstName ) ) {
					mess.push( dataError.invalid_first_name );
				}
					
				// Last name

				if ( lastName == '' ) {
					mess.push( dataError.empty_last_name );
				}

				if ( lastName != '' && ! unicodeWord.test( lastName ) ) {
					mess.push( dataError.invalid_last_name );
				}

				// Nickname
				if ( nickName == '' ) {
					mess.push( dataError.empty_nickname );
				}
				if ( nickName != '' && ! unicodeWord.test( nickName )  ) {
					mess.push( dataError.invalid_nickname );
				}
	
				if ( phoneNumber == '' ) {
					mess.push( dataError.empty_phone_numner );
				}
				if ( phoneNumber != '' && ! XRegExp.exec( phoneNumber, phoneRegex ) ) {
					mess.push( dataError.invalid_phone_number );
				}

				// Display Error
				if ( mess.length > 0 ) {
					for (var i = 0; i < mess.length; i++) {
						messHTML += '<div class="obp_alert_danger">'+ mess[i] + '</div>';
					}

					$('.obp_update_profile_notice').html( messHTML );

					var offsetTop = $this.offset().top - 50;

					$('html, body').animate({
				        scrollTop: offsetTop
				    }, 1000);

					return false;
				}

				var data = {
					'action': 'obp_update_my_profile',
					'nonce': nonce,
					'avatar_id': avatarID,
					'phone_number': phoneNumber,
					'first_name': firstName,
					'last_name': lastName,
					'nickname': nickName,
					'description': description,
				};

				$this.block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url,data, function(res){

					$(".obp_update_profile_wrapper").html(res);
					that.init();
					var offsetTop = $(".obp_update_profile_wrapper").offset().top - 50;

					$('html, body').animate({
				        scrollTop: offsetTop
				    }, 1000);

				} );


			});

		}

		update_password_profile(){
			var that = this;
			$('.obp_change_password_form').off().on('submit',function(e){
				e.preventDefault();

				var dataError = JSON.parse( $('.update_password_errors').attr('data-error') );
				var mess = [];
				var messHTML = '';
				var $this = $(this);
				var nonce = $this.find('#update_password_nonce').val();
				var oldPassword = $this.find('#old_password').val();
				var newPassword = $this.find('#new_password').val();
				var confirmPassword = $this.find('#confirm_password').val();
				var redirectTo = $this.find('input[name="_wp_http_referer"]').val();

				// Validation
				$('.obp_update_password_notice').html('');
				if ( oldPassword == '' ) {
					mess.push( dataError.empty_old_pass );
				}

				if ( newPassword == '' ) {
					mess.push( dataError.empty_new_pass );
				}

				if ( confirmPassword == '' ) {
					mess.push( dataError.empty_confirm_pass );
				}

				if ( newPassword != '' && confirmPassword != '' && newPassword != confirmPassword ) {
					mess.push( dataError.not_match );
				}

				// Display Error
				if ( mess.length > 0 ) {
					for (var i = 0; i < mess.length; i++) {
						messHTML += '<div class="obp_alert_danger">'+ mess[i] + '</div>';
					}

					$('.obp_update_password_notice').html( messHTML );

					var offsetTop = $this.offset().top - 50;

					$('html, body').animate({
				        scrollTop: offsetTop
				    }, 1000);

					return false;
				}

				var data = {
					'action': 'obp_update_password_profile',
					'nonce': nonce,
					'old_password': oldPassword,
					'new_password': newPassword,
					'confirm_password': confirmPassword,
				};

				$this.block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url,data, function(res){

					$(".obp_change_password_wrapper").html(res);
					that.init();

					var offsetTop = $(".obp_change_password_wrapper").offset().top - 50;

					$('html, body').animate({
				        scrollTop: offsetTop
				    }, 1000);

				    var status = $(".obp_change_password_form").attr("data-status");

				    if ( status == 'success' ) {
				    	var mess = $(".obp_change_password_form").attr("data-login-again");

						$(".obp_change_password_form").block({
							message: mess,
							overlayCSS:  { 
						        opacity: 0.3, 
						        cursor: null 
						    },
						});

				    	setTimeout( function(){
				    		location.reload();
				    	}, 3000 );

				    }

				} );

			});
		}

		delete_account_profile(){
			var that = this;

			$('.obp_delete_account_form').off().on('submit',function(e){
				e.preventDefault();
				var $this = $(this);
				var nonce = $this.find('#delete_account_nonce').val();
				var reasonDelete = $this.find('#reason_delete_account').val();

				var data = {
					'action': 'obp_delete_account_profile',
					'nonce': nonce,
					'reason_delete': reasonDelete,
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
					$(".obp_delete_account_wrapper").html(res);
					that.init();

					var offsetTop = $(".obp_delete_account_wrapper").offset().top - 50;

					$('html, body').animate({
				        scrollTop: offsetTop
				    }, 1000);

				    var status = $(".obp_delete_account_form").attr("data-status");

				    if ( status == 'success' ) {
				    	var mess = $(".obp_delete_account_form").attr("data-redirect");

						$(".obp_delete_account_form").block({
							message: mess,
							overlayCSS:  { 
						        opacity: 0.3, 
						        cursor: null 
						    },
						});

				    	setTimeout( function(){
				    		location.reload();
				    	}, 3000 );

				    }
				} );

			});
		}

	}

	window.OBP_My_Profile = new OBP_My_Profile();

})(jQuery);