$(document).foundation();

$(document).ready(function(){
	$('#recent_projects_slider').slick({
		arrows: false,
		dots: true,
		slidesToShow: 3,
		autoplay: true,
		autoplaySpeed: 2000
	});
	$('#who_i_am_slider').slick({
		arrows: false,
		autoplay: true,
		autoplaySpeed: 1500,
		fade: true,
		vertical: false
	});
	$(document.links).filter(function() {
    	return this.hostname != window.location.hostname;
	}).attr('target', '_blank');
});