(function ($) {
    window.OBP_Frontend_Order = {
        init: function() {

            this.filter();

            this.pagination();

            this.dateInputInit();

            this.download_order();

            this.cancel_order();

            this.sort_by_id();

            this.sort_by_name();

            this.order_popup();
            
        },
        show_loader( flag = true, container = '.obp-content-my-orders' ){
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
        order_popup: function(){
            const that = this;
            $('.obp_order_detail_popup').off().on('click', function(e){
                e.preventDefault();

                const order_id = $(this).attr('data-order-id');

                const data = {
                    'action': 'obp_order_detail_popup',
                    'nonce': ajax_object.nonce,
                    'order_id': order_id
                };

                that.show_loader( true, '.obp-order-table' );

                $.ajax({
                    url: ajax_object.ajax_url,
                    complete: function( jqXHR, textStatus){
                        that.show_loader( false, '.obp-order-table' );
                    },
                    data: data,
                    method: 'POST',
                    type: 'POST',
                    error: function( jqXHR, textStatus, errorThrown){
                        console.error( errorThrown );
                    },
                    success: function(results){
                        window.Export_Popup = new $.Zebra_Dialog(results, {
                            type: false,
                            custom_class: "obp_order_detail_popup",
                            buttons: false,
                            width: 800,
                        });
                    }
                });

            });
        },
        cancel_order: function(){
            const that = this;
            const container = $(".obp-content-my-orders");

            container.find(".order_cancel").off().on("click",function(e){
                e.preventDefault();

                const action_wrap = $(this).closest(".order_action_wrapper");
                const order_id = action_wrap.find('input[name="order_id"]').val();

                new $.Zebra_Dialog(
                    obp_order_obj.confirm_cancel,
                    {
                        type: "question",
                        title: null,
                        buttons: [
                            {caption: obp_order_obj.yes, callback: function() {

                                const data = {
                                    'action': 'obp_my_booking_cancel_order',
                                    'nonce': ajax_object.nonce,
                                    'order_id': order_id,
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
                                        window.location.reload( true );
                                    }
                                });

                            }},
                            {caption: obp_order_obj.no},
                        ]
                    }
                );
            });
        },
        sort_by_id: function(){
            const that = this;
            $(".obp_order_orderby_ID").off().on("click",function(){
                const $this = $(this);
                const container = $(".obp-content-my-orders");
                const order = $this.find('input[name="orderby"]').val();
                const order_status = $('#post_order_status').val();
                const from_date = $('input[name="from_date"]').val();
                const to_date = $('input[name="to_date"]').val();
                const page = $(".order-pagination .page_item.current_page").attr("data-page");
                const date_filter = container.find('select[name="date_filter"]').val();
                const data = {
                    'action': 'obp_my_booking_load_data',
                    'nonce': ajax_object.nonce,
                    'orderby': 'ID',
                    'order': order,
                    'from_date': from_date,
                    'to_date': to_date,
                    'page': page,
                    'date_filter': date_filter
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

                        if ( order == 'DESC' ) {
                            $this.find('input[name="orderby"]').val('ASC');
                            $this.find('.icon').html('<i class="flaticon bookproicon-up-arrow"></i>');
                        } else {
                            $this.find('input[name="orderby"]').val('DESC');
                            
                            $this.find('.icon').html('<i class="flaticon bookproicon-down-arrow"></i>');
                        }
                        
                        container.find(".order-table-body").html("");
                        container.find(".obp-pagination-wrap").html("");

                        container.find(".order-table-body").html(data?.table_html);
                        container.find(".obp-pagination-wrap").html(data?.pagination_html);
                        that.init_events();
                    }
                });

            });
        },
        sort_by_name: function(){
            const that = this;
            $(".obp_order_orderby_name").off().on("click",function(){
                const $this = $(this);
                const container = $(".obp-content-my-orders");
                const order = $this.find('input[name="orderby"]').val();
                const order_status = $('#post_order_status').val();
                const from_date = $('input[name="from_date"]').val();
                const to_date = $('input[name="to_date"]').val();
                const page = $(".order-pagination .page_item.current_page").attr("data-page");
                const date_filter = container.find('select[name="date_filter"]').val();
                const data = {
                    'action': 'obp_my_booking_load_data',
                    'nonce': ajax_object.nonce,
                    'orderby': 'ID',
                    'order': order,
                    'from_date': from_date,
                    'to_date': to_date,
                    'page': page,
                    'date_filter': date_filter,
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

                        if ( order == 'DESC' ) {
                            $this.find('input[name="orderby"]').val('ASC');
                            $this.find('.icon').html('<i class="flaticon bookproicon-up-arrow"></i>');
                        } else {
                            $this.find('input[name="orderby"]').val('DESC');
                            $this.find('.icon').html('<i class="flaticon bookproicon-down-arrow"></i>');
                        }
                        
                        container.find(".order-table-body").html("");
                        container.find(".obp-pagination-wrap").html("");

                        container.find(".order-table-body").html(data?.table_html);
                        container.find(".obp-pagination-wrap").html(data?.pagination_html);
                        that.init_events();
                    }
                });
            });
        },
        pagination: function(){
            const that = this;
            const container = $(".obp-content-my-orders");
            container.find(".order-pagination .page_item").off().on("click",function(e){
                e.preventDefault();
                const page = $(this).attr("data-page");
                const from_date = container.find('input[name="from_date"]').val();
                const to_date = container.find('input[name="to_date"]').val();
                const order_status = container.find('select[name="post_order_status"]').val();
                const date_filter = container.find('select[name="date_filter"]').val();
                const data = {
                    'action': 'obp_my_booking_load_data',
                    'nonce': ajax_object.nonce,
                    'from_date': from_date,
                    'to_date': to_date,
                    'order_status': order_status,
                    'page': page,
                    'date_filter': date_filter,
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
                        container.find(".order-table-body").html("");
                        container.find(".obp-pagination-wrap").html("");

                        container.find(".order-table-body").html(data?.table_html);
                        container.find(".obp-pagination-wrap").html(data?.pagination_html);
                        that.init_events();
                    }
                });

            });
        },
        dateInputInit: function(){
            $('input[name="from_date"]').flatpickr({
                'locale': obp_flatpickr_obj?.lang,
                "plugins": [new rangePlugin({ input: '#to_date'})],
                'disableMobile': true,
            });
        },
        filter: function() {
            const that = this;
            
            $(document).find('.obp-content-orders .search-order').on('click', function(e) {
                e.preventDefault();
                const container = $(".obp-content-my-orders");
                const from_date = container.find('input[name="from_date"]').val();
                const to_date = container.find('input[name="to_date"]').val();
                const order_status = container.find('select[name="post_order_status"]').val();
                const date_filter = container.find('select[name="date_filter"]').val();
                const data = {
                    'action': 'obp_my_booking_load_data',
                    'nonce': ajax_object.nonce,
                    'from_date': from_date,
                    'to_date': to_date,
                    'order_status': order_status,
                    'date_filter': date_filter,
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
                        container.find(".order-table-body").html("");
                        container.find(".obp-pagination-wrap").html("");

                        container.find(".order-table-body").html(data?.table_html);
                        container.find(".obp-pagination-wrap").html(data?.pagination_html);
                        that.init_events();
                    }
                });


            });
        },
        init_events: function(){
            this.pagination();
            this.download_order();
            this.cancel_order();
            this.order_popup();
            OBP_Change_Order.init();
            OBP_Frontend.tooltip_init();
        },
        download_order: function(){
            const that = this;
            const container = $(".obp-content-my-orders");
            container.find('.order_download').off().on("click",function(e){
                e.preventDefault();
                const action_wrap = $(this).closest(".order_action_wrapper");
                const order_id = action_wrap.find('input[name="order_id"]').val();

                const data = {
                    'action': 'obp_my_booking_download',
                    'nonce': ajax_object.nonce,
                    'order_id': order_id,
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

                        var $a = $('<a />', {
                          'href': data?.file_url,
                          'download': data?.file_name,
                          'text': "click"
                        }).hide().appendTo("body")[0].click();
                    }
                });

            });
        },
    };


    OBP_Frontend_Order.init();
})(jQuery);