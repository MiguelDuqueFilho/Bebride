/** 
 * javascript de notificação
 * */
/**
 * Scripts customizados para a BeBride parte Admin
 */

    // deixar menu ativo dinamicamente  (experimental)
    // $(".sidebar-wrapper .nav .nav-item").click(function(){
    //     $(".sidebar-wrapper .nav .nav-item").addClass('active');
    // });    
    jQuery(document).ready(function($) {
      
mdm = {
    misc: {
      navbar_menu_visible: 0,
      active_collapse: true,
      disabled_collapse_init: 0,
    },
  
    checkSidebarImage: function() {
      $sidebar = $('.sidebar');
      image_src = $sidebar.data('image');
  
      if (image_src !== undefined) {
        sidebar_container = '<div class="sidebar-background" style="background-image: url(' + image_src + ') "/>';
        $sidebar.append(sidebar_container);
      }
    },
  
    showNotification: function(from, align) {
      type = ['', 'info', 'danger', 'success', 'warning', 'rose', 'primary'];
  
      color = Math.floor((Math.random() * 6) + 1);
  
      $.notify({
        icon: "add_alert",
        message: "teste de mensagem."
  
      }, {
        type: type[color],
        timer: 3000,
        placement: {
          from: from,
          align: align
        }
      });
    }
}

});