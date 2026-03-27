<div class="container-fluid p-0 mb-0"> 
	<div style="height:700px;background-color:black;);">
		<div style="text-align: center;"><img src="img/infomation_w.png" /></div>
			<!--https://picsum.photos/100-->
			<div class="container">
				<div class="d-flex justify-content-center align-items-center">
					<button class="btn btn-dark mx-2" id="prevBtn">‹</button>
					<div class="carousel-wrapper">
						<div class="carouselContainer d-flex">
							{foreach $infos as $info}
								<div class="item text-center img_link">
									<a href="infomation.php?num={$info.num}" class="">
										<img src="{$baseURL}/vendor/smallimg3.php?path=../upload/news/{$info.pic1}&w=180&h=180" class="img-fluid" alt="{$info.subject}"><br />
										{$info.subject}				
									</a>
								</div>
							{/foreach}	
						</div>
					</div>
					<button class="btn btn-dark mx-2" id="nextBtn">›</button>
				</div>
			</div>
		<div><a href="./infomation.php" class="more">了解更多</a></div>
	</div>
</div>