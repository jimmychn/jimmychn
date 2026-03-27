<?php 
require_once('./Vendor/smarty/smarty/libs/Smarty.class.php'); 
require_once('./Vendor/SunDB.php');
require_once('./config.php');

$db = new SunDB($cnDB); 

$smarty = new Smarty\Smarty;

//網站目錄
$smarty->assign("baseURL",$baseURL);

//讀取公司資料表
$company=$db->select('company',['com_key','com_descript','marqueeText','marqueeText2'])->run();
//print_r($company); //for TEST

$smarty->assign("CompanyName",$CompanyName);
$smarty->assign("CompanyMail",$CompanyMail);
$smarty->assign("CompanyTalkFree",$CompanyTalkFree);
$smarty->assign("ComplayUrl",$ComplayUrl);
$smarty->assign("CompanyHrUrl",$CompanyHrUrl?:"");
$smarty->assign("CompanyLineUrl",$CompanyLineUrl?:"");
$smarty->assign("CompanyFbUrl",$CompanyFbUrl?:"");
$smarty->assign("CompanyIgUrl",$CompanyIgUrl?:"");

$smarty->assign("pageTitle","大陸眼鏡-首頁");
$smarty->assign("PageDescription",$company[0]['com_key']);
$smarty->assign("PageKeywords",$company[0]['com_descript']);
$smarty->assign("PageAuthor","JimmyChen");

$smarty->assign("MenuActive","Home");

//取得符合日期和首頁條件的首頁BANNER
$banners=$db->select('banner',['subject','note','pic','url'])
			->where("(`kind`=1 AND `view`='Y') AND ((`selltime1`>=CURDATE() AND `selltime2`<=CURDATE()) OR isnull(`selltime2`))")
			->run();
//增加首頁BANNER清單
$banners=array_merge($banners,[ ['subject'=>'','note'=>'','pic'=>'banner_20210310085724_0.jpg','url'=>''],
								['subject'=>'','note'=>'','pic'=>'banner_20210310085614_0.jpg','url'=>''],
								['subject'=>'','note'=>'','pic'=>'banner_20210310085746_0.jpg','url'=>''],
								['subject'=>'','note'=>'','pic'=>'banner_20210310085702_0.jpg','url'=>'']
							  ]);
//print_r($banners); //for TEST
//設定SMARTY的首頁BANNERS變數
$smarty->assign("carousels",$banners);	

//取得符合日期和首頁條件的首頁NEWS
$infos=$db->select('news',['num','subject','pic1','marqueeText'])
			->where("(`kind`=1 AND (isnull(shops) OR shops='') ) AND ((`selltime1`>=CURDATE() AND `selltime2`<=CURDATE()) OR isnull(`selltime2`))")
			->run();
//print_r($infos); //for TEST

//首頁文字跑馬燈處理：先加入公司預設的跑馬燈字串
$marquees=array(['text'=>$company[0]['marqueeText'],'url'=>''],
				['text'=>$company[0]['marqueeText2'],'url'=>'']);

//首頁文字跑馬燈處理：再加入BANNER的跑馬燈字串，注意ARRAY括號的數量！！
foreach ($banners as $banner) {
	if ($banner['note']!='' && !in_array(['text'=>$banner['note'],'url'=>''],$marquees)) { //不加入重複內容
	  $marquees=array_merge($marquees,[['text'=>$banner['note'],'url'=>$banner['url']]]); 
	}
}
//首頁文字跑馬燈處理：再加入NEWS的跑馬燈字串，注意ARRAY括號的數量！！
foreach ($infos as $info) {
	if ($info['marqueeText']!='' && !in_array(['text'=>$info['marqueeText'],'url'=>''],$marquees)) { //不加入重複內容
	  $marquees=array_merge($marquees,[['text'=>$info['marqueeText'],'url'=>'infomation.php?num=$info.num']]); 
	}
}
//print_r($marquees);  //for TEST
//設定SMARTY的首頁文字跑馬燈marquee變數
$smarty->assign("marquees",$marquees);
$smarty->assign("infos",$infos);


$files = ["_header.tpl",
	"_menu.tpl",
	"_carousel.tpl",
	"_marqueetext.tpl",
	"__about.tpl",
	"__slogan2.tpl",
	"__information.tpl",
	//"_blog_news.tpl",
	//"__product.tpl",
	//"__store.tpl",
	//"__facebook.tpl",
	//"_service.tpl",
	//"_price.tpl",
	//"_offer.tpl",
	//"_team.tpl",
	//"_testimonial.tpl",
	//"_blog.tpl",
	//"_blog_detail.tpl",
	//"_contact.tpl",
	"_footer.tpl"];


foreach ($files as $file){
    $smarty->display($file);
}
?>
