window.addEventListener('load', function() {
	function HoverWatcher(selector) {
		this.hovering = false;
		var self = this;
	
		this.isHoveringOver = function () {
			return self.hovering;
		}
	
		$(selector).hover(function () {
			self.hovering = true;
		}, function () {
			self.hovering = false;
		})
	}
	
	function LangCurDropDown(selector, subsel) {
		var main_block = new HoverWatcher(selector);
		var sub_ul = new HoverWatcher(subsel);
		$(selector).click(function () {
			$(selector).addClass('active');
			$(subsel).slideToggle('slow');
			setTimeout(function () {
				if (!main_block.isHoveringOver() && !sub_ul.isHoveringOver())
					$(subsel).stop(true, true).slideUp(450);
				$(selector).removeClass('active');
			}, 3000);
		});
	
		$(subsel).hover(function () {
			setTimeout(function () {
				if (!main_block.isHoveringOver() && !sub_ul.isHoveringOver())
					$(subsel).stop(true, true).slideUp(450);
			}, 3000);
		});
	}


	LangCurDropDown('#cart', '.cart-menu');

	// $("#cart .dropdown-toggle").click(function () {
	// 	console.log('click');
	// 	$(this).toggleClass("active");
	// 	$(".cart-menu").slideToggle("slow");
	// 	$(".myaccount-menu").slideUp("slow");
	// 	$(".myaccount .dropdown-toggle").removeClass('active');
	// 	$(".menu_toggle").slideUp("slow");
	// 	return false;
	// });
});

var widthClassOptions = ({
	bestseller: 'bestseller_default_width',
	featured: 'featured_default_width',
	special: 'special_default_width',
	latest: 'latest_default_width',
	related: 'related_default_width',
	additional: 'additional_default_width',
	module: 'module_default_width',
	tabbestseller: 'tabbestseller_default_width',
	tabfeatured: 'tabfeatured_default_width',
	tabspecial: 'tabspecial_default_width',
	tablatest: 'tablatest_default_width',
	blog: 'blog_default_width',
	testimonial: 'testimonial_default_width'
});

$(document).ready(function () {
  $('ul.breadcrumb').prependTo('.row #title-content');
	$('#content h1').prependTo('.row #title-content');
	$('#content h2').prependTo('.row #title-content');
	$('#content .sidebarFilter').insertBefore('.category_filter');

	// $(".myaccount > .dropdown-toggle").click(function () {
	// 	$(".cart-menu").slideUp("slow");
	// 	$(".myaccount-menu").slideToggle("slow");
	// 	$(this).toggleClass("active");
	// 	$("#cart .dropdown-toggle").removeClass('active');
	// 	$(".menu_toggle").slideUp("slow");
	// 	return false;
	// });

	$('.write-review,.review-count').on('click', function () {
		$('html, body').animate({scrollTop: $('#tabs_info').offset().top}, 'slow');
	});

});



function mobileToggleMenu() {
	//alert($(window).width());
	if ($(window).width() < 980) {
		$("#footer .mobile_togglemenu").remove();
		$("#footer .column h5,#footer .box-heading").append("<a class='mobile_togglemenu'>&nbsp;</a>");
		$("#footer .column h5,#footer .box-heading").addClass('toggle');
		$("#footer .mobile_togglemenu").click(function () {
			$(this).parent().toggleClass('active').parent().find('ul,.box-content').toggle('slow');
		});

	} else {
		$("#footer .column h5,#footer .box-heading").parent().find('ul,.box-content').removeAttr('style');
		$("#footer .column h5,#footer .box-heading").removeClass('active');
		$("#footer .column h5,#footer .box-heading").removeClass('toggle');
		$("#footer .mobile_togglemenu").remove();
	}
}

$(document).ready(function () {
	mobileToggleMenu();
});
$(window).resize(function () {
	mobileToggleMenu();
});


function menuResponsive() {
	if ($(window).width() < 980) {
		$("#menu").addClass('responsive-menu');
		$("#menu").removeClass('main-menu');
		$('.nav-responsive').css('display', 'block');
		$("#menu .mobile_togglemenu").remove();
		$("#menu ul li.dropdown").append("<a class='mobile_togglemenu'>&nbsp;</a>");
		$("#menu ul li.dropdown").addClass('toggle');

		$("#menu .nav > li.dropdown .mobile_togglemenu").click(function () {
			$(this).parent().toggleClass('active');
			$(this).siblings("li .dropdown-menu").slideToggle('slow');
		});

	} else {
		$("#menu").addClass('main-menu');
		$("#menu").removeClass('responsive-menu');
		$("#menu ul li.dropdown").parent().find('li .dropdown-menu').removeAttr('style');
		$("#menu ul li.dropdown").removeClass('active');
		$("#menu ul li.dropdown").removeClass('toggle');
		$("#menu .mobile_togglemenu").remove();
		$('.nav-responsive').css('display', 'none');
	}
}

$(document).ready(function () {
	menuResponsive();
});
$(window).resize(function () {
	menuResponsive();
});


$(document).ready(function () {
	$(".dropdown-toggle").click(function () {
		$("ul.dropdown-toggle").toggle('slow');
	});
});

function leftright() {
	if ($(window).width() < 980) {
		if ($('.category_filter .col-md-3, .category_filter .col-md-2, .category_filter .col-md-1').hasClass('text-right') == true) {
			$(".category_filter .col-md-3, .category_filter .col-md-2, .category_filter .col-md-1").addClass('text-left');
			$(".category_filter .col-md-3, .category_filter .col-md-2, .category_filter .col-md-1").removeClass('text-right');
		}
	}
}

$(document).ready(function () {
	leftright();
});
$(window).resize(function () {
	leftright();
});


function blogCrop() {
	if ($(window).width() > 979) {
		$('.extension-megnor_blog-home .blog-image').each(function () {
			var that = $(this);
			var url = that.find('img').attr('src');
			that.css({'background-image': 'url("' + url + '")'});
		});
	}
}

jQuery(document).ready(function () {
	blogCrop();
});
jQuery(window).resize(function () {
	blogCrop();
});


//LEFT-RIGHT COLUMN RESPONSIVE TOOGLE

function mobileToggleColumn() {
	if ($(window).width() < 980) {
		$('#column-left,#column-right').insertAfter('#content');
		$("#column-left .box-heading .mobile_togglemenu,#column-right .box-heading .mobile_togglemenu").remove();
		$("#column-left .box-heading,#column-right .box-heading").append("<a class='mobile_togglemenu'>&nbsp;</a>");
		$("#column-left .box-heading,#column-right .box-heading").addClass('toggle');
		$("#column-left .box-heading .mobile_togglemenu,#column-right .box-heading .mobile_togglemenu").click(function () {
			$(this).parent().toggleClass('active').parent().find('.box-content,.filterbox,.list-group').slideToggle('slow');
		});
	} else {
		$('#column-left').insertBefore('#content');
		$('#column-right').insertAfter('#content');
		$('.common-home #column-left,.common-home #column-right').insertBefore('#content-top');
		$("#column-left .box-heading,#column-right .box-heading").parent().find('.box-content,.filterbox,.list-group').removeAttr('style');
		$("#column-left .box-heading,#column-right .box-heading").removeClass('active');
		$("#column-left .box-heading,#column-right .box-heading").removeClass('toggle');
		$("#column-left .box-heading .mobile_togglemenu,#column-right .box-heading .mobile_togglemenu").remove();
	}
}

$(document).ready(function () {
	mobileToggleColumn();
});
$(window).resize(function () {
	mobileToggleColumn();
});


function productCarouselAutoSet() {
	$("#content .product-carousel, .banners-slider-carousel .product-carousel, .homepage-testimonials-inner .product-carousel,.box.related .product-carousel").each(function () {
		var objectID = $(this).attr('id');
		var myObject = objectID.replace('-carousel', '');
		if (myObject.indexOf("-") >= 0)
			myObject = myObject.substring(0, myObject.indexOf("-"));
		if (widthClassOptions[myObject])
			var myDefClass = widthClassOptions[myObject];
		else
			var myDefClass = 'grid_default_width';
		var slider = $("#content #" + objectID + ", .homepage-testimonials-inner #" + objectID + ", .banners-slider-carousel #" + objectID + ", .box.related #" + objectID);
		slider.sliderCarousel({
			defWidthClss: myDefClass,
			subElement: '.slider-item',
			subClass: 'product-block',
			firstClass: 'first_item_tm',
			lastClass: 'last_item_tm',
			slideSpeed: 200,
			paginationSpeed: 800,
			autoPlay: false,
			stopOnHover: false,
			goToFirst: true,
			goToFirstSpeed: 1000,
			goToFirstNav: true,
			pagination: false,
			paginationNumbers: false,
			responsive: true,
			responsiveRefreshRate: 200,
			baseClass: "slider-carousel",
			theme: "slider-theme",
			autoHeight: true
		});

		var nextButton = $(this).parent().find('.next');
		var prevButton = $(this).parent().find('.prev');
		nextButton.click(function () {
			slider.trigger('slider.next');
		})
		prevButton.click(function () {
			slider.trigger('slider.prev');
		})
	});
}

//$(window).load(function(){productCarouselAutoSet();});
$(document).ready(function () {
	productCarouselAutoSet();
});


function productListAutoSet() {
	$("#content .productbox-grid").each(function () {
		var objectID = $(this).attr('id');
		if (objectID.length > 0) {
			if (widthClassOptions[objectID.replace('-grid', '')])
				var myDefClass = widthClassOptions[objectID.replace('-grid', '')];
		} else {
			var myDefClass = 'grid_default_width';
		}
		$(this).smartColumnsRows({
			defWidthClss: myDefClass,
			subElement: '.product-items',
			subClass: 'product-block'
		});
	});
}

$(window).load(function () {
	productListAutoSet();
});
$(window).resize(function () {
	productListAutoSet();
});




$(document).ready(function () {

	// LangCurDropDown('#currency', '.currency_div');
	// LangCurDropDown('#language', '.language_div');

	$('.nav-responsive').click(function () {
		$('.nav-responsive div').toggleClass('active');
	});
});


$(document).ready(function () {
	$(".tm_headerlinks_inner").click(function () {
		$(".header_links").toggle('slow');
	});

});


// JS for calling loadMore
jQuery(document).ready(function () {
	var size_li = jQuery(".box.feature_box #featured-grid > div").size();
	var x = 8;

	jQuery('.box.feature_box #featured-grid > div:lt(' + x + ')').fadeIn('slow');

	jQuery('.box.feature_box .gridcount').click(function () {
		if (x == size_li) {
			jQuery('.box.feature_box .tm-message').show();
			jQuery('.box.feature_box .gridcount').hide();

		} else {
			x = (x + 8 <= size_li) ? x + 8 : size_li;
			jQuery('.box.feature_box #featured-grid > div:lt(' + x + ')').fadeIn('slow');
		}
	});

});


/*For Grid and List View Buttons*/
function gridlistactive() {
	$('.btn-list-grid button').on('click', function () {
		if ($(this).hasClass('grid')) {
			$('.btn-list-grid button').addClass('active');
			$('.btn-list-grid button.list').removeClass('active');
		}
		else if ($(this).hasClass('list')) {
			$('.btn-list-grid button').addClass('active');
			$('.btn-list-grid button.grid').removeClass('active');
		}
	});
}

$(document).ready(function () {
	gridlistactive()
});
$(window).resize(function () {
	gridlistactive()
});


/*For Back to Top button*/
$(document).ready(function () {
	$("body").append("<a class='top_button' title='Back To Top' href=''>TOP</a>");

	$(function () {
		$(window).scroll(function () {
			if ($(this).scrollTop() > 70) {
				$('.top_button').fadeIn();
			} else {
				$('.top_button').fadeOut();
			}
		});
		// scroll body to 0px on click
		$('.top_button').click(function () {
			$('body,html').animate({
				scrollTop: 0
			}, 800);
			return false;
		});
	});
});
