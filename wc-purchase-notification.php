<?php
/*---------------------------------------------------------
Plugin Name: WC Purchase Notification 
Author: carlosramosweb
Author URI: http://www.criacaocriativa.com/plugins/
Donate link: http://www.criacaocriativa.com/
Description: Plugin que notifica nova compra no WooCommerce tocando um audio de alerta.
Text Domain: wc-purchase-notification
Domain Path: /languages/
Version: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html 
------------------------------------------------------------*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'wpn_plugin_action_links_settings' );		
function wpn_plugin_action_links_settings( $links ) {
	$action_links = array(
		'settings' => '<a href="' . esc_url(admin_url( 'admin.php?page=wc-purchase-notification' )) . '" title="'.__( 'Configurações', 'wc-purchase-notification' ).'" class="error">'.__( 'Configurações', 'wc-purchase-notification' ).'</a>',
		'donate' => '<a href="' . esc_url("http://donate.criacaocriativa.com") . '" title="'.__( 'Doação', 'wc-purchase-notification' ).'" class="error">'.__( 'Doação', 'wc-purchase-notification' ).'</a>',
	);

	return array_merge( $action_links, $links );
}

add_action('switch_theme', 'wc_purchase_notification_theme');
function wc_purchase_notification_theme() {
	update_option( 'wc_purchase_notification_enabled', 'yes' );
	update_option( 'wc_purchase_notification_audio_loop', '1' );
	update_option( 'wc_purchase_notification_audio_custom', '' );
}

add_action( 'admin_menu', 'register_wc_purchase_notification_submenu_page', 70 );
function register_wc_purchase_notification_submenu_page() {
    add_submenu_page( 'woocommerce', 'Purchase Notification', 'Purchase Notification', 'manage_woocommerce', 'wc-purchase-notification', 'wc_purchase_notification_callback' ); 
}

@include_once("wc-purchase-notification-verify.php");

function wc_purchase_notification_callback() {
	 
	 if( isset( $_POST['_update'] ) && isset( $_POST['_wpnonce'] ) && isset( $_POST['update_all'] ) ) {
		$_update = sanitize_text_field( $_POST['_update'] );
		$_wpnonce = sanitize_text_field( $_POST['_wpnonce'] );
	 }
	 
	 if( isset( $_POST['_update'] ) && isset( $_POST['_wpnonce'] ) && isset( $_POST['delete_audio_custom'] ) ) {
		$_update = sanitize_text_field( $_POST['_update'] );
		$_wpnonce = sanitize_text_field( $_POST['_wpnonce'] );
	 }
	
	$message = "";
	if( isset( $_wpnonce ) && isset( $_update )) {		
		if ( ! wp_verify_nonce( $_wpnonce, "wc-purchase-notification-update" ) ) {
			$message = "error";
			
		} else if ( empty( $_update ) ) {
			$message = "error";
			
		} else if ( isset( $_POST['delete_audio_custom'] ) ) {
			$name_plugin = basename( dirname(__FILE__) );
			$name_path = dirname( dirname(__FILE__) );			
			$plugin_path = $name_path. '/' . $name_plugin . '/assets/audio/';
			
			if( isset( $_POST['wc_purchase_notification_audio_custom_'] ) ) {
				$wc_purchase_notification_audio_custom_ =  sanitize_text_field( $_POST['wc_purchase_notification_audio_custom_'] );
				if( unlink( $plugin_path . $wc_purchase_notification_audio_custom_ ) ) {
					update_option( 'wc_purchase_notification_audio_custom', '' );									
					$message = "updated";
				}
			}
				
		} else {
			$wc_purchase_notification_enabled = sanitize_text_field( $_POST['wc_purchase_notification_enabled'] );	
			$wc_purchase_notification_audio_loop = sanitize_text_field( $_POST['wc_purchase_notification_audio_loop'] );
			
			update_option( 'wc_purchase_notification_enabled', $wc_purchase_notification_enabled );
			update_option( 'wc_purchase_notification_audio_loop', $wc_purchase_notification_audio_loop );		
						
			if( isset( $_FILES['wc_purchase_notification_audio_custom'] ) ) {
				$wc_purchase_notification_audio_custom = sanitize_text_field( $_FILES['wc_purchase_notification_audio_custom'] );	
				$wc_purchase_notification_audio_custom_name = sanitize_text_field( $_FILES['wc_purchase_notification_audio_custom']['name'] );
				$wc_purchase_notification_audio_custom_tmp = sanitize_text_field( $_FILES['wc_purchase_notification_audio_custom']["tmp_name"] );
						
				$file_type =  substr( $wc_purchase_notification_audio_custom_name, -4, 4 );	
				if( $file_type == ".mp3" or $file_type == ".wav" ) {				
					
					$name_plugin = basename(dirname(__FILE__));
					$name_path = dirname(dirname(__FILE__));
					
					$plugin_path = $name_path. '/' . $name_plugin . '/assets/audio/';
						
					$file_wc_purchase_notification_audio_custom = md5( $wc_purchase_notification_audio_custom_name ) . $file_type;														
					if( move_uploaded_file( $wc_purchase_notification_audio_custom_tmp, $plugin_path . $file_wc_purchase_notification_audio_custom ) ) { 
						update_option( 'wc_purchase_notification_audio_custom', $file_wc_purchase_notification_audio_custom );										
						$message = "updated";
					}
				}
				$message = "error-type";
			}
			$message = "updated";
		}
	}

	$wc_purchase_notification_enabled = esc_attr( get_option( 'wc_purchase_notification_enabled' ) );
	$wc_purchase_notification_audio_loop = esc_attr( get_option( 'wc_purchase_notification_audio_loop' ) );
	$wc_purchase_notification_audio_custom = esc_attr( get_option( 'wc_purchase_notification_audio_custom' ) );
	
	if( !empty( $wc_purchase_notification_audio_custom ) ) {
		$audio = esc_url( plugins_url( 'assets/audio/' . $wc_purchase_notification_audio_custom, __FILE__ ) );
	} else {
		$audio = esc_url( plugins_url( 'assets/audio/6687cd1aeb8ba2285d01c95d14a4c43c.mp3', __FILE__ ) );
	} 

?>
<!----->
<div id="wpwrap">
<!--start-->
    <h1><?php echo __( 'WC Purchase Notification', 'wc-purchase-notification' ); ?></h1>
    <p><?php echo __( 'Sempre que tiver um novo pedido feito o sistema irá tocar um áudio de alerta.', 'wc-purchase-notification' ); ?><br/>
    <?php echo __( 'Mas você precisa está página aberta e com o botão <strong>Start Purchase Notification</strong> clicado.', 'wc-purchase-notification' ) ; ?></p>
    
    <?php if( isset( $message ) ) { ?>
        <div class="wrap">
    	<?php if( $message == "updated" ) { ?>
            <div id="message" class="updated notice is-dismissible" style="margin-left: 0px;">
                <p><?php echo __( 'Atualizações feita com sucesso!', 'wc-purchase-notification' ) ; ?></p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">
                        <?php echo __( 'Dispensar este aviso.', 'wc-purchase-notification' ) ; ?>
                    </span>
                </button>
            </div>
            <?php } ?>
            <?php if( $message == "error" ) { ?>
            <div id="message" class="updated error is-dismissible" style="margin-left: 0px;">
                <p><?php echo __( 'Erro! Não conseguimos fazer as atualizações!', 'wc-purchase-notification' ) ; ?></p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">
                        <?php echo __( 'Dispensar este aviso.', 'wc-purchase-notification' ) ; ?>
                    </span>
                </button>
            </div>
        <?php } ?>
    	</div>
    <?php } ?>
    <!----->
    <?php if( $wc_purchase_notification_enabled == "yes" ) { ?>
    <div class="wrap">
        <div>
            <hr/>
            <div class="msn-loading" style="display:none;">
                <img src="<?php echo esc_url( plugins_url( 'assets/images/loading35px.gif', __FILE__ ) ); ?>" alt="Loarding" style="display: inline-block;">
                <span style="display: inline-block; position: absolute; padding: 10px;">
                    <strong>Pesquisando por novos pedidos...</strong> 
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=shop_order' ) ); ?>" target="_blank">( Ver Pedidos )</a>
                </span>
                <span style="clear: both;"></span>
            </div>
            <a href="javascript:;" class="button button-start" onClick="startLoopAudioAdmin()">
            	<span class="dashicons dashicons-search" style="line-height: 1.4;"></span>
                 <?php echo __( 'Start Purchase Notification', 'wc-purchase-notification' ) ; ?>
            </a>
            <span style="clear: both;"></span>
            <hr/>
        </div>
        <div id="modal-window-id" style="display: none; border: 2px solid #900; padding: 10px; background:#eadede; ">
            <h3 style="margin:0; padding:0;">Clique no botão abaixo para parar o som.</h3>  
            <hr/>
            <a href="javascript:;" class="button button-primary" onClick="pauseAudioAdmin()">
                <span class="dashicons dashicons-controls-pause" style="line-height: 1.4;"></span> Pause
            </a>
        </div>
    </div>
    <?php } ?>
    <!---->
    <div class="wrap woocommerce">
    	<!---->
            <nav class="nav-tab-wrapper wc-nav-tab-wrapper">
            <?php
			if( isset( $_GET['tab'] ) ) {
				$tab = esc_attr( $_GET['tab'] );
			}
			?>
           		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-purchase-notification' ) ); ?>" class="nav-tab <?php if( $tab == "" ) { echo "nav-tab-active"; }; ?>">
					<?php echo __( 'Configurações', 'wc-purchase-notification' ) ; ?>
                </a>
            	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-purchase-notification&tab=wpn-doacao' ) ); ?>" class="nav-tab <?php if( $tab == "wpn-doacao") { echo "nav-tab-active"; }; ?>">
					<?php echo __( 'Doação', 'wc-purchase-notification' ) ; ?>
                </a>
            </nav>
            <!---->
            <?php if(!isset($tab)) { ?>
            <form method="POST" id="fileform" name="fileform" enctype="multipart/form-data">
            	<input type="hidden" name="delete_audio_custom" value="1">
                <input type="hidden" name="wc_purchase_notification_audio_custom_" value="<?php echo $wc_purchase_notification_audio_custom; ?>">
                <input type="hidden" name="_update" value="1">
                <input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'wc-purchase-notification-update' ) ); ?>">
            </form>
        	<form method="POST" id="mainform" name="mainform" enctype="multipart/form-data">
                <!---->
                <table class="form-table">
                    <tbody>
                        <!---->
                        <tr valign="top">
                            <th scope="row">
                                <label>
                                    <?php echo __( 'Habilita/Desabilita', 'wc-purchase-notification' ) ; ?>
                                </label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wc_purchase_notification_enabled" value="yes" <?php if( $wc_purchase_notification_enabled == "yes" ) { echo 'checked="checked"'; } ?>>
                                    <?php echo __( 'Ativar notificações', 'wc-purchase-notification' ) ; ?>
                                </label>
                           </td>
                        </tr>  
                        <!---->
                        <tr valign="top">
                            <th scope="row">
                                <label>
                                    <?php echo __( 'Tocar em loop infinito?', 'wc-purchase-notification' ) ; ?>
                                </label>
                            </th>
                            <td>
                                <label>
                                	<input name="wc_purchase_notification_audio_loop" type="radio" value="1" <?php if( $wc_purchase_notification_audio_loop == "1" ) { echo "checked"; } ?>>
                                    <?php echo __( '<strong>Sim!</strong>', 'wc-purchase-notification' ) ; ?>
                                    <?php echo __( '<i>( Você poderá parar o som com um botão que aparecerá )</i>', 'wc-purchase-notification' ) ; ?>
                                </label>
                                <hr/>
                                <label>
                                	<input name="wc_purchase_notification_audio_loop" type="radio" value="0" <?php if( $wc_purchase_notification_audio_loop == "0" ) { echo "checked"; } ?>>
                                    <?php echo __( 'Não!', 'wc-purchase-notification' ) ; ?>
                                    <?php echo __( '<i>( Tocar apenas uma vez )</i>', 'wc-purchase-notification' ) ; ?>
                                </label>
                            </td>
                        </tr>
                        <!---->
                        <tr valign="top">
                            <th scope="row">
                                <label>
                                    <?php echo __( 'Testar o áudio:', 'wc-purchase-notification' ) ; ?>
                                </label>
                            </th>
                            <td>
                                <?php if( !empty( $wc_purchase_notification_audio_custom ) ) { ?>
                                	<strong><?php echo __( 'Arquivo: ', 'wc-purchase-notification' ) ; ?></strong>
                                    <?php echo $wc_purchase_notification_audio_custom; ?>
                                    <a href="javascript:;" style="color:#900; text-decoration:none;" onClick="deleteAudioAdmin()">
                                        [ <?php echo __( 'Apagar', 'wc-purchase-notification' ) ; ?> ]
                                    </a>
                                	<hr/>
                                <?php } else { ?>
                                	<strong><?php echo __( 'Arquivo: ', 'wc-purchase-notification' ) ; ?></strong>
                                	<?php echo __( 'Áudio Padrão', 'wc-purchase-notification' ) ; ?>
                                	<hr/>
                                <?php } ?>
                                <a href="javascript:;" class="button" onClick="playAudio()">
                                    <span class="dashicons dashicons-controls-play" style="line-height: 1.4;"></span> Play
                                </a>
                                
                                <a href="javascript:;" class="button" onClick="pauseAudioAdmin()">
                                    <span class="dashicons dashicons-controls-pause" style="line-height: 1.4;"></span> Pause
                                </a>
                            </td>
                        </tr>
                        <!---->
                        <tr valign="top">
                            <th scope="row">
                                <label>
                                    <?php echo __( 'Usar outro áudio:', 'wc-purchase-notification' ) ; ?>
                                </label>
                            </th>
                            <td>
                                <input name="wc_purchase_notification_audio_custom" type="file" value=""><br/>
                                ( <i><?php echo __( 'MP3 ou WAV são os arquivos suportados pelo sistema.', 'wc-purchase-notification' ) ; ?></i> )
                            </td>
                        </tr>
                        <!---->
                    </tbody>
                </table>
                <!---->
                <hr/>
                <div class="submit">
                    <button class="button-primary" type="submit"><?php echo __( 'Salvar Alterações', 'wc-purchase-notification' ) ; ?></button>
                    <input type="hidden" name="update_all" value="1">
                    <input type="hidden" name="_update" value="1">
                    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'wc-purchase-notification-update' ) ); ?>">
                    <!---->
                    <span>
                    	<span aria-hidden="true" class="dashicons dashicons-warning" style="vertical-align: middle;"></span>
    					<?php echo __( 'Não esqueça de <strong>salvar suas alterações</strong>.', 'wc-purchase-notification' ) ; ?>
                    </span>
                </div>
                <!---->
        	</form>
        <?php } else if($tab == "wpn-doacao") { ?>
            <h2><?php echo __( 'Oba! Fique a vontade.', 'wc-purchase-notification' ) ; ?></h2>
        	<div class="">
            	<p><?php echo __( '<strong>É totalmente seguro!</strong> Ajude a manter esse plugin sempre atualizado com seu incentivo.', 'wc-purchase-notification' ) ; ?></p>
            </div>
			<!---->
            <table class="form-table">
                <tbody>
                    <!---->
                    <tr valign="top">
                        <th scope="row">
                            <button class="button-primary" onClick="window.open('http://donate.criacaocriativa.com')">
                            <?php echo __( 'Quero doar agora', 'wc-purchase-notification' ) ; ?>
                            </button>
                        </th>
                        <td>
                            <label>
							<span>
								<span class="dashicons dashicons-warning" style="vertical-align: middle;"></span>
								<?php echo __( 'Você será direcionado para um site seguro.', 'wc-purchase-notification' ) ; ?> 
							</span> 
                            </label>
                        </td>
                    </tr>
                    <!---->
                </tbody>
            </table>
            <!---->
        <?php } ?>
        <!---->
    </div>
<!--enf wpwrap-->
</div>
<script>	

	var audio_admin = new Audio('<?php echo $audio; ?>');
	
    function deleteAudioAdmin() {	
        var response = confirm("Tem certeza que deseja apagar?");
        if (response == true) {
            document.getElementById("fileform").submit();
        } else {
            return;
        }
    }
	
	function pauseAudioAdmin() {
		audio_admin.pause();
		jQuery("#modal-window-id").hide();
	}
	
	function playAudio() {	
		<?php if( $wc_purchase_notification_audio_loop == "0" ) { ?>
            audio_admin.loop = false;
            audio_admin.play();
		<?php } else { ?>
			audio_admin.loop = true;
			audio_admin.play();
		<?php } ?>
	}
	
	function playAudioAdmin() {				
		var form_data = new FormData();	
		form_data.append('action', 'wc_purchase_notification_verify');
		
		jQuery.ajax({
			type: 'POST',
			url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
			contentType: false,
			processData: false,
			data: form_data,
			success:function(response) {
				if(response == 'success') {
				<?php if( $wc_purchase_notification_audio_loop == "0" ) { ?>
                    audio_admin.loop = false;
                    audio_admin.play();
				<?php } else { ?>
					audio_admin.loop = true;
					audio_admin.play();
					jQuery("#modal-window-id").show();
				<?php } ?>
				}
			}
		});
	};
    
	function startLoopAudioAdmin() {
	    jQuery(".button-start").hide();
        jQuery(".msn-loading").show();
        
	    playAudioAdmin();
        var intervalId = setInterval(function(){
           var timoutId = setTimeout(function(){ 
                playAudioAdmin();
           }, 10000);
        }, 10000);
	};
		
</script> 
    <?php
}


