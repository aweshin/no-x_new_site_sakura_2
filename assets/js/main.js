/*
	Overflow by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
*/

(function($) {

	var	$window = $(window),
		$body = $('body'),
        $topheader = $('#topheader'),
		settings = {

			// Parallax background effect?
				parallax: true,

			// Parallax factor (lower = more intense, higher = less intense).
				parallaxFactor: 10

		};

	// Breakpoints.
		breakpoints({
			wide:    [ '1081px',  '1680px' ],
			normal:  [ '841px',   '1080px' ],
			narrow:  [ '737px',   '840px'  ],
			mobile:  [ null,      '736px'  ]
		});

	// Mobile?
		if (browser.mobile)
			$body.addClass('is-scroll');

	// Play initial animations on page load.
		$window.on('load', function() {
			window.setTimeout(function() {
				$body.removeClass('is-preload');
			}, 100);
		});

	// Scrolly.
		$('.scrolly-middle').scrolly({
			speed: 1000,
			anchor: 'middle'
		});

		$('.scrolly').scrolly({
			speed: 1000,
			offset: function() { return (breakpoints.active('<=mobile') ? 70 : 190); }
		});

	// Parallax background.

		// Disable parallax on IE/Edge (smooth scrolling is jerky), and on mobile platforms (= better performance).
			if (browser.name == 'ie'
			||	browser.name == 'edge'
			||	browser.mobile)
				settings.parallax = false;

		if (settings.parallax) {

			var $dummy = $(), $bg;

			$window
				.on('scroll.overflow_parallax', function() {

					// Adjust background position.
						$bg.css('background-position', 'center ' + (-1 * (parseInt($window.scrollTop()) / settings.parallaxFactor)) + 'px');

				})
				.on('resize.overflow_parallax', function() {

					// If we're in a situation where we need to temporarily disable parallax, do so.
						if (breakpoints.active('<=narrow')) {

							$body.css('background-position', '');
							$bg = $dummy;

						}

					// Otherwise, continue as normal.
						else
							$bg = $body;

					// Trigger scroll handler.
						$window.triggerHandler('scroll.overflow_parallax');

				})
				.trigger('resize.overflow_parallax');

		}

	// Poptrox.
		$('.gallery').poptrox({
			useBodyOverflow: false,
			usePopupEasyClose: false,
			overlayColor: '#0a1919',
			overlayOpacity: 0.75,
			usePopupDefaultStyling: false,
			usePopupCaption: true,
			popupLoaderText: '',
			windowMargin: 10,
			usePopupNav: true
		});

      // スクロール途中から表示したいメニューバーを指定
        
      // メニューバーは初期状態では消しておく
      $topheader.hide();

      // 表示を開始するスクロール量を設定(px)
      var TargetPos = 350;

      // スクロールされた際に実行
      $window.scroll( function() {
         // 現在のスクロール位置を取得
         var ScrollPos = $(window).scrollTop();
         // 現在のスクロール位置と、目的のスクロール位置を比較
         if( ScrollPos > TargetPos ) {
            // 表示(フェイドイン)
            $topheader.fadeIn();
         }
         else {
            // 非表示(フェイドアウト)
            $topheader.fadeOut();
         }
      });

    var headerHeight = $topheader.outerHeight(); //ヘッダの高さ
    $('a[href^="#"]').click(function(){
        var href= $(this).attr("href");
        var target = $(href == "#" || href == "" ? 'html' : href);
        var position = target.offset().top-headerHeight; //ヘッダの高さ分位置をずらす
        $("html, body").animate({scrollTop:position}, 1550, "swing");
        return false;
    });

    // スマホのハンバーガーメニュー
        $('.navToggle').click(function() {
        $(this).toggleClass('active');

        if ($(this).hasClass('active')) {
            $('.globalMenuSp').addClass('active');
        } else {
            $('.globalMenuSp').removeClass('active');
        }
    });
    
    $('.globalMenuSp a').click(function() {
         $('.navToggle').toggleClass('active');
         $('.globalMenuSp').removeClass('active');
    });
    	// Events.
//		var resizeTimeout, resizeScrollTimeout;
//
//		$window
//			.on('resize', function() {
//
//				// Disable animations/transitions.
//					$body.addClass('is-resizing');
//
//				clearTimeout(resizeTimeout);
//
//				resizeTimeout = setTimeout(function() {
//
//					// Update scrolly links.
//						$('a[href^="#"]').scrolly({
//							speed: 1500,
//							offset: $topheader.outerHeight() - 1
//						});
//
//					// Re-enable animations/transitions.
//						setTimeout(function() {
//							$body.removeClass('is-resizing');
//							$window.trigger('scroll');
//						}, 0);
//
//				}, 100);
//
//			})
//			.on('load', function() {
//				$window.trigger('resize');
//			});
    
       // 画面いっぱいに画像を表示する
    // 対象の画像
    var bgImg = $('#header');
    // 画像の縦横サイズ
    var bgWidth = 1474;
    var bgHeight = 984;
 
    // 画像のサイズ調整
    function adjust() {
        // 画面サイズの取得
        var winWidth = $window.width();
        var winHeight = $window.height();
 
        // 画像幅を仮で画面幅にする
        var imgWidth = winWidth;
        // 画面幅と画像比率に合わせた画像高さを取得
        var imgHeight = Math.floor(bgHeight * (winWidth / bgWidth));
        // topheaderの高さ分ずらす
        imgHeight -= headerHeight;
        // 画面高さと画像高さから、画像が上下中央にくるtopの位置を取得
//        var imgTop = 0;
//        // 画面幅 = 画像幅なので、leftは0
//        var imgLeft = 0;
// 
//        // 画像高さが画面高さより大きい時
//        if(imgHeight >= winHeight) {
//        // 画像高さが画面高さ未満の時
//        } else {
//            // 画像高さを画面高さにする
//            imgHeight = winHeight;
//            // 画面高さと画像比率に合わせた画像幅を取得
//            imgWidth = Math.floor(bgWidth * (winHeight / bgHeight));
//            // 画面高さ = 画像高さなので、topは0
//            imgTop = 0;
//            // 画面幅と画像幅から、画像が左右中央にくるleftの位置を取得
//            imgLeft = (winWidth - imgWidth) / 2;
//        }
// 
        // 画像のサイズと位置の指定
        bgImg.css({
            height: imgHeight
        })
    }
    adjust();
 
    // ページ読み込み時、リサイズ時に画像調整を実行
    $(window).on('load resize', function() {
        adjust();
    });
})(jQuery);