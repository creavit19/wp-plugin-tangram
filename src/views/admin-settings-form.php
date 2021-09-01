<form method="post" name="my_options" action="options.php">

	<?php

	// Load all values of form elements
	$options = get_option($plugin_name, array('catalog' => ''));
	// Current state of options
	$catalog = $options['catalog'];

	// Displays hidden form fields on the settings page
	settings_fields( $plugin_name );
	do_settings_sections( $plugin_name );

	?>

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<fieldset>
		<legend class="screen-reader-text"><span><?php _e('Projects Catalog:', $text_domain);?></span></legend>
		<label for="<?php echo $plugin_name;?>-catalog">
			<span><?php esc_attr_e('Projects Catalog:', $text_domain);?></span>
		</label>
		<input type="text"
			   class="regular-text" id="<?php echo $plugin_name;?>-catalog"
			   name="<?php echo $plugin_name;?>[catalog]"
			   value="<?php if(!empty($catalog)) echo $catalog; ?>"
			   placeholder="<?php esc_attr_e('Enter projects catalog', $text_domain);?>"
		/>
	</fieldset>

	<?php submit_button(__('Save all changes', $text_domain), 'primary','submit', TRUE); ?>

</form>
