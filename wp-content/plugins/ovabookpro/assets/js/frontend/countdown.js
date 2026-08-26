jQuery(document).ready(function($) {
	window.obp_countdown = {
		init: function(){
			if ( $("#obp_order_countdown").length ) {
				var isRTL = $("body").hasClass("rtl");
				var countdownTime = parseInt( $("#obp_order_countdown").attr("data-time") );
				$("#obp_order_countdown").countdown({
					until: countdownTime,
					compact: true,
					layout: '<span class="text">'+obp_countdown_obj?.title+':</span> <span class="time">{hnn}{sep}{mnn}{sep}{snn}</span> {desc}',
					alwaysExpire: true,
					isRTL: isRTL,
					onExpiry: function(){
						var nonce = $("#obp_order_countdown").attr("data-nonce");
						var data = {
							'action': 'obp_booking_countdown_timeout',
							'nonce': nonce,
						};

						$.post( ajax_object.ajax_url, data, function(res){
							location.reload();
						} );
					}
				});
			}
		},
	};

	obp_countdown.init();
	
});