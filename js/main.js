(function ($) {
    //"use strict";
    // Initiate the wowjs
    new WOW().init();

    // Sticky Navbar
    $(window).scroll(function () {
		var scrollTop=$(this).scrollTop();
        if (scrollTop > 40) {
            $('.navbar').addClass('sticky-top');
        } else {
            $('.navbar').removeClass('sticky-top');
        }
		
    });
    
    // Dropdown on mouse hover
    $(document).ready(function () {

		function toggleNavbarMethod() {
            if ($(window).width() > 992) {
                $('.navbar .dropdown').on('mouseover', function () {
                    $('.dropdown-toggle', this).trigger('click');
                }).on('mouseout', function () {
                    $('.dropdown-toggle', this).trigger('click').blur();
                });
            } else {
                $('.navbar .dropdown').off('mouseover').off('mouseout');
            }
        }
        toggleNavbarMethod();
        $(window).resize(toggleNavbarMethod);
    });
    
    
    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });

})(jQuery);

document.addEventListener("DOMContentLoaded", function () {
    const carousel = document.querySelector(".carouselContainer");
    let scrollPosition = document.querySelector(".item").offsetWidth + 20; // 初始偏移量
    //let scrollPosition = 0; // 初始偏移量

    carousel.style.transform = `translateX(-${scrollPosition}px)`; // 確保第一張圖片對齊
});

document.addEventListener("DOMContentLoaded", function () {
    const carousel_1 = document.querySelector(".carouselContainer");
    const prevBtn = document.querySelector("#prevBtn");
    const nextBtn = document.querySelector("#nextBtn");
    const itemWidth = document.querySelector(".item").offsetWidth + 20; // 圖片寬度加間距
    let scrollPosition = 0;

    nextBtn.addEventListener("click", function () {
        const maxScroll = carousel_1.scrollWidth - itemWidth * 3; // 最大滾動範圍
        if (scrollPosition < maxScroll) {
            scrollPosition += itemWidth;
        } else {
            scrollPosition = maxScroll; // 防止滾動超過範圍
        }
        carousel_1.style.transform = `translateX(-${scrollPosition}px)`;
    });

    prevBtn.addEventListener("click", function () {
        if (scrollPosition > 0) {
            scrollPosition -= itemWidth;
        } else {
            scrollPosition = 0; // 確保能夠滾回第一張
        }
        carousel_1.style.transform = `translateX(-${scrollPosition}px)`;
    });
});

function ajaxKind(nation,defaultValue){				
	$.ajax({
		type: "POST",
		url: "../../module/kind.php",
		cache: false,
		data: { 
				ojbID: "kind",        		//下拉控制項的id
				table: "news_kind",   		//資料表名稱
				pleaseSelect: true,  		//是否需要有「請選擇」
				text: "shops",		  		//門市專屬的text欄位要顯示的資料
				text: "kind",		  		//option的text欄位要顯示的資料
				value: "num",		  		//option的value欄位要顯示的資料
				change: "",			  		//onchange時要執行的js
				where: "nation='"+nation+"'",	//加入where條件式查詢
				range: "",			 		//指定排序方式
				defValue: defaultValue		//預設要被選取的value值
			  }
	}).done(function( htmlData ) {  			
		$('#kindDiv').html(htmlData);
	});
}	

function validate() {
   if ($('#nation').val()==""){
		alert("請選擇語系！");
		$('#nation').focus();
		return false;
	}
	
	if ($('#kind').val()==""){
		alert("請選擇分類！");
		$('#kind').focus();
		return false;
	}			
			   
	if ($('#subject').val()==""){
		alert("請輸入標題！");
		$('#subject').focus();
		return false;
	}	
			
	if ($('#selltime1').val()!="" && $('#selltime2').val()!=""){
		if ( Date.parse($('#selltime2').val()) < Date.parse($('#selltime1').val())){
			alert("起迄日期錯誤！");
			$('#selltime1').focus();
			return false;
		}
	}
				

	return true;
}
