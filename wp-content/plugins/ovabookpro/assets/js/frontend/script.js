(function ($) {

	$.fn.fileInputPreview = function() {
		return this.each(function() {
			var $fileInput = $(this);
			var $selectedFilesContainer = $(this).closest('.selected-files-container');
			var $selectedFiles = $selectedFilesContainer.find('.selected-files');
			var $fileClear = $fileInput.attr('data-clear');
			var $clearButton = '';
			var $fileMax = parseFloat( $fileInput.attr('data-max') );

			if ( isNaN( $fileMax ) ) {
				$fileMax = 10;
			}
			if ( $fileClear ) {
				$clearButton = $('<button type="button" class="clear-button">'+$fileClear+'</button>');
			}

			$selectedFilesContainer.append($clearButton);

			$fileInput.on('change', function(e) {
				var files = e.target.files;
				$selectedFiles.empty();

				if ( files.length > 0 ) {
					for (var i = 0; i < files.length; i++) {
						var file = files[i];
						var fileName = file.name;
						var fileSizeMb = file.size * Math.pow(10,-6);
						if ( fileSizeMb > $fileMax ) {
							return false;
						}
						var fileSize = getFileSize(file.size);

						var $fileItem = $('<div class="file-item">' + fileName + ' (' + fileSize + ')</div>');
						var $removeButton = $('<span class="remove-button">x</span>');

						$removeButton.data('fileIndex', i);
						$removeButton.on('click', function() {
							var index = $(this).data('fileIndex');
							var selectedFiles = Array.from($fileInput[0].files);

							selectedFiles.splice(index, 1);

							var dt = new DataTransfer();
							for (var i = 0; i < selectedFiles.length; i++) {
								dt.items.add(selectedFiles[i]);
							}

							$fileInput[0].files = dt.files;
							$(this).parent('.file-item').remove();
						});

						$fileItem.append($removeButton);
						$selectedFiles.append($fileItem);
					}

					$selectedFilesContainer.addClass('has-files');
				} else {
					$selectedFilesContainer.removeClass('has-files');
				}
			});
			if ( $clearButton ) {
				$clearButton.on('click', function() {
					$fileInput.val('');
					$selectedFiles.empty();
					$selectedFilesContainer.removeClass('has-files');
				});
			}

			$selectedFilesContainer.find('.remove-button').click( function(e){
				e.preventDefault();
				$selectedFilesContainer.find('input').val('');
				$selectedFilesContainer.find('.selected-files').empty();
				$selectedFilesContainer.removeClass('has-files');
			} );
			

			function getFileSize(bytes) {
				var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
				if (bytes == 0) return '0 Byte';
				var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
				return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
			}
		});
	};

	// Define a global jQuery function: showMoreDescription()
    $.fn.showMoreDescription = function() {
        return this.each(function() {
        	var that   = $(this);
	        var h_data = that.outerHeight();

	        var text_show_more = that.data('text_show_more');
	        var text_show_less = that.data('text_show_less');

	        if (that.data('height') != 'auto' && that.data('height') < h_data) {
	            that.css('height', that.data('height'));

	            that.find('.show_more_desc').css({
	                'display': 'block',
	            });

	            that.find('.show_more_desc .btn_showmore').on('click', function(e) {
	                e.preventDefault();

	                let parent = $(this).parents('.description-wrap');

	                if ($(this).parent().hasClass('show_less')) {
	                    // Collapse
	                    parent.animate({
	                        height: parent.data('height')
	                    }, 500);

	                    $(this).parent().removeClass('show_less');
	                    $(this).find('.text').text(text_show_more);
	                } else {
	                    // Expand
	                    parent.animate({
	                        height: parent.get(0).scrollHeight + 30
	                    }, 500);

	                    $(this).parent().addClass('show_less');
	                    $(this).find('.text').text(text_show_less);
	                }
	            });
	        }
        });
    };

	window.OBP_Frontend = {
		init: function() {
			this.tooltip_init();
            this.obp_select2();
            this.obp_nav_main_mobile_toggle();
            this.obp_show_hide_passsword();
            this.download_order();
		},

		get_first_day_of_week: function(){
			var $option = calendar_object?.first_day;
			var $result = 0;
			switch( $option ) {
				case 'monday':
					$result = 1;
					break;
				case 'tuesday':
					$result = 2;
					break;
				case 'wednesday':
					$result = 3;
					break;
				case 'thursday':
					$result = 4;
					break;
				case 'friday':
					$result = 5;
					break;
				case 'saturday':
					$result = 6;
					break;
				default:
			}
			return $result;
		},

		download_order: function(){
			var that = this;
			$(".obp_download_order").off().on('click', function(e){
				e.preventDefault();
				const $this = $(this);
				const order_id = $this.attr('data-order-id');
				const data = {
					'action': 'obp_my_booking_download',
					'nonce': ajax_object.nonce,
					'order_id': order_id
				};

				$this.block({
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
                        $this.unblock();
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

		obp_select2:function() {
            $('.obp-select2').select2();
		},

		tooltip_init: function(){
			tippy('[data-tippy-content]', {
				zIndex: 99999,
			});
		},

		obp_nav_main_mobile_toggle: function() {
            $('.obp-dashboard .nav-main-mobile-toggle').on('click', function(){
                $(this).closest('.obp-dashboard-nav').find('.obp-dashboard-nav-main').slideToggle();
            });

            // Function to adjust the menu based on window size
		    function adjustMenu() {
		        var windowWidth = $(window).width() + 17;
		        var breakpoint = 1024; 

		        if (windowWidth >= breakpoint) {
		            $('.obp-dashboard-nav-main').show();
		        } else {
		            $('.obp-dashboard-nav-main').hide();
		        }
		    }

		    // Initial adjustment when the page loads
		    adjustMenu();

		    // Adjust the menu on window resize
		    $(window).on('resize', function() {
		        adjustMenu();
		    });
        },

        obp_show_hide_passsword: function(){
        	$('.obp-password i').on('click',function(){
				var $this = $(this);
				var obp_password = $this.closest('.obp-password');
				var that_field = obp_password.find('input');
				var field_type = that_field.attr('type');
				switch( field_type ) {
					case 'text':
						that_field.attr('type','password');
						$this.attr('class','bookproicon-view');
					break;
					case 'password':
						that_field.attr('type','text');
						$this.attr('class','bookproicon-hide');
					break;
					default:
				}
			});
        },
        obp_get_date: function( year, month, day ){
        	var date_str = '';
        	var date_format = calendar_object?.date_format;
        	month = month + 1;
        	month = month < 10 ? '0'+month : month;
        	var day_zero = day < 10 ? '0'+day : day;
        	switch( date_format ) {
				case 'Y-m-d':
					date_str = year+'-'+month+'-'+day_zero;
					break;
				case 'Y/m/d':
					date_str = year+'/'+month+'/'+day_zero;
					break;
				case 'd-m-Y':
					date_str = day_zero+'-'+month+'-'+year;
					break;
				case 'm/d/Y':
					date_str = month+'/'+day_zero+'/'+year;
					break;
				case 'F j, Y':
					var month_int = parseInt( month ) - 1;
					var month_full = obp_fullcalendar['months_name'][month_int];

					date_str = month_full+' '+day+', '+year;
					break;
			default:
			// code block
			}
			return date_str;
        }
        
	};
    OBP_Frontend.init();
})(jQuery);