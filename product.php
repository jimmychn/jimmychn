<?php 
require_once('./Vendor/smarty/smarty/libs/Smarty.class.php'); 
require_once('./Vendor/SunDB.php');
require_once('./config.php');

$db = new SunDB($cnDB); 

$smarty = new Smarty\Smarty;

$rows_com=$db->select('company',['com_key','com_descript','marqueeText','marqueeText2'])->run();
//print_r($rows_com); //for TEST

$smarty->assign("CompanyName",$CompanyName);
$smarty->assign("CompanyMail",$CompanyMail);
$smarty->assign("CompanyTalkFree",$CompanyTalkFree);
$smarty->assign("ComplayUrl",$ComplayUrl);
$smarty->assign("CompanyHrUrl",$CompanyHrUrl??"");
$smarty->assign("CompanyLineUrl",$CompanyLineUrl??"");
$smarty->assign("CompanyFbUrl",$CompanyFbUrl??"");
$smarty->assign("CompanyIgUrl",$CompanyIgUrl??"");


$smarty->assign("pageTitle","大陸眼鏡-商品");
$smarty->assign("PageDescription",$rows_com[0]['com_key']);
$smarty->assign("PageKeywords",$rows_com[0]['com_descript']);
$smarty->assign("PageAuthor","JimmyChen");

$smarty->assign("menuActive","Product");


$files = [
	"_header.tpl",
	"_menu.tpl",
	"_hero.tpl",
	"_team.tpl",
	"_footer.tpl"];

foreach ($files as $file){
    $smarty->display($file);
}
?>