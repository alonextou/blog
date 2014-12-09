$(document).foundation();

$(document).ready(function(){
	$('#recent_projects_slider').slick({
		arrows: false,
		dots: true,
		slidesToShow: 3
	});
	$('#who_i_am_slider').slick({
		arrows: false,
		autoplay: true,
		autoplaySpeed: 1700,
		fade: false
	});
});