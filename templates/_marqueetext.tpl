    <!--start code-->
	<!-- 使用SMARTY變數：$marquees[[text,url],[text,url]...] -->
    <div class="row vh-1 p-1 m-0 text-light bg-dark">
		<div class="col-1"></div>
        <div class="col-10 ">
			<marquee behavior="scroll" direction="up" height="30em" scrolldelay="400" onmouseover="this.stop();" onmouseleave="this.start();">
				{foreach $marquees as $marquee}
					<a class="h4 font-weight-light text-light" href="{if $marquee.url neq ""}{$baseURL}/{$marquee.url}{/if}">{$marquee.text}</a><br />
				{/foreach}
			</marquee>
        </div>
    </div>
	<!--end code-->