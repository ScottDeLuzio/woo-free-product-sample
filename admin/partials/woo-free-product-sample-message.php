<div class="wrap wfps_settings">
    <div id="icon-options-general" class="icon32"></div>
    <h1><?php echo WFPS_PLUGIN_NAME; ?></h1>
    <?php settings_errors(); ?>
    <div id="poststuff">
        <div class="metabox-holder columns-2">
            <!-- main content -->
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable" style="position:relative">
                    <div class="wfps_settings_outer_left">
                        <div class="postbox">
                            <div class="wfps_inside">
								<form method="post" action="options.php" novalidate="novalidate">
									<?php
										settings_fields( $this->_optionGroupMessage );
                                        $setting_options = wp_parse_args( get_option($this->_optionNameMessage), $this->_defaultMessageOptions );
									?>
									<table class="form-table">
										<tbody>
											<tr>
												<th scope="row">
													<label for="">
                                                        <?php echo __( 'Maximum quantity message', 'woo-free-product-sample' ); ?>
													</label>
												</th>
												<td>
                                                    <input type="text" name ="<?php echo $this->_optionNameMessage."[qty_validation]"; ?>" value ="<?php echo isset( $setting_options['qty_validation'] ) ? $setting_options['qty_validation'] : ''; ?>">
                                                    <div class="woo-free-product-sample-form-desc">
                                                        <?php echo __( '{product} and {qty} for dynamic content.', 'woo-free-product-sample' ); ?>
                                                    </div>
                                                </td>
											</tr>						
																			
											<?php do_settings_fields( $this->_optionGroupMessage, 'default' ); ?>
										</tbody>
									</table>    
									<?php do_settings_sections($this->_optionGroupMessage, 'default'); ?>
									<?php submit_button(); ?>
								</form>
                            </div>
                        </div>
                    </div>
                    <div class="sidebar"></div>
                </div>
            </div>


        </div>
        <div id="post-body" class="metabox-holder columns-2"></div>

    </div>
    <!-- #poststuff -->

</div> <!-- .wrap -->