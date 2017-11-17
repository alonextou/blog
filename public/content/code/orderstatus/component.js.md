if ($('#com_jw_lookup').length > 0 ){
	var com_jw_lookup = new Com_jw_lookup();
	com_jw_lookup.init();
}

function Com_jw_lookup(){

	var self = this;
	self.order = {};
	self.ordertype;
	self.inputmask = {};
	self.inputmask['store'] = '1234-123456';
	self.inputmask['online'] = 'W123456789';
	self.inputlength = {};
	self.inputlength['store'] = 11;
	self.inputlength['online'] = 10;
	self.phones = {};
	self.phones['store'] = '1-800-HOME-DEPOT (1-800-466-3337)';
	self.phones['online'] = '1-800-430-3376';

	self.init = function(){

		// Next
		$('input.next').click(function(){
			var valid = self.validateStep(1);
			if(!valid) return false;

			self.ordertype = $('input[name="ordertype"]:checked').val();
			$('.typeinfo').hide();
			$('.typeinfo[data-type=' + self.ordertype + ']').show();

			if(self.ordertype === 'online'){
				$('#lookup-contact h6 b').html(self.phones[self.ordertype]);
			}

			var ordernumInput = $('input[name="ordernum"]');
			$(ordernumInput).attr("placeholder", self.inputmask[self.ordertype]);
			$(ordernumInput).attr("maxlength", self.inputlength[self.ordertype]);

			$('.lookup-step[data-step=1]').hide();
			$('.lookup-step[data-step=2]').show();
			$('#errormsg').html('').hide();
		});

		// Prev
		$('input.prev').click(function(){
			$('input[name="ordernum"]').val('');
			$('input[name="lastname"]').val('');
			$('.lookup-step[data-step=2]').hide();
			$('.lookup-step[data-step=1]').show();
			$('#errormsg').html('').hide();
			$('#lookup-contact h6 b').html('1-800-HOME-DEPOT <br> (1-800-466-3337)');
		});

		// Submit
		$('#lookup-form').submit(function(e){
			e.preventDefault();

			var valid = self.validateStep(2);
			if(!valid) return false;

			var form = this;
			var url = form.action;
			var input = $('#lookup-form').serialize();
			$.post(url, input)
				.done(function(response){
					if(response.result == 'success'){
						self.showResult(response);
					} else if(response.result == 'fail'){
						self.showFail();
					} else {
						self.showNoResult();
					}
				}).fail(function(xhr) {
					self.showFail();
					console.log(xhr);
				}
			);
		});

		// New Lookup
		$('input.newlookup').click(function(){
			$('#lookup-result li').removeClass();
			$('#lookup-result li .check').hide();
			$('#lookup-result .ordernum').html('');
			$('#lookup-result #recdate').html('');
			$('#lookup-result #estdate').html('Not Yet Available');
			$('input[name="ordertype"]').prop('checked', false);
			$('input[name="ordernum"]').val('');
			$('input[name="lastname"]').val('');
			$('.lookup-step[data-step=3]').hide();
			$('.lookup-step[data-step=1]').show();
			$('#lookup-contact h6 b').html('1-800-HOME-DEPOT <br> (1-800-466-3337)');
		});

		// Loading icon
		$("#ajax-loader").bind("ajaxStart", function(){
			$(this).show();
		}).bind("ajaxComplete", function(){
			$(this).hide();
		});

		// Invoice Modal
		$('a#popup-store').click(function(e){
			console.log($(this).data('path'));
			var path = $(this).data('path') + 'components/com_jw_lookup/assets/img/invoices/' + self.ordertype + '.png';
			modal.open({
					content: '<img src="' + path + '" width="100%">',
					width: 700,
					height: 400
			});
			e.preventDefault();
		});

		$('a#popup-online').click(function(e){
			console.log($(this).data('path'));
			var path = $(this).data('path') + 'components/com_jw_lookup/assets/img/invoices/' + self.ordertype + '.png';
			modal.open({
				content: '<img src="' + path + '" width="100%">',
				width: 700,
				height: 400
			});
			e.preventDefault();
		});

		// Input mask
		$('input[name="ordernum"]').keyup(function() {
			$('input[name="ordernum"]').removeClass('error');
			var start = this.selectionStart,
        		end = this.selectionEnd;
			if (event.keyCode == 8 || event.keyCode == 46 || event.keyCode == 37 || event.keyCode == 39) return false;
			if(self.ordertype === 'store') {
				var value = $(this).val();
				if (value.length > 4) {
					value = value.replace('-', '');
					$(this).val(value.substring(0,4) + '-' + value.slice(4));
				} 
				if(start === 5){
					start++;
					end++;
				}
			}
			this.setSelectionRange(start, end);
		});
	}

	self.showResult = function(result){
		$('.lookup-step[data-step=2]').hide();
		$('.lookup-step[data-step=3]').show();

		$('#lookup-result .ordernum').html(result.ordernum);
		$('#lookup-result #recdate').html(result.recdate);
		$('#lookup-result #estdate').html(result.estdate);
		$('#lookup-result #lastupdate').html(result.lastupdate);
		
		for(i = 0; i < (result.step); i++) {
			var item = $('#lookup-result li:nth-child('+ i +')');
			item.addClass('success');
			item.children('.check').css('display', 'inline-block');
		}

		$('#lookup-result li:nth-child('+ result.step +')').addClass('current');
		if(result.step > 5){
			$('#lookup-result li:nth-child(5) h5').html('Shipped Order');
		} else {
			$('#lookup-result li:nth-child(5) h5').html('Shipping Order');
		}

		switch(result.extra) {
			case 'partial':
				$('#lookup-extra').html('<p>* One or more of your items have shipped. Please contact customer service for information about the remaining items in your order: ' + self.phones[self.ordertype] + '</p>');
				break;
			case 'split':
			case 'cancel':
				$('#lookup-extra').html('<p>* Please contact customer service regarding your order.<br> We apologize for any inconvenience. <br><b>Please Contact: ' + self.phones[self.ordertype] + '</b></p>');
				break;
			default:
				$('#lookup-extra').html('');
		}
	}

	self.showNoResult = function(){
		$('#errormsg').html('Your order could not be found. Please re-enter the order number as it appears on the receipt or in the confirmation email.').show();
	}

	self.showFail = function(){
		$('#errormsg').html('Please contact customer service regarding your order.<br> We apologize for any inconvenience. <br><b>Please Contact: ' + self.phones[self.ordertype] + '</b>').show();
	}

	self.validateStep = function(step){
		var isValid = true;
		switch(step){
			case 1:
				if(!$('input[name="ordertype"]').is(':checked')){
					$('#errormsg').html('Please choose an order type.').show();
					isValid = false;
				}
				break;
			case 2:
				var value = $('input[name="ordernum"]').val().replace('-', '');
				if(value == ''){
					$('#errormsg').html('Please input your order number.').show();
					$('input[name="ordernum"]').addClass('error');
					isValid = false;
				} else if(self.ordertype == 'store') {
					//var trim = value.replace('-', '');
					if(isNaN(value)){
						$('#errormsg').html('Your order number should only contain numbers.').show();
						$('input[name="ordernum"]').addClass('error');
						isValid = false;
					} else if(value.length <= 4){
						$('#errormsg').html('Please verify your order number length.').show();
						$('input[name="ordernum"]').addClass('error');
						isValid = false;
					}
				} else if(self.ordertype == 'online') {
					var first = value.charAt(0).toUpperCase();
					if(first != 'W' && first != 'C'){
						$('#errormsg').html('Your order number should begin with a C or W.').show();
						$('input[name="ordernum"]').addClass('error');
						isValid = false;
					} else if(value.length != 10){
						$('#errormsg').html('Your order number should be 10 characters long.').show();
						$('input[name="ordernum"]').addClass('error');
						isValid = false;
					}
				}
				if(isValid) {
					$('input[name="ordernum"]').removeClass('error');
				}
				break;
		}
		if(isValid) $('#errormsg').html('').hide();
		return isValid;
	}
}

var modal = (function(){
	var 
	method = {},
	$overlay,
	$modal,
	$content,
	$close;

	// Center the modal in the viewport
	method.center = function () {
		var top, left;

		top = Math.max($(window).height() - $modal.outerHeight(), 0) / 2;
		left = Math.max($(window).width() - $modal.outerWidth(), 0) / 2;

		$modal.css({
			top:top + $(window).scrollTop(), 
			left:left + $(window).scrollLeft()
		});
	};

	// Open the modal
	method.open = function (settings) {
		$content.empty().append(settings.content);

		$modal.css({
			width: settings.width || 'auto', 
			height: settings.height || 'auto'
		});

		method.center();
		$(window).bind('resize.modal', method.center);
		$modal.show();
		$overlay.show();
	};

	// Close the modal
	method.close = function () {
		$modal.hide();
		$overlay.hide();
		$content.empty();
		$(window).unbind('resize.modal');
	};

	// Generate the HTML and add it to the document
	$overlay = $('<div class="com_jw_lookup hopup overlay"></div>');
	$modal = $('<div class="com_jw_lookup hopup main"></div>');
	$content = $('<div class="com_jw_lookup hopup content"></div>');
	$close = $('<a class="com_jw_lookup hopup close" href="#">close</a>');

	$modal.hide();
	$overlay.hide();
	$modal.append($content, $close);

	$(document).ready(function(){
		$('body').append($overlay, $modal);						
	});

	$close.click(function(e){
		e.preventDefault();
		method.close();
	});

	return method;
}());