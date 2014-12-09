$(document).foundation();

$(document).ready(function(){
	$('#recent_projects_slider').slick({
		arrows: true,
		dots: true,
		slidesToShow: 3
	});
	$('#who_i_am_slider').slick({
		autoplay: true,
		autoplaySpeed: 1700,
		fade: false
	});
});