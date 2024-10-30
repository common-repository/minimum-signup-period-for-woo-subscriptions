<?php 
/**
 * custom option and settings
 */
add_action( 'admin_init', 'wsmsp_settings_init' );
function wsmsp_settings_init() {
	
	// register a new setting for "wsmsp" page
	register_setting( 'wsmsp', 'wsmsp_options' );

	// register a new section in the "wsmsp" page
	add_settings_section(
	'wsmsp_section_developers',
	__( 'Settings', 'wsmsp' ),
	'',
	'wsmsp'
	);

	// register wsmsp_orders
	add_settings_field(
	'wsmsp_field_orders',
	__( 'General Settings', 'wsmsp' ),
	'wsmsp_field_cb',
	'wsmsp',
	'wsmsp_section_developers',
	[
	'class' => 'wsmsp_row wsmsp_row_general',
	'wsmsp_text' => 'wsmsp_field_text',
	'wsmsp_text2' => 'wsmsp_field_text2',
	'wsmsp_number_months' => 'wsmsp_field_number_months',
	'wsmsp_disable_normal' => 'wsmsp_disable_normal',
	]
	);

}

/**
 * settings fields
 */
function wsmsp_field_cb( $args ) {

	$options = get_option( 'wsmsp_options' );

	$defaulttext = "The first {number} months will be billed upfront today.
	<br/><br/>
	You will then be billed the 'recurring total' every month starting on the date: {date}";

	$defaulttext2 = "for the first {number} months.";

	 ?>

	<style>
  	i { background: #e9f3f5; display: inline-block; padding: 4px; margin: 5px 0 -4px 0; font-style: normal; border-radius: 4px; font-size: 11px; color: #848484; opacity: 0.85; }
	.wsmsp_row th { display: none; }
	</style>
	 
	<div id="general-settings" class="settings-area">
	 
		<p>
			<!-- Custom Text -->
			<strong>Custom "Info" Text:</strong><br/>
			<textarea rows="6" cols="50" id="<?php echo esc_attr( $args['wsmsp_text'] ); ?>"
			data-custom="<?php echo esc_attr( $args['wsmsp_custom_data'] ); ?>"
			name="wsmsp_options[<?php echo esc_attr( $args['wsmsp_text'] ); ?>]"><?php if( isset( $options[ $args['wsmsp_text'] ] ) ) { echo $options[ $args['wsmsp_text'] ]; } else { echo $defaulttext; } ?>
			</textarea><br/>
			<i>Displayed under the order summary page.</i>
		</p>
		<br/>
		<p>
			<!-- Custom Text -->
			<strong>Custom "Subtotal" Text:</strong><br/>
			<textarea rows="1" cols="50" id="<?php echo esc_attr( $args['wsmsp_text2'] ); ?>"
			data-custom="<?php echo esc_attr( $args['wsmsp_custom_data'] ); ?>" name="wsmsp_options[<?php echo esc_attr( $args['wsmsp_text2'] ); ?>]"><?php if( isset( $options[ $args['wsmsp_text2'] ] ) ) { echo $options[ $args['wsmsp_text2'] ]; } else { echo $defaulttext2; } ?></textarea><br/>
			<i>Displayed after the cart subtotal.</i>
		</p>
		<br/>
		<br/>Use " <code>{number}</code> " to show the minimum number of months.
		<br/>Use " <code>{date}</code> " to show the date that the normal renewals will begin.
		<br/>User " <code>&lt;br&gt;</code> " for a new line.

		<br/><br/><br/>

		<p>
			<!-- Recent Orders Number -->
			<strong>Number of Months:</strong><br/>
			<input type="number" value="<?php if($options[ $args['wsmsp_number_months'] ]) { echo $options[ $args['wsmsp_number_months'] ]; } else { echo "0"; } ?>" id="<?php echo esc_attr( $args['wsmsp_number_months'] ); ?>" data-custom="<?php echo esc_attr( $args['wsmsp_number_months'] ); ?>" name="wsmsp_options[<?php echo esc_attr( $args['wsmsp_number_months'] ); ?>]"><br/>
			<i>The number of months that must be paid upfront.</i>
		</p>

		<br/><br/>

		<p>
		  <?php
		  if(isset($options['wsmsp_disable_normal'])) {
		 	$disable_normal = $options['wsmsp_disable_normal'];
		  } else {
			$disable_normal = 1;
		  }
		  $checked = ( $disable_normal == '1' ? ' checked="checked"' : '' );
		  ?>
		  <label class="switch">
			  <input type="hidden" value="0" data-custom="custom" name="wsmsp_options[wsmsp_disable_normal]" >
			  <input type="checkbox" value="1" id="wsmsp_disable_normal" class="wsmsp_disable_normal" data-custom="custom" name="wsmsp_options[wsmsp_disable_normal]"
			  <?php echo $checked; ?>>
			<span class="slider round">
			  <span class="on"><span class="fa-solid fa-check"></span></span>
			  <span class="off"></span>
			</span>
		  </label>
		  <strong><label for="scales">Don't allow non-subscription products to be added to the same cart as a subscription.</label></strong>
		  <br/><i>If you have this disabled, in some cases it may cause issues with the totals displayed on cart if they have both a normal product, and subscription product in their cart.</i>
		</p>
		
	</div>
	
<?php
}

/**
 * top level menu
 */
add_action( 'admin_menu', 'wsmsp_options_page' );
function wsmsp_options_page() {
	
	// add top level menu page
	add_submenu_page(
	'options-general.php',
	'Minimum Signup Period',
	'Woo Signup Period',
	'manage_options',
	'wsmsp',
	'wsmsp_options_page_html'
	);
	
}

/**
 * top level menu:
 * callback functions
 */
function wsmsp_options_page_html() {
	
	// check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// add error/update messages

	// check if the user have submitted the settings
	// wordpress will add the "settings-updated" $_GET parameter to the url
	if ( isset( $_GET['settings-updated'] ) ) {
	// add settings saved message with the class of "updated"
	add_settings_error( 'wsmsp_messages', 'wsmsp_message', __( 'Settings Saved', 'wsmsp' ), 'updated' );
	}

	// show error/update messages
	settings_errors( 'wsmsp_messages' );
	?>
	<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<form action="options.php" method="post">
	<?php
	// output security fields for the registered setting "wsmsp"
	settings_fields( 'wsmsp' );
	// output setting sections and their fields
	// (sections are registered for "wsmsp", each field is registered to a specific section)
	do_settings_sections( 'wsmsp' );
	// output save settings button
	submit_button( 'Save Settings' );
	?>
	</form>

	<br/>Found a bug or have any suggestions? Please get in touch!

	</div>
	
<?php
}