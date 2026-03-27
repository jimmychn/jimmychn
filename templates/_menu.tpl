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
                <a id="about" 		href="about.php" class="nav-item nav-link {if $menuActive eq "About"}active{/if}"></a>
                <a id="infomation" 	href="infomation.php" class="nav-item nav-link {if $menuActive eq "Infomation"}active{/if}"></a>
                <a id="product" 	href="product.php" class="nav-item nav-link {if $menuActive eq "Product"}active{/if}"></a>
                <a id="knowledge" 	href="knowledge.php" class="nav-item nav-link {if $menuActive eq "Knowledge"}active{/if}"></a>
                <a id="store" 		href="store.php" class="nav-item nav-link {if $menuActive eq "Store"}active{/if}"></a>
                <a id="joinus" 		href="{$CompanyHrUrl}" class="nav-item nav-link {if $menuActive eq "joinus"}active{/if}" target="_blank"></a>
				{if $CompanyLineUrl neq ""}
					<a id="Line" 	href="{$CompanyLineUrl}" class="nav-item nav-link nav-contact" target="_blank">
						<img src="./img/LINE-square.png" width="80" height="auto" /></a>
				{/if}		
				{if $CompanyFbUrl neq ""}
					<a id="facebook" 	href="{$CompanyFbUrl}" class="nav-item nav-link nav-contact" target="_blank">
						<img src="./img/facebook.png" width="80" height="auto" /></a>
				{/if}
				{if $CompanyIgUrl neq ""}
					<a id="instgram" 	href="{$CompanyIgUrl}" class="nav-item nav-link nav-contact" target="_blank">
						<img src="./img/instagram_chatting.png" width="80" height="auto" /></a>
				{/if}	
            </div>
        </div>
    </nav>
    <!-- Navbar End -->
	
