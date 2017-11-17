```javascript
if ($('#com_jw_egresscalc').length > 0 ) {
	var egresscalc = new Com_jw_egresscalc();
	egresscalc.init();
}

function Com_jw_egresscalc () {

	var self = this;
	self.allOptions;
	self.inputForm = $('#com_jw_egresscalc form');
	self.inputMaterial = $('#com_jw_egresscalc form select[name="material"]');
	self.inputProduct = $('#com_jw_egresscalc form select[name="product"]');
	self.inputStyle = $('#com_jw_egresscalc form select[name="style"]');
	self.inputSizes = $('#com_jw_egresscalc form #egresscalc-size-input');
	self.inputSizesType = $('#com_jw_egresscalc form input[name="input-size-type"]');
	self.inputFrameWidth = $('#com_jw_egresscalc form input[name="frameWidth"]');
	self.inputFrameWidthFraction = $('#com_jw_egresscalc form select[name="frameWidth_fraction"]');
	self.inputFrameHeight = $('#com_jw_egresscalc form input[name="frameHeight"]');
	self.inputFrameHeightFraction = $('#com_jw_egresscalc form select[name="frameHeight_fraction"]');
	self.inputLegHeight = $('#com_jw_egresscalc form input[name="legHeight"]');
	self.inputLegHeightFraction = $('#com_jw_egresscalc form select[name="legHeight_fraction"]');
	self.selectedInputSizesType = 'fraction';
	self.formulas = [];
	self.results = [];
	self.errors = {};
	self.egressChecks = {
		first: {
			cow: '>= 20',
			coh: '>= 24',
			cos: '>= 5.0',
			vas: '>= 0',
			dos: '>= 0'
		},
		second: {
			cow: '>= 20',
			coh: '>= 24',
			cos: '>= 5.7',
			vas: '>= 0',
			dos: '>= 0'
		}
	}

    self.init = function() {
    	self.inputMaterial.attr("disabled", true);
    	self.inputProduct.attr("disabled", true);
    	self.inputStyle.attr("disabled", true);

		self.getOptions().done(function(data) {
			self.allOptions = JSON.parse(data);
			self.fillMaterials();
			self.inputMaterial.bind('change', self.fillProducts);
			self.inputProduct.bind('change', self.fillStyles);
			self.inputStyle.bind('change', self.showHideSizing);
			self.inputSizesType.bind('change', self.switchSizesType);
			self.inputForm.bind('submit', function(e){
				e.preventDefault();
				self.processForm();
			});
			self.inputSizes.find('input').bind('keyup', function(){
				$('#egresscalc-results').hide();
			});
			self.inputSizes.find('select').bind('change', function(){
				$('#egresscalc-results').hide();
			});
		});
    }

    self.serviceDown = function(error) {
    	$('#egresscalc-service-down').show();
    	if (typeof error != 'undefined') {
			console.log(error);
    	}
    }

    self.getOptions = function() {
		return $.ajax({
			url: 'index.php/?option=com_jw_egresscalc&task=egresscalc.getOptions&format=raw',
			error: function(xhr, status, error){
				self.serviceDown(xhr.status + ": " + error);
			}
		});
    }

    self.fillMaterials = function() {
    	$.each(self.allOptions, function(index, material) {
    		self.inputMaterial.append($('<option>', {value: index}).text(material.title));
    	});
    	self.inputMaterial.attr("disabled", false);
    	self.inputProduct.attr("disabled", false);
    	self.inputStyle.attr("disabled", false);
    }

    self.fillProducts = function() {
    	var materialId = self.inputMaterial.val();

		self.clearInput('product');
		self.clearInput('style');
		self.showHideSizing();

		if (!materialId) {
			return;
		}

    	$.each(self.allOptions[materialId].products, function(index, product) {
    		self.inputProduct.append($('<option>', {value: index}).text(product.title));
    	});
    }

    self.fillStyles = function() {
    	var materialId = self.inputMaterial.val();
    	var productId = self.inputProduct.val();

		self.clearInput('style');
		self.showHideSizing();

		if (!productId) {
			return;
		}

    	$.each(self.allOptions[materialId].products[productId].styles, function(index, style) {
    		self.inputStyle.append($('<option>', {value: style.id}).text(style.title));
    	});
    }

    self.clearInput = function(inputField) {
    	inputField = inputField.charAt(0).toUpperCase() + inputField.slice(1);
    	eval('self.input' + inputField).find('option:not(.placeholder)').remove();
	}

	self.showHideSizing = function() {
    	var styleId = self.inputStyle.val();
    	var frameWidth = self.inputFrameWidth.val();
    	var frameHeight = self.inputFrameHeight.val();

    	if (!styleId) {
    		self.inputSizes.find('input[type="text"], select').val('');
    		self.inputSizes.hide();  
    		$('#egresscalc-results').hide();
    	} else {
    		self.getFormula(styleId, frameWidth, frameHeight).done(function(data) {
				self.formulas = JSON.parse(data);
				$('#egresscalc-size-input>h3').html(self.inputStyle.find('option:selected').text());
				self.inputSizes.show();
				if(self.formulas.has_leg == 1) {
					$('#egresscalc-input-legheight').show();
				} else {
					$('#egresscalc-input-legheight').hide();
				}
				self.errors = {};
    			self.inputForm.find('input.error').removeClass('error').prev('.error').text('');
			});		
    	}
	}

	self.switchSizesType = function() {
		self.selectedInputSizesType = $('input[name="input-size-type"]:checked').val();
		self.inputForm.find('input.error').removeClass('error').prev('.error').text('');
		$('#egresscalc-results').hide();
		if(self.selectedInputSizesType === 'decimal') {
			self.inputSizes.find('select.fraction-dropdown').hide();
			self.inputSizes.find('select.fraction-dropdown').val('');
		} else {
			self.inputSizes.find('select.fraction-dropdown').show();
		}
	}

	self.processForm = function() {
		var styleId = self.inputStyle.val();
		var frameWidth = self.inputFrameWidth.val();
		var frameWidthFraction = self.inputFrameWidthFraction.val();
		var frameHeight = self.inputFrameHeight.val();
		var frameHeightFraction = self.inputFrameHeightFraction.val();
		var legHeight = self.inputLegHeight.val();
		var legHeightFraction = self.inputLegHeightFraction.val();
		var formulas = self.formulas;

		self.errors = {};
		self.validateField(self.inputFrameWidth);
		self.validateField(self.inputFrameHeight);
		if(self.formulas.has_leg == true){
			self.validateField(self.inputLegHeight);
		}

		if($.isEmptyObject(self.errors)) {
			if(self.selectedInputSizesType === 'fraction') {
				if(frameWidthFraction != ''){
					frameWidth = self.addFraction(frameWidth, frameWidthFraction);
				}
				if(frameHeightFraction != ''){
					frameHeight = self.addFraction(frameHeight, frameHeightFraction);
				}
				if(legHeightFraction != ''){
					legHeight = self.addFraction(legHeight, legHeightFraction);
				}
			}
			self.postForm(styleId, frameWidth, frameHeight, legHeight, formulas).done(function(data) {
				self.results = JSON.parse(data);
				self.fillResults();
				self.checkResults();
			});
		} else {
			$('#egresscalc-results').hide();
		}
	}

	self.validateField = function(field) {
		field.removeClass('error');
		field.prev('.error').text('');

		var messages = {
			blank: 'This field can not be left blank.',
			numeric: 'This field must be a numeric value.',
			decimal: 'Please switch input type to decimals.',
			comma: 'This field may not contain commas.',
			fraction: 'Please switch input type to fractions.'
		}

		// Number
		if(isNaN(field.val())) {
			self.errors[field.attr('name')] = messages.numeric;
			field.prev('.error').text(messages.numeric);
			field.addClass('error');
		}

		// Fraction
		if(self.selectedInputSizesType === 'decimal') {
			if(field.val().indexOf('/') != -1)
			{
				self.errors[field.attr('name')] = messages.fraction;
				field.prev('.error').text(messages.fraction);
				field.addClass('error');
			}
		}

		// Decimal
		if(self.selectedInputSizesType === 'fraction') {
			if(field.val().indexOf('.') != -1)
			{
				self.errors[field.attr('name')] = messages.decimal;
				field.prev('.error').text(messages.decimal);
				field.addClass('error');
			}
		}

		// Comma
		if(self.selectedInputSizesType === 'fraction') {
			if(field.val().indexOf(',') != -1)
			{
				self.errors[field.attr('name')] = messages.comma;
				field.prev('.error').text(messages.comma);
				field.addClass('error');
			}
		}

		// Blank
		if(field.val() === '') {
			self.errors[field.attr('name')] = messages.blank;
			field.prev('.error').text(messages.blank);
			field.addClass('error');
		}
	}

	self.postForm = function(styleId, frameWidth, frameHeight, legHeight, formulas) {
		return $.ajax({
			url: 'index.php/?option=com_jw_egresscalc&task=egresscalc.postForm&format=raw',
			data: { styleId: styleId, frameWidth: frameWidth, frameHeight: frameHeight, legHeight: legHeight, formulas: formulas },
			error: function(xhr, status, error){
				self.serviceDown(xhr.status + ": " + error);
			}
		});
	}

	self.getFormula = function(styleId, framewidth, frameheight) {
		return $.ajax({
			url: 'index.php/?option=com_jw_egresscalc&task=egresscalc.getFormula&format=raw',
			data: { styleId: styleId },
			error: function(xhr, status, error){
				self.serviceDown(xhr.status + ": " + error);
			}
		});
	}

	self.fillResults = function() {
		$('#egresscalc-results').show();
		$('#egresscalc-results tbody tr').hide();
		$.each(self.results, function(index, value) {
			$('#com_jw_egresscalc #egresscalc-results tr[data-result="' + index + '"]').show();
			$('#com_jw_egresscalc #egresscalc-results tr[data-result="' + index + '"] td.result').html(value);
		});
	}

	self.checkResults = function() {
		var hasError = false;
		var errors = {
			second: {},
			first: {}
		};
		var errorMessages = {
			second: "Meets IRC egress requirements for 1st floor only.",
			first: "Does NOT meet IRC Egress Requirements."
		};
		$('#egresscalc-results td').removeClass('error');
		$.each(self.results, function(key) {
			$.each(self.egressChecks, function(floor) {
				if(!self.resultPasses(key, floor)) {
					hasError = true;
					errors[floor][key] = true;
					$('#com_jw_egresscalc #egresscalc-results tr[data-result="' + key + '"] td.' + floor).addClass('error');
				}
			});
		});
		if(hasError === true) {
			$.each(errors, function(floor, value) {
				if(!$.isEmptyObject(value)){
					$('#com_jw_egresscalc .result-message').html('<span class="warning">' + errorMessages[floor] + '</span>');
				}
			});
		} else {
			$('#com_jw_egresscalc .result-message').html('<span class="success">Meets IRC Egress Requirements.</span>');
		}
	}

	self.resultPasses = function(key, floor) {
		return eval(self.results[key] + self.egressChecks[floor][key]);
	}

	self.addFraction = function(number, fraction) {
		var splitFraction = fraction.split('/');
		fraction = splitFraction[0] / splitFraction[1];
		return parseFloat(number) + fraction;
	}

}
```