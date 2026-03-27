<!DOCTYPE html>
<?php $CompanyName="大陸光學眼鏡鐘錶有限公司"; ?>
<?php $CompanyMail="cont.a00@msa.hinet.net"; ?>
<?php $CompanyTalkFree="+0800 033 322"; ?>

<?php $pageTitle="大陸光學眼鏡鐘錶有限公司"; ?>
<?php $metaDescription=""; ?>
<?php $metaKeywords=""; ?>
<?php $metaAuthor="JimmyChen"; ?>
<?php $menuActive="Home"; ?>

<?php require_once("_header.php"); ?>
<?php //require_once("_menu.php"); ?>

<?php
$SringMenu="about,,about.php,../img/menu.png,-38,-60,80,70;"
		."infomation,,infomation.php,../img/menu.png,-204,-60,80,70;"
		."product,,product.php,../img/menu.png,-356,-60,70,70;"
		."knowledge,,knowledge.php,../img/menu.png,-500,-60,82,80;"
		."store,,store.php,../img/menu.png,-636,-60,64,70;"
		."joinUs,,https://www.1111.com.tw/corp/1266072/,../img/menu.png,-746,-60,66,70;";
?>
	<!-- Navbar Start -->
    <nav class="navbar navbar-expand-lg bg-white-transparent navbar-light shadow-sm py-3 py-lg-0 px-3 px-lg-0">
        <a href="index.php" class="navbar-brand ms-lg-5">
			<img height="60pt" class="w-auto" src="img/cont.png" alt="Image">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto py-0">
				<?php
					foreach(explode(";",$SringMenu) as $item) {
						$arrItem=explode(",",$item);
						if (Count($arrItem)>=3) {
				?>
                <a id="<?php echo $arrItem[0]; ?>" href="<?php echo $arrItem[2]; ?>" class="nav-item nav-link <?php if($menuActive==$arrItem[0]) echo(" active"); ?>"></a>
				<?php		
						}
					}				
				?>
                <a id="freetalk" href="tel:<?php echo $CompanyTalkFree; ?>" class="nav-item nav-link nav-contact bg-secondary text-white px-5 ms-lg-5">
					<i class="bi bi-telephone-outbound  me-2"></i><?php echo $CompanyTalkFree; ?></a>
            </div>
        </div>
    </nav>
    <!-- Navbar End -->
<?php require_once("_footer.php"); ?>