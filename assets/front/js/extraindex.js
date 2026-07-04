$(function ($) {

	"use strict";

         function lazy (){
			$(".lazy").Lazy({
				scrollDirection: 'vertical',
				effect: "fadeIn",
				effectTime:1000,
				threshold: 0,
				visibleOnly: false,  
				onError: function(element) {
					console.log('error loading ' + element.data('src'));
				}
			});
		}

        function applyBannerTextContrast() {
            var targets = document.querySelectorAll('.hero-slider .item, .sright-image, .genius-banner');

            function imageSource(target) {
                var img = target.querySelector('img');
                if (img) {
                    return img.currentSrc || img.getAttribute('src') || img.getAttribute('data-src');
                }

                var background = window.getComputedStyle(target).backgroundImage || '';
                var match = background.match(/url\(["']?(.*?)["']?\)/);
                return match ? match[1] : '';
            }

            function textArea(target) {
                return target.querySelector('.inner-content') || target.querySelector('.item-inner') || target.querySelector('.from-bottom') || target;
            }

            function drawCover(context, img, width, height) {
                var imageWidth = img.naturalWidth || img.width;
                var imageHeight = img.naturalHeight || img.height;
                var scale = Math.max(width / imageWidth, height / imageHeight);
                var drawWidth = imageWidth * scale;
                var drawHeight = imageHeight * scale;
                var dx = (width - drawWidth) / 2;
                var dy = (height - drawHeight) / 2;
                context.drawImage(img, dx, dy, drawWidth, drawHeight);
            }

            function sampleBounds(target, canvasWidth, canvasHeight) {
                if (target.matches('.hero-slider .item')) {
                    return { left: 0, top: 0, width: Math.ceil(canvasWidth * 0.48), height: canvasHeight };
                }

                var targetRect = target.getBoundingClientRect();
                var textRect = textArea(target).getBoundingClientRect();
                var scaleX = canvasWidth / Math.max(1, targetRect.width);
                var scaleY = canvasHeight / Math.max(1, targetRect.height);
                var paddingX = Math.max(12, textRect.width * 0.18);
                var paddingY = Math.max(12, textRect.height * 0.18);

                var left = Math.max(0, Math.floor((textRect.left - targetRect.left - paddingX) * scaleX));
                var top = Math.max(0, Math.floor((textRect.top - targetRect.top - paddingY) * scaleY));
                var right = Math.min(canvasWidth, Math.ceil((textRect.right - targetRect.left + paddingX) * scaleX));
                var bottom = Math.min(canvasHeight, Math.ceil((textRect.bottom - targetRect.top + paddingY) * scaleY));

                if (right <= left || bottom <= top) {
                    return { left: 0, top: 0, width: Math.ceil(canvasWidth * 0.45), height: canvasHeight };
                }

                return { left: left, top: top, width: right - left, height: bottom - top };
            }

            function setFallback(target) {
                target.classList.remove('banner-text-dark');
                target.classList.toggle('banner-text-dark', target.matches('.hero-slider .item'));
                target.classList.toggle('banner-text-light', !target.matches('.hero-slider .item'));
            }

            function calculate(target, src) {
                if (!src) {
                    setFallback(target);
                    return;
                }

                var img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = function () {
                    try {
                        var canvas = document.createElement('canvas');
                        var context = canvas.getContext('2d', { willReadFrequently: true });
                        var rect = target.getBoundingClientRect();
                        var isHero = target.matches('.hero-slider .item');
                        var width = 96;
                        var imageWidth = img.naturalWidth || img.width;
                        var imageHeight = img.naturalHeight || img.height;
                        var height = isHero
                            ? Math.max(1, Math.round(imageHeight / Math.max(1, imageWidth) * width))
                            : Math.max(1, Math.round(rect.height / Math.max(1, rect.width) * width));
                        canvas.width = width;
                        canvas.height = height;
                        context.fillStyle = '#ffffff';
                        context.fillRect(0, 0, width, height);
                        if (isHero) {
                            context.drawImage(img, 0, 0, width, height);
                        } else {
                            drawCover(context, img, width, height);
                        }

                        var bounds = sampleBounds(target, width, height);
                        var data = context.getImageData(bounds.left, bounds.top, bounds.width, bounds.height).data;
                        var total = 0;
                        var count = 0;
                        var brightCount = 0;
                        var darkCount = 0;
                        for (var i = 0; i < data.length; i += 16) {
                            var alpha = data[i + 3];
                            if (alpha < 20) continue;
                            var luminance = (0.299 * data[i]) + (0.587 * data[i + 1]) + (0.114 * data[i + 2]);
                            total += luminance;
                            if (luminance >= 170) brightCount++;
                            if (luminance <= 95) darkCount++;
                            count++;
                        }

                        var brightness = count ? total / count : 0;
                        var brightRatio = count ? brightCount / count : 0;
                        var darkRatio = count ? darkCount / count : 0;
                        var useDarkText = isHero
                            ? (brightness >= 105 || brightRatio >= 0.18 || darkRatio < 0.55)
                            : (darkRatio <= 0.65 && (brightness >= 110 || brightRatio >= 0.25));
                        target.classList.toggle('banner-text-dark', useDarkText);
                        target.classList.toggle('banner-text-light', !useDarkText);
                    } catch (e) {
                        setFallback(target);
                    }
                };
                img.onerror = function () {
                    setFallback(target);
                };
                img.src = src;
            }

            targets.forEach(function (target) {
                calculate(target, imageSource(target));
            });
        }

		$(document).ready(function(){
			lazy();
            applyBannerTextContrast();
            setTimeout(applyBannerTextContrast, 1200);
		})
	// Flash Deal Area Start
    var $hero_slider_main = $(".hero-slider-main");
    $hero_slider_main.owlCarousel({
        navText: [],
        nav: true,
        dots: true,
        loop: true,
        autoplay: true,
        autoplayTimeout: 7000,
        items: 1,
    });
    $hero_slider_main.on('initialized.owl.carousel translated.owl.carousel refreshed.owl.carousel', function () {
        applyBannerTextContrast();
    });
    setTimeout(applyBannerTextContrast, 300);

    // popular_category_slider
    var $popular_category_slider = $(".popular-category-slider");
    $popular_category_slider.owlCarousel({
        navText: [],
        nav: true,
        dots: false,
        loop: false,
        autoplayTimeout: 6000,
        smartSpeed: 1200,
        margin: 15,
        responsive: {
            0: {
                items: 2,
            },
            576: {
                items: 2,
            },
            768: {
                items: 3,
            },
            992: {
                items: 4,
            },
            1200: {
                items: 4,
            },
            1400: {
                items: 5
            }
        },
    });



    // Flash Deal Area Start
    var $flash_deal_slider = $(".flash-deal-slider");
    $flash_deal_slider.owlCarousel({
        navText: [],
        nav: true,
        dots: false,
        autoplayTimeout: 6000,
        smartSpeed: 1200,
        margin: 15,
        responsive: {
            0: {
                items: 1,
                margin: 0,
            },
            576: {
                items: 2,
                margin: 0,
            },
            768: {
                items: 3,
                margin: 0,
            },
            992: {
                items: 4,
                margin: 0,
            },
            1200: {
                items: 4,
                margin: 0,
            },
            1400: {
                items: 1,
            },
        },
    });

    // col slider
    var $col_slider = $(".newproduct-slider");
    $col_slider.owlCarousel({
        navText: [],
        nav: true,
        dots: false,
        loop: false,
        autoplayTimeout: 6000,
        smartSpeed: 1200,
        margin: 15,
        responsive: {
            0: {
                items: 1,
            },
            530: {
                items: 1,
            },
        },
    });

    // col slider 2
    var $col_slider2 = $(".toprated-slider");
    $col_slider2.owlCarousel({
        navText: [],
        nav: true,
        dots: false,
        loop: true,
        autoplayTimeout: 6000,
        smartSpeed: 1200,
        margin: 15,
        responsive: {
            0: {
                items: 1,
            },
            530: {
                items: 1,
            },
        },
    });

    // newproduct-slider Area Start
    var $newproduct_slider = $(".features-slider");
    $newproduct_slider.owlCarousel({
        navText: [],
        nav: true,
        dots: false,
        autoplayTimeout: 6000,
        smartSpeed: 1200,
        loop: false,
        margin: 15,
        responsive: {
            0: {
                items: 2,
            },
            576: {
                items: 2,
            },
            768: {
                items: 3,
            },
            992: {
                items: 4,
            },
            1200: {
                items: 4,
            },
            1400: {
                items: 5
            }
        },
    });

    // home-blog-slider
    var $home_blog_slider = $(".home-blog-slider");
    $home_blog_slider.owlCarousel({
        navText: [],
        nav: true,
        dots: false,
        autoplayTimeout: 6000,
        smartSpeed: 1200,
        loop: true,
        margin: 15,
        responsive: {
            0: {
                items: 1,
            },
            576: {
                items: 2,
            },
            768: {
                items: 3,
            },
            992: {
                items: 3,
            },
            1200: {
                items: 3,
            },
            1400: {
                items: 4,
            }
        },
    });


    // brand-slider
    var $brand_slider = $(".brand-slider");
    $brand_slider.owlCarousel({
        navText: [],
        nav: true,
        dots: false,
        autoplayTimeout: 6000,
        smartSpeed: 1200,
        loop: true,
        margin: 0,
        responsive: {
            0: {
                items: 2,
            },
            575: {
                items: 3,
            },
            790: {
                items: 4,
            },
            1100: {
                items: 4,
            },
            1200: {
                items: 4,
            },
            1400: {
                items: 5,
            }
        },
    });

    // toprated-slider Area Start
    var $relatedproductsliderv = $(".relatedproductslider");
    $relatedproductsliderv.owlCarousel({
        nav: false,
        dots: true,
        autoplayTimeout: 6000,
        smartSpeed: 1200,
        margin: 15,
        responsive: {
            0: {
                items: 2,
            },
            576: {
                items: 2,
            },
            768: {
                items: 3,
            },
            992: {
                items: 4,
            },
            1200: {
                items: 4,
            },
            1400: {
                items: 5
            }
        },
    });


$('.left-category-area .category-header').on('click', function(){
    $('.left-category-area .category-list').toggleClass("active")
});


$("[data-date-time]").each(function () {
    var $this = $(this),
        finalDate = $(this).attr("data-date-time");
    var labels = window.language || {};
    $this.countdown(finalDate, function (event) {
        $this.html(
            event.strftime(
                "<span>%D<small>" + (labels.Days || "Dias") + "</small></span> <span>%H<small>" + (labels.Hrs || "Horas") + "</small></span> <span>%M<small>" + (labels.Min || "Min") + "</small></span> <span>%S<small>" + (labels.Sec || "Seg") + "</small></span>"
            )
        );
    });
});

// Subscriber Form Submit
$(document).on("submit", ".subscriber-form", function (e) {
    e.preventDefault();
    var $this = $(this);
    var submit_btn = $this.find("button");
    submit_btn.find(".fa-spin").removeClass("d-none");
    $this.find("input[name=email]").prop("readonly", true);
    submit_btn.prop("disabled", true);
    $.ajax({
        method: "POST",
        url: $(this).prop("action"),
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData: false,
        success: function (data) {
            if (data.errors) {
                for (var error in data.errors) {
                    dangerNotification(data.errors[error]);
                }
            } else {
                if ($this.hasClass("subscription-form")) {
                    $(".close-popup").click();
                }
                successNotification(data);
                $this.find("input[name=email]").val("");
            }
            submit_btn.find(".fa-spin").addClass("d-none");
            $this.find("input[name=email]").prop("readonly", false);
            submit_btn.prop("disabled", false);
        },
    });
});


});
