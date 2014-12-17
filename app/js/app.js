$(document).foundation();

$(document).ready(function(){
	$('#recent_projects_slider').slick({
		arrows: true,
		dots: true,
		slidesToShow: 3,
		autoplay: true,
		autoplaySpeed: 1700
	});
	$('#who_i_am_slider').slick({
		arrows: false,
		autoplay: true,
		autoplaySpeed: 1700,
		fade: false
	});
});