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
$smarty->assign("CompanyHrUrl",$CompanyHrUrl??"");
$smarty->assign("CompanyLineUrl",$CompanyLineUrl??"");
$smarty->assign("CompanyFbUrl",$CompanyFbUrl??"");
$smarty->assign("CompanyIgUrl",$CompanyIgUrl??"");

$smarty->assign("pageTitle","大陸眼鏡-後台編輯");
$smarty->assign("PageDescription",$company[0]['com_key']);
$smarty->assign("PageKeywords",$company[0]['com_descript']);
$smarty->assign("PageAuthor","JimmyChen");

$smarty->assign("MenuActive","Home");

//$att=getRequests("find");

//master.php相關設定
//$meta="<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\" >";  //設定IE的相容模式
//$find_tab="Y";   //顯示搜尋頁籤列，製作查詢列表頁時使用
//$body_onload="";  //在body的onload要觸發的js


//上傳檔案
if (@$_POST["todo"]!=""){
	//==多音樂檔處理========================	
	for ($i=0;$i<$midMax;$i++){
		echo $_FILES["newsMusics"]["name"][$i];
		if (@$_FILES["newsMusics"]["size"][$i]>0){ //檢查檔案大小是否大於0	
			//重新命名
			$subName=getSubName($_FILES["newsMusics"]["name"][$i]);
			$newName = "Music_".date("Ymdhis")."_".$i.$subName;
			//傳檔			
			copy($_FILES["newsMusics"]["tmp_name"][$i],$picDir.$newName);   //存檔案
			$mid[$i]=$newName;
		}else{
			$mid[$i]="";
		}
	}
	//處理圖片檔
	for ($i=0;$i<$picMax;$i++){
		if (@$_FILES["upload"]["size"][$i]>0){ //檢查檔案大小是否大於0	
			//重新命名
			$subName=getSubName($_FILES["upload"]["name"][$i]);		
			$newName = "news_".date("Ymdhis")."_".$i.$subName;
			//傳檔			
			if ($photoClass->isPhoto($newName)){
				$photoClass->save($_FILES["upload"]["tmp_name"][$i],$picDir.$newName,800); //存圖片，如果超過高寬超過800象素則縮圖			
			}else{
				copy($_FILES["upload"]["tmp_name"][$i],$picDir.$newName);   //存檔案
			}		
			$pic[$i]=$newName;	
		}else{
			$pic[$i]="";
		}
	}
}

//新增
if (@$_POST["todo"]=="add"){
	
	//圖片檔名欄位
	$picSql="";
	$midSql="";
	for ($i=0;$i<$picMax;$i++){
		$picSql.=",pic".($i+1);
	}
	//音樂檔名欄位
	for ($i=0;$i<$midMax;$i++){
		$midSql.=",mid".($i+1);
	}
	
	$colSql="nation,subject,shops,kind,demo,word,word2,selltime1,selltime2,buildtime,reg_time,show_index,marqueeText,newsMusicCount".$midSql.$picSql;
	
	$sqlStr="INSERT INTO news (".sqlInsertString($colSql,0).")";
	$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
	$parameter=array();
	$parameter[":nation"]=@$_POST["nation"];
	$parameter[":subject"]=@$_POST["subject"];
	$parameter[":shops"]=@$_POST["shops"];
	$parameter[":kind"]=@$_POST["kind"];
	$parameter[":demo"]=@$_POST["demo"];
	$parameter[":word"]=array(@$_POST["word"]);
	$parameter[":word2"]=array(@$_POST["word2"]);
	$parameter[":selltime1"]=(@$_POST["selltime1"]!=""?@$_POST["selltime1"]:NULL);
	$parameter[":selltime2"]=(@$_POST["selltime2"]!=""?@$_POST["selltime2"]:NULL);
	$parameter[":buildtime"]=(@$_POST["buildtime"]!=""?@$_POST["buildtime"]:NULL);
	$parameter[":reg_time"]=now();
	$parameter[":show_index"]=@$_POST["show_index"];
	$parameter[":marqueeText"]=@$_POST["marqueeText"];
	for ($i=0;$i<$midMax;$i++){
	  $parameter[":mid".($i+1)]=$mid[$i];
	}
    $parameter[":newsMusicCount"]=@$_POST["newsMusicCount"];
	for ($i=0;$i<$picMax;$i++){
		$parameter[":pic".($i+1)]=$pic[$i];
	}
	sqlExc($sqlStr,$parameter);
	scriptMsg("","index.php");
	exit;
	
}

//修改
if (@$_POST["todo"]=="edit" && @$_GET["num"]!=""){
	
	$sqlStr = "select * from news where num=:num";
	$parameter=array(':num'=>@$_GET["num"]);
	$row=sqlRow($sqlStr,$parameter);
	if($row!=NULL){
		
		$colSql="nation,subject,shops,kind,demo,word,word2,selltime1,selltime2,buildtime,show_index,marqueeText,newsMusicCount";
		
		$sqlStr="UPDATE news SET ".sqlUpdateString($colSql);
		$parameter=array();		
		$parameter[":nation"]=@$_POST["nation"];
		$parameter[":subject"]=@$_POST["subject"];
		$parameter[":shops"]=@$_POST["shops"];
		$parameter[":kind"]=@$_POST["kind"];
		$parameter[":demo"]=@$_POST["demo"];
		$parameter[":word"]=array(@$_POST["word"]);
		$parameter[":word2"]=array(@$_POST["word2"]);
		$parameter[":selltime1"]=(@$_POST["selltime1"]!=""?@$_POST["selltime1"]:NULL);
		$parameter[":selltime2"]=(@$_POST["selltime2"]!=""?@$_POST["selltime2"]:NULL);
		$parameter[":buildtime"]=(@$_POST["buildtime"]!=""?@$_POST["buildtime"]:NULL);	
		$parameter[":show_index"]=@$_POST["show_index"];
		$parameter[":marqueeText"]=@$_POST["marqueeText"];
		$parameter[":newsMusicCount"]=@$_POST["newsMusicCount"];
		for ($i=0;$i<$midMax;$i++){
			//有傳圖片或刪除圖片
			if (@$_POST["delMid".$i]=="Y" || $mid[$i]!=""){  
				$sqlStr.=",`mid".($i+1)."`=:mid".($i+1);
				$parameter[":mid".($i+1)]=$mid[$i];
				if ($row["mid".($i+1)]!=""){
					@unlink($picDir.$row["mid".($i+1)]); //執行刪除	
				}
			}		
		}	
		for ($i=0;$i<$picMax;$i++){
			//有傳圖片或刪除圖片
			if ($pic[$i]!="" || @$_POST["delpic".$i]=="Y"){  
				$sqlStr.=",`pic".($i+1)."`=:pic".($i+1);
				$parameter[":pic".($i+1)]=$pic[$i];
				if ($row["pic".($i+1)]!=""){
					@unlink($picDir.$row["pic".($i+1)]); //執行刪除	
				}				
			}		
		}	
		$sqlStr.=" WHERE `num`=:num";
		$parameter[":num"]=@$_GET["num"];
		sqlExc($sqlStr,$parameter);		
		
	}	
	
	
	scriptMsg("","index.php?page=".@$_GET["page"].$att);
	exit;
	
}

//載入資料
if (@$_GET["num"]!=""){	 
	$sqlStr = "select * from news where num=:num";
	$parameter=array(':num'=>@$_GET["num"]);
	$row=sqlRow($sqlStr,$parameter);
	if($row!=NULL){
		$todo="edit";
		$nation=$row["nation"];		
		$subject=$row["subject"];
		$shops=$row["shops"];
		$kind=$row["kind"];
		$demo=$row["demo"];
		$selltime1=$row["selltime1"];
		$selltime2=$row["selltime2"];
		$buildtime=$row["buildtime"];
		$word=$row["word"];
		$word2=$row["word2"];	
		$show_index=$row["show_index"];	
		$marqueeText=$row["marqueeText"];	
		$newsMusicCount=$row["newsMusicCount"];
		for ($i=0;$i<$midMax;$i++){
			$mid[$i]=$row["mid".($i+1)];
		}
		for ($i=0;$i<$picMax;$i++){
			$pic[$i]=$row["pic".($i+1)];
		}
		
	}else{
		scriptMsg("資料不存在","index.php");		
		exit;
	}
}else{  //初始值
	
	$todo="add";
	$nation=(isMultiLanguage()?'':defaultNation()); //如果無多語系則取得預設語
	$subject="";
	$shops="";
	$kind="";
	$demo="";
	$selltime1="";
	$selltime2="";
	$buildtime=date("Y-m-d",time()+$t_diff);
	$word="";
	$word2="";	
	$show_index="";		
	$marqueeText="";
	$newsMusicCount=1;
	for ($i=0;$i<$midMax;$i++){
		$mid[$i]="";
	}
	for ($i=0;$i<$picMax;$i++){
		$pic[$i]="";
	}
		
}


$files = ["_header.tpl",
	"_contentEdit.tpl",
	"_footer.tpl"];

foreach ($files as $file){
    $smarty->display($file);
}
?>

