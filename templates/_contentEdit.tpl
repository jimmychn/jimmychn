<!--日期元件-->
<link rel="stylesheet" href="../../module/jqdate/jquery.ui.all.css">	
<link rel="stylesheet" href="../../module/jqdate/demos.css">

<script src="../../module/jqdate/ui/jquery.ui.core.js"></script>
<script src="../../module/jqdate/ui/jquery.ui.widget.js"></script>
<script src="../../module/jqdate/ui/jquery.ui.datepicker.js"></script>
<script src="../../module/jqdate/jqdate.js"></script>
<!--日期元件-->

<!--編緝器元件-->
<script type="text/javascript" src="../ckeditor/ckeditor.js"></script>
<script type="text/javascript">
    CKEDITOR.config.toolbar = 'Default';                              
</script>
<!--編緝器元件-->

<!--分類模組，請勿於前台使用-->
<script type="text/javascript">	
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
</script>
<!--分類模組，請勿於前台使用-->

<script type="text/javascript">

function validate() 
		{
		   
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

</script>


<form action="<?php echo $_SERVER['REQUEST_URI']?>" enctype="multipart/form-data" id="form1" name="form1" method="post" onsubmit="return validate()">
<input name="todo" type="hidden" value="<?php echo $todo?>" />
<table align="center" border="1" cellpadding="2" cellspacing="0" width="100%" class="table1">
	<tr>
		<td class="td41" colspan="2" align="center">以下 * 欄位為必填欄位</td>
	</tr>
    <tr <?php echo (isMultiLanguage()?'':'style="display:none"')?>> 
    		   <td align="right" class="td1" width="100">* 語系：</td>           	
               <td align="left" class="td2">
               <?php
               if (isMultiLanguage()){
				   echo '<select name="nation" id="nation" onchange="ajaxKind(this.value,\'\')">>';		
				   echo '<option value="">請選擇</option>';		  
				   buildLanguage($nation);
				   echo '</select>';				   
			   }else{
				   echo '<input name="nation" id="nation" type="hidden" value="'.$nation.'" />';
			   }
			   ?>  
               </td>
    </tr> 
    <tr>
    	<td align="right" class="td1" width="100">* 分類：</td>
		<td align="left" class="td2">
		<div id="kindDiv"></div>
        <script type="text/javascript">
			$(window).ready(function() {	
				ajaxKind($('#nation').val(),'<?php echo $kind?>');
			});
		</script>
      </td>
	</tr>	
	<tr>
		<td align="right" class="td1" width="100">* 主題：</td>
		<td align="left" class="td2">
  			<input name="subject" type="text" id="subject" value="<?php echo $subject?>" maxlength="100" size="45" />
    	</td>
	</tr>
	<tr>
		<td align="right" class="td1" width="100">門市專屬：</td>
		<td align="left" class="td2">
  			<input name="shops"" type="text" id="shops" value="<?php echo $shops?>" maxlength="120" size="45" />
			  全部門市共用請清空欄位
    	</td>
	</tr>
	<tr>
		<td align="right" class="td1" width="100">簡述：</td>
		<td align="left" class="td2">
  			<textarea name="demo" id="demo" style="width:550px" rows="6"><?php echo $demo?></textarea>
        </td>
	</tr>  
    <tr>
		<td align="right" class="td1" nowrap="nowrap">
            開放時間：</td>
		<td align="left" class="td2">
起<input name="selltime1" type="text" id="selltime1" style="width:80px" class="jqdate" value="<?php echo $selltime1?>">            
訖<input name="selltime2" type="text" id="selltime2" style="width:80px" class="jqdate" value="<?php echo $selltime2?>">  


  	    </td>
	</tr>
    <tr>
		<td align="right" class="td1" nowrap="nowrap">* 發佈時間：</td>
		<td align="left" class="td2">       
  <input name="buildtime" type="text" id="buildtime" style="width:80px" class="jqdate" clear="false" value="<?php echo $buildtime?>">          
            ( 在前台顯示發佈日期 )            
              
        </td>
	</tr>
    <tr>
		<td align="right" class="td1" nowrap="nowrap">* 首頁顯示：</td>
		<td align="left" class="td2">       
  			        
        
                <label>
                  <input type="radio" name="show_index" value="Y" id="show_index_0" <?php if ($show_index=="Y" || $show_index==""){echo 'checked="checked"';}?> />
                  是</label>
               
                <label>
                  <input type="radio" name="show_index" value="N" id="show_index_1" <?php if ($show_index=="N"){echo 'checked="checked"';}?> />
                  否</label>
               
        </td>
	</tr>

    <tr>
		<td align="right" class="td1" nowrap="nowrap"> 跑馬燈顯示：</td>
		<td align="left" class="td2">       
  			 <input name="marqueeText" type="text" id="marqueeText" style="width:90%" class="" value="<?php echo $marqueeText;?>">       
        </td>
	</tr>
	<tr>
		<td align="right" class="td1" width="100">背景音樂：</td>
		<td align="left" class="td2">
        <font color="#990000">音樂檔</font>
		<input name="newsMusicCount" type="text" id="newsMusicCount" style="width:2em" class="" value="<?php echo $newsMusicCount;?>"><label for="newsMusicCount">播放次數</label>
<?php
for ($i=0;$i<$midMax;$i++) {
?>    
        <div style="margin:5px; float:left;">
 	    <input type="file" name="newsMusics[]"><br />
		<?php if (strlen($mid[$i])>0) { ?>
		  <input name="delMid<?php echo $i?>" id="delMid<?php echo $i?>" type="checkbox" value="Y" /><label for="delMid<?php echo $i?>">刪除檔案</label><?php echo $mid[$i];?><br />
		  <embed type="audio/mpeg" src="<?php echo $picDir.$mid[$i]; ?>" autoplay="false" autostart="0" loop="1" width="200" height="50" />
        <?php } ?>  
        </div>
<?php } ?>	

        </td>
	</tr>
	<tr>
		<td align="right" class="td1" nowrap="nowrap">* 電腦版內容：</td>
		<td align="left" class="td2">
<span style="color:#990000">內容輸入請斟酌尺寸，以免造成前台排版顯示錯誤</span>
<textarea class="ckeditor" cols="70" id="word" name="word" rows="10">
<?php echo htmlencode($word)?>
</textarea>
          </td>
	</tr>
	 
    <tr>
		<td align="right" class="td1" width="100">* 手機版內容：</td>
		<td align="left" class="td2">
 <span style="color:#990000">內容輸入請斟酌尺寸，以免造成前台排版顯示錯誤</span>
<textarea class="ckeditor" cols="70" id="word2" name="word2" rows="10">
<?php echo htmlencode($word2)?>
</textarea>
        </td>
	</tr>
<?php
for ($i=0;$i<$picMax;$i++){
	$picPath="../../module/smallimg2.php?path=../".str_replace("../","",$picDir).$pic[$i]."&w=170&h=170";
?>    
	<tr>
		<td align="right" class="td1" width="100">列表小圖：</td>
		<td align="left" class="td2">
          <input type="file" name="upload[]">
           <font color="#990000">僅可使用JPG圖檔(170*170)</font>
           
           <?php if ($pic[$i]!=""){?>
             <div style="margin:5px;"><input name="delpic<?php echo $i?>" id="delpic<?php echo $i?>" type="checkbox" value="Y" /><label for="delpic<?php echo $i?>">刪除照片</label><br/>
               <img src="<?php echo $picPath?>" /></div>
           <?php }?>
	   	   
        </td>
	</tr>
<?php
}
?>	
	<tr>
		<td align="right" class="td1" width="100">&nbsp;</td>
		<td align="left" class="td2">   
        <?php
        if ($todo=="add"){
		?>
		 <input id="ok" type="submit"  value="新增" />
		<?php
		}else{
		?>
		<input id="ok" type="submit"  value="修改" />
               <input id="goback" type="button"  value="返回" onclick="window.location='index.php?page=<?php echo @$_GET["page"].$att?>'" />
		<?php
		}
		?>
              
      </td>
	</tr>
</table>
</form>
