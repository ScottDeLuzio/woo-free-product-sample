
<select class="<?php echo esc_attr( $value['class'] ); ?>" id="<?php echo $value['name']; ?>" name="<?php echo $this->_optionName."[".$value['name']."]"; ?>[]" <?php if( isset($value['is_pro']) && $value['is_pro'] == true && !\Woo_Free_Product_Sample_Helper::is_pro()) { ?>disabled<?php } ?> multiple="multiple">
    <?php
    $settings = \Woo_Free_Product_Sample_Helper::wfps_settings();
    $all_selected = '';
    if ( '0' == $settings['enable_product'][0] ) {
        $all_selected = 'selected';
    }
    ?>
    <option value="" <?php echo $all_selected; ?>><?php esc_html_e( 'All', 'woo-free-product-sample' ); ?></option>
    <?php

        foreach( $value['default'] as $val ) :

            $option_value = $option_title = '';
            if( is_object( $val ) ) {
                $option_value = $val->ID;
                $option_title = $val->post_title;
            } else {
                $option_value = $val['ID'];
                $option_title = $val['post_title'];
            }

            $selected = '';
            if( isset( $setting_options[ $value['name'] ] ) ) :
                if( in_array( $option_value, $setting_options[ $value['name'] ] ) ) :
                    $selected = 'selected';
                endif;
            endif;
    ?>
    <option value="<?php echo $option_value; ?>" <?php echo $selected; ?>><?php echo $option_title; ?></option>
    <?php
        endforeach;
    ?>
</select>