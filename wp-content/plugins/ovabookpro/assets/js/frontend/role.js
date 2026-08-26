(function ($) {
	
	class OBP_Role {

		constructor(){
			this.init();
		}

		init(){
			this.add_role();
			this.edit_role();
			this.update_role();
			this.remove_role();
			this.remove_form();
		}

		remove_role(){
			var that = this;
			$(".obp_remove_role").off().on("click",function(e){
				e.preventDefault();

				var $this = $(this);
				var roleID = $this.attr('data-id');
				var nonce = $this.attr('data-nonce');
				var dataMess = JSON.parse( $(".listing_roles").attr('data-mess') );
				var dataBtn = JSON.parse( $(".listing_roles").attr('data-btn') );

				new $.Zebra_Dialog(dataMess.confirm_remove,
				    {
				        type: "question",
				        buttons: [
				        	{
				        		caption: dataBtn.yes,
				        		callback: function(){
				        			var data = {
				        				'action': 'obp_remove_role',
				        				'nonce': nonce,
				        				'id': roleID,
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
				        				$(".obp-main-content").html(res);
										var offsetTop = $(".obp-main-content").offset().top - 50;

										$('html, body').animate({
									        scrollTop: offsetTop
									    }, 1000);

									    that.init();
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

		remove_form(){

			var that = this;

			$(".obp_remove_edit_form").off().on("click", function(e){
				e.preventDefault();
				$(this).closest(".edit-role-wrap").html('');
			} );
		}

		update_role(){
			var that = this;
			$(".obp_save_role_form").off().on("submit",function(e){
				e.preventDefault();
				var $this = $(this);
				var nonce = $this.find("#obp_save_role_nonce").val();
				var dataError = JSON.parse( $(".add_role_errors").attr('data-error') );
				var roleName = $this.find("#edit_role_name").val();
				var roleID = $this.find("#role_id").val();
				var capabilities = [];
				var mess = [];
				var messHTML = '';
				var unicodeWord = XRegExp('^[\\p{Latin}\\p{Letter}\\p{Mark}\\p{Hiragana}\\p{Common}]+$');

				$this.find(".capabilities:checked").each(function(i,el){
					capabilities.push( $(el).val() );
				});

				$('.obp_save_role_notice').html('');

				if ( roleName == '' ) {
					mess.push( dataError.empty_name );
				}

				if ( roleName != '' && ! unicodeWord.test( roleName ) ) {
					mess.push( dataError.invalid_name );
				}

				// Display Error
				if ( mess.length > 0 ) {
					for (var i = 0; i < mess.length; i++) {
						messHTML += '<div class="obp_alert_danger">'+ mess[i] + '</div>';
					}

					$('.obp_save_role_notice').html( messHTML );

					var offsetTop = $this.offset().top - 50;

					$('html, body').animate({
				        scrollTop: offsetTop
				    }, 1000);

					return false;
				}


				var data = {
					'action': 'obp_save_role',
					'nonce': nonce,
					'role_id': roleID,
					'role_name': roleName,
					'cap': capabilities,
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
					$(".obp-main-content").html(res);
					var offsetTop = $(".obp-main-content").offset().top - 50;

					$('html, body').animate({
				        scrollTop: offsetTop
				    }, 1000);

				    that.init();

				} );

			});
		}

		edit_role(){
			var that = this;
			$(".obp_edit_role").off().on("click",function(e){
				e.preventDefault();
				var $this = $(this);

				var roleID = $this.attr('data-id');
				var nonce = $this.attr('data-nonce');

				$(".edit-role-wrap").html('');

				var data = {
					'action': 'obp_edit_role',
					'nonce': nonce,
					'role_id': roleID,
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
					
					$('.edit-role-wrap[data-role-id="'+roleID+'"]').html(res);
					$('.obp-content').unblock();
					that.init();
				} );

			});
		}

		add_role(){
			var that = this;
			$(".obp_add_role_form").off().on("submit",function(e){
				e.preventDefault();
				
				var $this = $(this);
				var dataError = JSON.parse( $(".add_role_errors").attr('data-error') );
				var nonce = $this.find("#obp_add_role_nonce").val();
				var roleName = $this.find("#role_name").val();
				var capabilities = [];
				var mess = [];
				var messHTML = '';
				var unicodeWord = XRegExp('^[\\p{Latin}\\p{Letter}\\p{Mark}\\p{Hiragana}\\p{Common}]+$');


				$this.find(".capabilities:checked").each(function(i,el){
					capabilities.push( $(el).val() );
				});

				$('.obp_add_role_notice').html('');

				if ( roleName == '' ) {
					mess.push( dataError.empty_name );
				}

				if ( roleName != '' && ! unicodeWord.test( roleName ) ) {
					mess.push( dataError.invalid_name );
				}

				// Display Error
				if ( mess.length > 0 ) {
					for (var i = 0; i < mess.length; i++) {
						messHTML += '<div class="obp_alert_danger">'+ mess[i] + '</div>';
					}

					$('.obp_add_role_notice').html( messHTML );

					var offsetTop = $this.offset().top - 50;

					$('html, body').animate({
				        scrollTop: offsetTop
				    }, 1000);

					return false;
				}


				var data = {
					'action': 'obp_save_role',
					'nonce': nonce,
					'role_name': roleName,
					'cap': capabilities,
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
					$(".obp-main-content").html(res);
					var offsetTop = $(".obp-main-content").offset().top - 50;

					$('html, body').animate({
				        scrollTop: offsetTop
				    }, 1000);

				    that.init();

				} );

			});
		}
	}

	window.OBP_Role = new OBP_Role();

})(jQuery);