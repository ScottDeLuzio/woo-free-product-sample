
<select class="<?php echo esc_attr( $value['class'] ); ?>" id="<?php echo $value['name']; ?>" name="<?php echo $this->_optionName."[".$value['name']."]"; ?>" <?php if( isset($value['is_pro']) && $value['is_pro'] == true && !\Woo_Free_Product_Sample_Helper::is_pro()) { ?>disabled<?php } ?>>
    <?php 
        foreach( $value['default'] as $key => $val ) :
            $selected = '';
            if( isset( $setting_options[ $value['name'] ] ) ) :
                if( $key == $setting_options[ $value['name'] ] ) :
                    $selected ='selected';
                endif;
            endif;             
    ?>
    <option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $val; ?></option>
    <?php 
        endforeach;
    ?>
</select>