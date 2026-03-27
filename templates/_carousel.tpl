    <!-- Carousel Start -->
	<!-- 使用SMARTY變數：$baseURL,$carousels[[subject,pic,url],[subject,pic,url]...] -->
<div class="container-fluid p-0 mb-0">
	<div id="carouselExampleDark" class="carousel carousel-dark slide" data-bs-ride="carousel" data-bs-interval="3000" data-bs-wrap="true">
	  <div class="carousel-indicators">
		{foreach $carousels as $carousel}
		<button type="button" data-bs-target="#carouselExampleDark" data-bs-slide-to="{$carousel@index}" {if $carousel@first} class="active" {/if} aria-current="true" aria-label="{$carousel@index}"></button>
		{/foreach}
	  </div>
	  <div class="carousel-inner">
		{foreach $carousels as $carousel}
			<div class="carousel-item {if $carousel@first} active {/if}" >
				{if $carousel.url!=""}<a href="{$baseURL}/{$carousel.url}" target="_blank">{/if}
					<img class="d-block w-100" src="{$baseURL}/upload/banner/{$carousel.pic}" alt="Image">
				{if $carousel.url!=""}</a>{/if}
			</div>
		{/foreach}
	  </div>
	  <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleDark" data-bs-slide="prev">
		<span class="carousel-control-prev-icon" aria-hidden="true"></span>
		<span class="visually-hidden">Previous</span>
	  </button>
	  <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleDark" data-bs-slide="next">
		<span class="carousel-control-next-icon" aria-hidden="true"></span>
		<span class="visually-hidden">Next</span>
	  </button>
	</div>
</div>	    
<!-- Carousel End -->
