$(document).foundation();

$(document).ready(function(){
	$('#recent_projects_slider').slick({
		arrows: false,
		dots: true,
		slidesToShow: 3,
		autoplay: true,
		autoplaySpeed: 3000,
	});
	$('#who_i_am_slider').slick({
		arrows: false,
		autoplay: true,
		autoplaySpeed: 2000,
		fade: true,
		vertical: false
	});
});