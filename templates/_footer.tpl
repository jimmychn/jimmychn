    <!-- Footer Start -->
    <div class="container-fluid bg-dark text-light mt-5 py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container pt-5">
            <div class="row g-5">
                 <div class="col-lg-4 col-md-6">
                    <h3 class="text-white mb-4">Company Information</h3>
                    <p class="mb-2"><i class="bi bi-house text-primary me-2"></i>{$CompanyName}</p>
                    <p class="mb-2"><i class="bi bi-envelope-open text-primary me-2"></i>{$CompanyMail}</p>
                    <p class="mb-0"><i class="bi bi-telephone text-primary me-2"></i>{$CompanyTalkFree}</p>
                </div>
                 <div class="col-lg-4 col-md-6">
                    <h3 class="text-white mb-4">Copyright</h3>
                    <p class="mb-2">Copyright © 2022 cont. All Rights Reserved</p>
					<p class="mb-2">Designed by <a class="text-white border-bottom" href="https://htmlcodex.com">HTML Codex</a></p>
                    <p class="mb-0">Planner/Programer {$PageAuthor}</p>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h3 class="text-white mb-4">Follow Us</h3>
                    <div class="d-flex">
						{if $CompanyLineUrl neq ""}
							<a class="btn btn-lg btn-primary btn-lg-square rounded me-2" href="{$CompanyLineUrl}" alt="LINE" target="_blank"><i class="fab fa-line fw-normal"></i></a>
						{/if}	
						{if $CompanyFbUrl neq ""}
							<a class="btn btn-lg btn-primary btn-lg-square rounded me-2" href="{$CompanyFbUrl}" alt="Facebook" target="_blank"><i class="fab fa-facebook-f fw-normal"></i></a>
						{/if}	
						{if $CompanyIgUrl neq ""}
							<a class="btn btn-lg btn-primary btn-lg-square rounded me-2" href="{$CompanyIgUrl}" alt="Instagram" target="_blank"><i class="fab fa-instagram fw-normal"></i></a>
						{/if}	
						{if $CompanyHrUrl neq ""}
							<a class="btn btn-lg btn-primary rounded me-2" href="{$CompanyHrUrl}" alt="Join In" target="_blank"><img src="img/1111.png" alt="1111"></a>
						{/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->


    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-secondary btn-lg-square rounded back-to-top"><i class="bi bi-arrow-up"></i></a>


    <!-- JavaScript Libraries -->
	<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js" integrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous" async></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>