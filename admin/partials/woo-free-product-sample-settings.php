<?php 
	settings_errors(); 
	$setting_options = wp_parse_args( get_option($this->_optionName), $this->_defaultOptions );	
	
	if( ! isset($setting_options['builder_id']) ) {
        $current_tab = 'wfps_settings_tab';
    } else {
		$current_tab = $setting_options['builder_id'];
	}

?>
<div class="wfps_settings">
	<?php include 'header.php'; 
        $status 		 = $this->get_license_status();      
		$activation_info = get_option( $this->_activation ); 
	?>
    <div class="wfps_setting_content">
		<div class="wfps_setting_form_wrapper">
			<div class="wfps_tab_container">
				<div class="wfps_setting_tab_menu">
					<ul>
						<?php
							foreach( $settings['tabs'] as $id => $tab ) {
								$active = $current_tab == 'wfps_'.$id ? ' wfps_tab_active' : '';								
						?>
						<li>
							<a href="#wfps_<?php echo esc_attr($id); ?>" class="wfps_tab <?php echo esc_attr($active); ?>" data-id="wfps_<?php echo esc_attr($id); ?>">
								<?php 
									if( isset( $tab['icon'] ) ) : 
										echo $tab['icon'];
									endif; 
								?>								
								<?php echo esc_html($tab['title']); ?>
							</a>
						</li>						
						<?php } ?>
					</ul>
				</div>
				<form method="post" action="options.php" novalidate="novalidate">
					<?php settings_fields( $this->_optionGroup ); ?>									
					<input id="wfps_builder_id" type="hidden" name="woo_free_product_sample_settings[builder_id]" value="<?php echo esc_attr($current_tab); ?>">
				<?php
					$is_pro = false;		
					foreach( $settings['tabs'] as $id => $tab  ) {
						$sections = $tab['sections'];
						$active = $current_tab == 'wfps_'.$id ? ' wfps-tab-active' : '';	 						
				?>					
					<div class="wfps_builder_tab <?php echo esc_attr($active); ?>" data-id="wfps_<?php echo esc_attr($id); ?>">
						<div class="wfps_setting_tab_heading">
							<h2><?php echo $tab['title']; ?></h2>
						</div>

						<div class="wfps_setting_form_setting_tab">
							<div class="wfps_settings_outer_left">
								<div class="wfps_inside">
									<?php 
										foreach( $sections as $sec_id => $section ) {
										$fields = $section['fields'];
									?>
									<table class="form-table">
										<tbody>
										<?php
											foreach( $fields as  $key => $value ) :
												$is_pro = isset( $fields['is_pro'] ) ? $fields['is_pro'] : false;
											?>
											<tr <?php if( isset( $value['position'] ) ) { echo  $value['style']; } ?>>
												<th scope="row">
													<label for="<?php echo $value['name']; ?>">
														<?php echo $value['label']; ?>
													</label>
												</th>
												<td>
													<?php
													$file_name = isset( $value['type'] ) ? $value['type'] : 'text';

													if( $file_name ) {
														include WFPS_ADMIN_DIR_PATH . 'includes/fields/'. $file_name .'.php';
													}
													if( isset($value['is_pro']) && $value['is_pro'] == true && !\Woo_Free_Product_Sample_Helper::is_pro()) {
													?>
													<sup class="wfps-pro-label"><?php echo __('Pro', 'woo-free-product-sample'); ?></sup>
													<?php } ?>
													<?php
													if( isset( $value['description'] ) ) {
														?>
														<div class="woo-free-product-sample-form-desc"><?php echo $value['description']; ?></div>
													<?php } ?>
												</td>
											</tr>

										<?php endforeach; ?>
										
										</tbody>
									</table>
									<?php } ?>									
								</div>
							</div>
						</div>
					</div>				
				<?php } ?>
					<?php do_settings_fields( $this->_optionGroup, 'default' ); ?>														
					<?php do_settings_sections($this->_optionGroup, 'default'); ?>
					<?php submit_button(); ?>				
				</form>
			</div>
		</div>
		<?php include 'sidebar.php'; ?>			
	</div>
	
	<?php include 'footer.php'; ?>

</div> 