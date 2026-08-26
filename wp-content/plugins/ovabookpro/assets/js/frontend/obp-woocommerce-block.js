(function ($) {

	window.obp_woocommerce_block = {
		init: function(){
			const block_data = wcSettings.obp_woocommerce_block_data;
			const cart = block_data?.cart;
			const tax = block_data?.tax;
			const system_fee = block_data?.system_fee;
			const discount = block_data?.discount;

			const { registerCheckoutFilters } = window?.wc?.blocksCheckout?.registerCheckoutFilters ? window?.wc?.blocksCheckout : {};

			const modifyCartItemClass = ( defaultValue, extensions, args ) => {
				const context = args?.context;

				const cartItemKey = args?.cartItem.key;
				if ( context == 'summary' || context == 'cart' ) {
					return 'obp-cart-item-'+cartItemKey;
				}

				return defaultValue;
			};

			if ( ! $.isEmptyObject( registerCheckoutFilters ) ) {
				registerCheckoutFilters( 'obp-cart-extension', {
					cartItemClass: modifyCartItemClass,
				} );
			}

			const obp_replace_subtotal = setInterval(function () {
				if ( ! $.isEmptyObject( cart ) ) {
					for( const key in cart ){
						if ( $('.obp-cart-item-'+key).length ) {
							$('.obp-cart-item-'+key).find('.wc-block-components-order-summary-item__total-price').html('');
							$('.obp-cart-item-'+key).find('.wc-block-cart-item__total').html('');

							$('.obp-cart-item-'+key).find('.wc-block-components-order-summary-item__total-price').html(cart[key]);
							$('.obp-cart-item-'+key).find('.wc-block-cart-item__total').html(cart[key]);

							clearInterval(obp_replace_subtotal);
						}
					}
				} else {
					clearInterval(obp_replace_subtotal);
				}
			}, 500 );

			const obp_add_tax_system_fee = setInterval( function(){

					if ( block_data?.tax || block_data?.system_fee || block_data?.discount ) {

						if ( block_data?.discount ) {

							if ( $('.wp-block-woocommerce-checkout-order-summary-subtotal-block').length ) {
								let content = '<div class="wc-block-components-totals-item">';
								content += '<span class="wc-block-components-totals-item__label">'+block_data?.discount?.label+'</span>';
								content += '<span class="wc-block-formatted-money-amount wc-block-components-formatted-money-amount wc-block-components-totals-item__value">';
								content += block_data?.discount?.value+'</span>';
								content += '</div>';
								$('.wp-block-woocommerce-checkout-order-summary-subtotal-block').append( content );
								clearInterval(obp_add_tax_system_fee);
							}

							if ( $('.wp-block-woocommerce-cart-order-summary-subtotal-block').length ) {
								let content = '<div class="wc-block-components-totals-item">';
								content += '<span class="wc-block-components-totals-item__label">'+block_data?.discount?.label+'</span>';
								content += '<span class="wc-block-formatted-money-amount wc-block-components-formatted-money-amount wc-block-components-totals-item__value">'+block_data?.discount?.value+'</span>';
								content += '</div>';
								$('.wp-block-woocommerce-cart-order-summary-subtotal-block').append( content );
								clearInterval(obp_add_tax_system_fee);
							}
						}
						
						if ( block_data?.tax ) {

							if ( $('.wp-block-woocommerce-checkout-order-summary-subtotal-block').length ) {
								let content = '<div class="wc-block-components-totals-item">';
								content += '<span class="wc-block-components-totals-item__label">'+block_data?.tax?.label+'</span>';
								content += '<span class="wc-block-formatted-money-amount wc-block-components-formatted-money-amount wc-block-components-totals-item__value">';
								content += block_data?.tax?.value+'</span>';
								content += '</div>';
								$('.wp-block-woocommerce-checkout-order-summary-subtotal-block').append( content );
								clearInterval(obp_add_tax_system_fee);
							}

							if ( $('.wp-block-woocommerce-cart-order-summary-subtotal-block').length ) {
								let content = '<div class="wc-block-components-totals-item">';
								content += '<span class="wc-block-components-totals-item__label">'+block_data?.tax?.label+'</span>';
								content += '<span class="wc-block-formatted-money-amount wc-block-components-formatted-money-amount wc-block-components-totals-item__value">'+block_data?.tax?.value+'</span>';
								content += '</div>';
								$('.wp-block-woocommerce-cart-order-summary-subtotal-block').append( content );
								clearInterval(obp_add_tax_system_fee);
							}
						}

						if ( block_data?.system_fee ) {
							if ( $('.wp-block-woocommerce-checkout-order-summary-subtotal-block').length ) {
								let content = '<div class="wc-block-components-totals-item">';
								content += '<span class="wc-block-components-totals-item__label">'+block_data?.system_fee?.label+'</span>';
								content += '<span class="wc-block-formatted-money-amount wc-block-components-formatted-money-amount wc-block-components-totals-item__value">';
								content += block_data?.system_fee?.value+'</span>';
								content += '</div>';
								$('.wp-block-woocommerce-checkout-order-summary-subtotal-block').append( content );
								clearInterval(obp_add_tax_system_fee);
							}
							
							if ( $('.wp-block-woocommerce-cart-order-summary-subtotal-block').length ) {
								let content = '<div class="wc-block-components-totals-item">';
								content += '<span class="wc-block-components-totals-item__label">'+block_data?.system_fee?.label+'</span>';
								content += '<span class="wc-block-formatted-money-amount wc-block-components-formatted-money-amount wc-block-components-totals-item__value">';
								content += block_data?.system_fee?.value+'</span>';
								content += '</div>';
								$('.wp-block-woocommerce-cart-order-summary-subtotal-block').append( content );
								clearInterval(obp_add_tax_system_fee);
							}
						}

					} else {
						clearInterval(obp_add_tax_system_fee);
					}
					
				});
		},
	};

	obp_woocommerce_block.init();
})(jQuery);