<div class="wfps_settings_header">
    <div class="wfps_header_left">
        <h1><span><?php echo esc_html( 'Free Product Sample' ); ?></span> <?php echo esc_html( 'for WooCommerce' ); ?></h1>
    </div>
    <div class="wfps_header_right">
        <div class="wfps_version"><?php echo esc_html( WFPS_PLUGIN_NAME ); ?>: <span class="wfps_version_blue"><?php echo esc_html( WFPS_VERSION); ?></span></div>
        <?php if( class_exists('Woo_Free_Product_Sample_Pro') ) { ?>
        <div class="wfps_version"><?php echo esc_html( WFPS_PRO_PLUGIN_NAME ); ?>: <span class="wfps_version_red"><?php echo esc_html( WFPS_PRO_VERSION); ?></span></div>
        <?php } ?>
    </div>
</div>