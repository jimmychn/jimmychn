<?php
// footer.php
?>
    </div>
  </div>
</div>
<footer class="bg-light text-center py-3" style="position: fixed; bottom: 0; left: 0; width: 100%; z-index: 100;">
    <small>&copy; <?php echo date("Y"); ?> 眼鏡連鎖店POS管理系統</small>
</footer>
<script>
$(function(){
  $('#mobileSysMenu').on('show.bs.collapse', function () {
    $(this).prev('a').find('i.fas.fa-caret-down')
      .removeClass('fa-caret-down').addClass('fa-caret-up');
  });
  $('#mobileSysMenu').on('hide.bs.collapse', function () {
    $(this).prev('a').find('i.fas.fa-caret-up')
      .removeClass('fa-caret-up').addClass('fa-caret-down');
  });

  $('#desktopSysMenu').on('show.bs.collapse', function () {
    $(this).prev('a').find('i.fas.fa-caret-down')
      .removeClass('fa-caret-down').addClass('fa-caret-up');
  });
  $('#desktopSysMenu').on('hide.bs.collapse', function () {
    $(this).prev('a').find('i.fas.fa-caret-up')
      .removeClass('fa-caret-up').addClass('fa-caret-down');
  });
});
</script>
</body>
</html>
