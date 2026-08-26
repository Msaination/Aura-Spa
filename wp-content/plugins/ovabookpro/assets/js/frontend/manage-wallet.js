(function ($) {

	class OBP_Manage_Wallet {


		constructor(){
			this.init();
		}

		init(){
			this.set_payout_method();
			this.widthdraw_popup();
			this.pagination();
			this.show_payout_popup();
		}

		show_payout_popup(){
			var that = this;

			$('.obp_show_payout').off().on('click', function(e){
				e.preventDefault();
				const id = $(this).attr('data-id');
				const data = {
					'action': 'obp_show_payout_popup',
					'nonce': ajax_object.nonce,
					'id': id
				};

				$('.obp-content').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.ajax({
                    url: ajax_object.ajax_url,
                    complete: function( jqXHR, textStatus){
                        $('.obp-content').unblock();
                    },
                    data: data,
                    method: 'POST',
                    type: 'POST',
                    error: function( jqXHR, textStatus, errorThrown){
                        console.error( errorThrown );
                    },
                    success: function(data){
                    	window.Payout_Popup = new $.Zebra_Dialog(data, {
                            type: false,
                            custom_class: "obp_payout_popup",
                            buttons: false,
                            width: 800,
                        });
                    }
                });

			});
		}

		reload_payout_method(){
			var that = this;

			var nonce = $(".obp_set_payout_method").attr("data-nonce");
			var data = {
				'action': 'obp_reload_payout_method',
				'nonce': nonce,
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

				$(".payout_method").html("");

				$(".payout_method").html(res);

				that.set_payout_method();

				$('.obp-content').unblock();
			} );
		}

		set_payout_method(){
			var that = this;

			$(".obp_set_payout_method").off().on("click",function(e){
				e.preventDefault();
				var $this = $(this);
				var nonce = $this.attr("data-nonce");
				var data = {
					'action': 'obp_set_payout_method',
					'nonce': nonce,
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
						window.OBP_Payout_Method_Dialog = new $.Zebra_Dialog(res,
						    {
						    	type: false,
						    	buttons: false,
						    	width: 650,
						    	max_height: 1000,
						    	onClose: function(caption){
						    		that.reload_payout_method();
						    	}
						    }
						);
						that.update_payout_method();
						that.payout_method_change();
					}

					$('.obp-content').unblock();
				} );
			});
		}

		update_payout_method(){

			$(".payout_method_setup_form").off().on("submit",function(e){
				e.preventDefault();
				var $this = $(this);
				var nonce = $this.attr("data-nonce");
				var payoutMethodID = $this.find('input[name="payout_method"]:checked').val();
				var payoutInfo = {};
				var errorMess = $this.attr("data-error");
				var inputSettings = $(".payout_method_field_settings .input_setting");

				var hasError = false;

				inputSettings.each( function(i,el){
					var isReqired 	= $(el).attr("data-required");
					var inputName 	= $(el).attr("name");
					var inputVal 	= $(el).val();

					if ( isReqired && inputVal == '' ) {
						hasError = true;
						return false;
					}

					payoutInfo[inputName] = inputVal;

				} );


				$this.find(".messages").html("");

				if ( hasError == true ) {
					$this.find(".messages").append( '<div class="error">'+errorMess+'</div>' );
					return false;
				}

				var data = {
					'action': 'obp_update_payout_method',
					'nonce': nonce,
					'payout_method_id': payoutMethodID,
					'payout_info': payoutInfo,
				};

				$('.payout_method_setup_form').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});


				$.post( ajax_object.ajax_url, data, function(res){

					res = JSON.parse(res);
					$this.find(".messages").append( '<div class="'+res.status+'">'+res.mess+'</div>' );

					$('.payout_method_setup_form').unblock();

				} );
				
			});
		}

		payout_method_change(){

			$('input[name="payout_method"]').off().on("change",function(){

				var payoutMethodID = $(this).val();
				var nonce = $(".payout_method_setup_form").attr("data-nonce");

				var data = {
					'action': 'obp_payout_method_change',
					'nonce': nonce,
					'payout_method_id': payoutMethodID,
				};

				$('.payout_method_setup_form').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				$.post( ajax_object.ajax_url, data, function(res){

					$(".payout_method_field_settings").html("");
					$(".payout_method_field_settings").html(res);

					$('.payout_method_setup_form').unblock();

				} );

			});
		}

		reload_manage_wallet(){
			var that = this;
			var nonce = $(".obp-content-manage-wallet").attr("data-nonce");
			var data = {
				'action': 'obp_reload_manage_wallet',
				'nonce': nonce,
			};

			$('.obp-content').block({
				message: null,
				overlayCSS:  { 
			        backgroundColor: '#fff', 
			        opacity: 0.3, 
			        cursor: null 
			    },
			});

			$.post(ajax_object.ajax_url, data, function(res){
				$(".obp-content-manage-wallet").html(res);
				that.init();
			});
		}

		pagination(){
			const that = this;
            const container = $(".transaction_history");
            container.find(".transaction-pagination .page_item").off().on("click",function(e){
                e.preventDefault();
                const page = $(this).attr("data-page");

                const data = {
                    'action': 'obp_payout_load_data',
                    'nonce': ajax_object.nonce,
                    'page': page,
                };

                $('.transaction_history_content').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

                $.ajax({
                    url: ajax_object.ajax_url,
                    complete: function( jqXHR, textStatus){
                        $('.transaction_history_content').unblock();
                    },
                    data: data,
                    method: 'POST',
                    type: 'POST',
                    error: function( jqXHR, textStatus, errorThrown){
                        console.error( errorThrown );
                    },
                    success: function(data){
                    	
                    	$(".transaction-table-body").html('');
                    	$(".obp-pagination-wrap").html('');
                    	$(".transaction-table-body").html(data?.table_html);
                    	$(".obp-pagination-wrap").html(data?.pagination_html);
                    	that.pagination();
                    	that.show_payout_popup();
                    }
                });

            });
		}

		widthdraw_popup(){
			var that = this;
			$(".obp_withdraw_popup").off().on("click",function(e){
				e.preventDefault();
				var $this = $(this);
				var nonce = $this.attr("data-nonce");

				var data = {
					'action': 'obp_withdraw_popup',
					'nonce': nonce,
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
					window.OBP_Widthdraw_Dialog = new $.Zebra_Dialog(res,
					    {
					    	type: false,
					    	buttons: false,
					    	width: 450,
					    	height: 300,
					    	onClose: function(caption){
					    		that.reload_manage_wallet();
					    	}
					    }
					);

					that.widthdraw_request_submit();

					$('.obp-content').unblock();
				});
			});
		}

		widthdraw_request_submit(){
			var that = this;
			$(".obp_withdraw_form").off().on("submit",function(e){
				e.preventDefault();
				var $this = $(this);
				var nonce = $this.attr("data-nonce");
				var amount = parseFloat( $this.find("#obp_withdraw_amount").val() );
				var dataError = JSON.parse( $this.attr("data-error") );
				var mess = [];

				if ( isNaN( amount ) ) {
					mess.push( dataError.invalid );
				}

				$(".obp_withdraw_popup_wrapper .message").html("");

				if ( mess.length > 0 ) {
					for (var i = 0; i < mess.length; i++) {
						$(".obp_withdraw_popup_wrapper .message").append('<p class="error">'+mess[i]+'</p>');
					}
					return false;
				}

				$('.obp_withdraw_popup_wrapper').block({
					message: null,
					overlayCSS:  { 
				        backgroundColor: '#fff', 
				        opacity: 0.3, 
				        cursor: null 
				    },
				});

				var data = {
					'action': 'obp_withdraw_request',
					'nonce': nonce,
					'amount': amount,
				};

				$.post( ajax_object.ajax_url ,data, function(res){
					$(".obp_withdraw_popup_wrapper").html( res );
					that.widthdraw_request_submit();
				});
			});
		}

	}

	window.OBP_Manage_Wallet = new OBP_Manage_Wallet();
})(jQuery);