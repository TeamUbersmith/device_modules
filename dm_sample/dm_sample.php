<?php
/**
 * My Device Module
 */

/**
 * My Device Module Class
 *
 * Please change the class name from "dm_sample" to something descriptive, 
 * but retain the "dm_" prefix to ensure that it's detected by Ubersmith. 
 * Please review the existing modules there and make sure you don't choose 
 * a filename that has already been used. This file will need to be named 
 * whatever this class is called with a ".php" extension.
 *
 * When you're ready to use your module, simply place this file in the 
 * "include/device_modules/" subdirectory of your Ubersmith base directory.
 */
class dm_sample extends device_module
{
	// Set namespace for device storage (please rename this to something unique)
	const STORAGE_NAMESPACE = 'my_device_module';
	
	/**
	 * Device Module Title
	 *
	 * This method returns the title of your device module. It will be 
	 * displayed in the module panel's title bar on the Device Manager page 
	 * for your device, as well as in the configuration drop down in the 
	 * Device Types section when you are adding/editing modules. 
	 * Again, please ensure this title is unique and not taken already.
	 *
	 * The uber_i18n() function is used for string translation.
	 */
	public static function title()
	{
		return uber_i18n('My Device Module');
	}

	/**
	 * Device Module Initialization
	 *
	 * This method performs basic initialization routines. In this 
	 * example, custom data for the device is retrieved. Anything else 
	 * that needs to happen initially (e.g. preliminary API calls, 
	 * loading data, checking prerequisites) should be added here.
	 */
	public function init($request = [])
	{
		if (isset($this->device)) {
			// Make API call to retrieve metadata and 
			// store this in a class variable
			$this->metadata = uber_api::call(
				'uber.metadata_get',
				[
					'id'        => $this->device['dev'],
					'meta_type' => 'device',
				]
			);
			
			// Load device storage items in a class variable.
			$this->conf = $this->edit_items();
			foreach ($this->conf as $key => $value) {
				$this->conf[$key] = uber_api::call(
					'device.storage_get',
					[
						'device_id' => $this->device['dev'],
						'item'      => self::STORAGE_NAMESPACE .'.'. $key,
					]
				);
			}
		}
	}

	/**
	 * Device Module Summary
	 *
	 * This method returns an HTML string that will be displayed 
	 * in the module panel on the Device Manager page for your device.
	 *
	 * This is typically used to do any (and sometimes all) of the following: 
	 *  - Display retrieved information
	 *  - Provide a link to an external portal or page
	 *  - Trigger some functionality or configuration within Ubersmith itself
	 */
	public function summary($request = [])
	{
		/*
		 * These two lines request the metadata for the device, which can
		 * be useful if you need to make an external request or some other
		 * decision based on the information associated with the device.
		 */
		$this->init($request);
		$metadata =& $this->metadata;
		
		// Initialize output string, so you can begin building it
		$output = '';
		
		// Put JavaScript code at the top (jQuery is also available)
		$output = '
		<script type="text/javascript" language="JavaScript">
			function change_case() {
				var id = '. j('#dm_sample_'. $this->id .'_content') .';
				$(id).each(function() {
					var change_case_input = $("#change_case_input_'. $this->id .'").val();
					
					if (!$(this).data("loading")) {
						$(this).data("loading",$(this).html());
					}
					$(this).html($(this).data("loading"));
					
					// Call the Device Module AJAX endpoint, specifying the function
					// name as "ajax_change_case"
					$.ajax({
						type: "GET",
						dataType: "html",
						url: "ajax.device_module_call.php?device='. u($this->device['dev']) .'&device_module='. u($this->id) .'&function=ajax_change_case&change_case_input=" + change_case_input,
						success: function(msg) {
							$(id).removeClass("error").html(msg);
						},
						error: function(xhr,status,err) {
							var msg = uber_ajax_error_string(xhr,status,err);
							$(id).addClass("error").text(h(msg));
						}
					});
				});
				return false;
			}
			
			$(function() {
				// Immediately call the change_case() function to populate the dm_sample_x div
				change_case();
			});
		</script>';
		
		// Display date
		$output .= '<p>'. date('l, M j Y') .'</p>';
		
		// Display external link
		$output .= '<p><a href="https://www.google.com/search?q=account '. urlencode($this->conf['account']) .', sub_account '. urlencode($this->conf['sub_account']) .'" target="_blank">'. 'Login to Portal' .'</a></p>';
		
		// Display Device Module configuration from Setup & Admin
		$output .= '<p><strong>Device Module Configuration:</strong></p>';
		
		// $this->config() pulls the whole configuration from Setup & Admin,
		// but you can also access individual items using $this->config('variable')
		$config = $this->config();
		foreach ($config as $key => $value) {
			// Loop through all config items and display them...
			// Note the use of htmlentities() for special characters
			$output .= htmlentities($key) .': '. htmlentities($value) . '<br/>';
		}
		
		// Display Device Metadata (Custom Data)
		$output .= '<p><em>Device Custom Data:</em></p>';
		foreach ($metadata as $metadata_item) {
			$output .= '<p>'. htmlentities($metadata_item['variable']) . ': ' . htmlentities($metadata_item['value']) . '</p>';
		}
		
		// Designate div for AJAX-loaded content.
		// We use the Device Module ID when naming a DOM ID. 
		// This helps us target this specific control without conflicts.
		$output .= '<div id="dm_sample_'. $this->id .'_content"><div style="padding:20px;text-align:center;">'. gui::img('/images/loading.gif') .' '. h(uber_i18n('Loading...')) .'</div></div>';
		
		// Create a simple form and text box for input.
		$output .= '
			<form name="change_case_form" id="change_case_form">
				<input id="change_case_input_'. $this->id .'" type="text" name="change_case_input" value="Mixed-Case String Here">
			</form>';
		
		// Trigger an AJAX call with a link, whose output is based on contents of the above text box
		$output .= '<p><a href="#" onclick="return change_case();">'. 'Change Case' .'</a></p>';
		
		return $output;
	}

	/**
	 * Device Module Configuration Items
	 *
	 * This method returns an array of configuration options that will be
	 * displayed when the module is configured in the Device Types section of
	 * Setup & Admin.
	 */
	public function config_items()
	{
		return [
			'server' => [
				'label'    => uber_i18n('Server'),
				'type'     => 'text',
				'size'     => 30,
				'default'  => '',
				'required' => true,
			],
			'api_key' => [
				'label'    => uber_i18n('API Key'),
				'type'     => 'textarea',
				'rows'     => 10,
				'cols'     => 50,
				'required' => true,
			],
			'threeway_toggle' => [
				'label'   => uber_i18n('Three-Way Toggle'),
				'type'    => 'select',
				'options' => [
					'0' => 'No',
					'1' => 'Yes',
					'2' => 'Sometimes',
				],
				'default' => '0',
			],
			'check_me' => [
				'label'   => uber_i18n('Check Me'),
				'type'    => 'checkbox',
				'default'  => '',
			],
		];
	}
	
	/**
	 * Edit Label
	 *
	 * This method allows you to override the default edit label on the 
	 * module panel, which displays "edit" by default.
	 *
	 * This method is optional.
	 */
	public function edit_label()
	{
		return uber_i18n('configure');
	}
	
	/**
	 * Device Module Instance Configuration Items
	 *
	 * This method returns an array of configuration options that will be
	 * displayed when this specific instance of the module is configured 
	 * via the "edit" link displayed on the module on the device's page.
	 */
	private function edit_items()
	{
		return [
			'account' => [
				'label'   => uber_i18n('Account'),
				'type'    => 'select',
				'options'  => [
					'a' => 'Account A',
					'b' => 'Account B',
					'c' => 'Account C',
					'd' => 'Account D',
				],
				'default' => 'c',
				'required' => true,
			],
			'sub_account' => [
				'label'   => uber_i18n('Sub Account'),
				'type'    => 'text',
				'size'    => 50,
				'default' => '',
				'required' => true,
			],
			'password' => [
				'label'   => uber_i18n('Password'),
				'type'    => 'text', // this should really be of type "password" but we are waiting for a bugfix for this functionality
				'size'    => 50,
				'default' => '',
				'required' => true,
			],
		];
	}
	
	/**
	 * Edit
	 *
	 * This method allows you to edit configuration options for this specific instance 
	 * of your module.
	 *
	 * This method is optional, but required if you wish to add an "edit" link to this 
	 * instance of the module on the device page.
	 */
	public function edit($request = [])
	{
		$this->init();
		
		// Load configuration for this instance of the device module
		$update = $this->conf;
		$items = $this->edit_items();
		$config = $this->edit_fields($items,$this->conf,'config');
		
		if (is_array($config) && !empty($config)) {
			foreach ($config as $field => $item) {
				if (isset($request['config'][$field])) {
					$update[$field] = $request['config'][$field];
				}
			}
			
			$config = $this->edit_fields($items,$update,'config');
		}
		
		if (PEAR::isError($config)) {
			return $config;
		}
		
		$output = '';
		
		while (!empty($request['editclick'])) {
			// Ensure password is stored in an encrypted state
			if (isset($update['password'])) {
				uber_api::call(
					'device.storage_set',
					[
						'device_id' => $this->device['dev'],
						'item'      => self::STORAGE_NAMESPACE .'.password',
						'value'     => $update['password'],
						'encrypt'   => true, // We want encryption for the password!
					]
				);
				
				unset($update['password']); // Important! Do not store password in plain text.
			}
			
			foreach ($update as $key => $value) {
				// We can store the rest of this data unencrypted
				uber_api::call(
					'device.storage_set',
					[
						'device_id' => $this->device['dev'],
						'item'      => self::STORAGE_NAMESPACE .'.'. $key,
						'value'     => $value,
					]
				);
			}
			
			$msg = uber_i18nf('%s updated',self::title());
			$output .= display_success(uber_i18n('Success'),$msg);
			$this->javascript_close($msg);
			break;
		}
		
		$output .= '
			<table border="0" width="100%" cellpadding="4" cellspacing="0" style="margin:10px 0;">';
		
		if (is_array($config) && !empty($config)) {
			foreach ($config as $item) {
				$output .= '
				<tr valign="'. $item['valign'] .'">
					<td width="35%" align="right" class="CellLabel">'. $item['label'] .'</td>
					<td>'. $item['field'] .'</td>
				</tr>';
			}
		} else {
			$output .= '
			<tr class="error">
				<td colspan="2">'. uber_i18n('No configuration options available') .'</td>
			</tr>';
		}
		
		$output .= '
			</table>';
		
		return $output;
	}
	
	/**
	 * Device Module Monitor
	 *
	 * This method allows you to include a monitor for your device that 
	 * will trigger based on some output provided by your module, or 
	 * some other calculation, customizable by you.
	 *
	 * This method should return true if successful, or a PEAR error 
	 * if applicable.
	 *
	 * This method is optional.
	 */
	public function monitor($monitor)
	{
		// Generate random value (replace this with your own monitoring)
		$generated_min = (isset($monitor['extra']['min']) && strlen($monitor['extra']['min']) > 0) ? $monitor['extra']['min'] : 0;
		$generated_max = (isset($monitor['extra']['max']) && strlen($monitor['extra']['max']) > 0) ? $monitor['extra']['max'] : 10;
		$generated_range = abs($generated_max - $generated_min);
		$value = rand(floor($generated_min - $generated_range/2), ceil($generated_max + $generated_range/2));
		
		if (isset($monitor['extra']['min']) && strlen($monitor['extra']['min']) > 0 && $value < $monitor['extra']['min']) {
			// Value is less than specified minimum
			return PEAR::raiseError(sprintf('The value (%1$s) is less than the minimum (%2$s)',$value,$monitor['extra']['min']),1);
		}
		
		if (isset($monitor['extra']['max']) && strlen($monitor['extra']['max']) > 0 && $value > $monitor['extra']['max']) {
			// Value is greater than specified maximum
			return PEAR::raiseError(sprintf('The value (%1$s) is greater than the maximum (%2$s)',$value,$monitor['extra']['max']),1);
		}
		
		return true;
	}

	/**
	 * Device Module Monitor Configuration Items
	 *
	 * If your device module has a monitor, this method will
	 * return an array of the possible configuration options. For example, if
	 * you want to check if a value returned by your module is bounded between two
	 * particular values, you could set a minimum and maximum value, and use them
	 * in the monitor() method above.
	 *
	 * This method is optional.
	 */
	public function monitor_config_items()
	{
		return [
			'min' => [
				'label' => uber_i18n('Minimum'),
				'type' => 'text',
				'size' => 10,
				'default' => '',
				'extra_text' => ' (Unit Label)', // This is useful for specifying unit type
			],
			'max' => [
				'label' => uber_i18n('Maximum'),
				'type' => 'text',
				'size' => 10,
				'default' => '',
				'extra_text' => ' (Unit Label)', // This is useful for specifying unit type
			],
		];
	}
	
	/**
	 * A post-hook for Device edit calls.
	 *
	 * The $request['before'] and $request['after'] variables contain the respective device data.
	 * The $flags contain any flags passed by the system (this is not widely used).
	 *
	 * This method is optional.
	 */
	public function onafteredit($request = [], $flags = [])
	{
		// If the label doesn't contain the word "cool" then do something.
		if (isset($request['before']['label']) && stripos($request['before']['label'],'cool')) {
			// Do something
		}
		
		return true;
	}
	
	/**
	 * A pre-hook for Device edit calls.
	 *
	 * This method is optional.
	 */
	public function onbeforeedit($request = [], $flags = [])
	{
		// Do something
	}
	
	/**
	 * A post-hook for Device create calls.
	 *
	 * This method is optional.
	 */
	public function onaftercreate($request = [], $flags = [])
	{
		// Do something
		
		return true;
	}
	
	/**
	 * A pre-hook for Device create calls.
	 *
	 * This method is optional.
	 */
	public function onbeforecreate($request = [], $flags = [])
	{
		// Do something
	}
	
	/**
	 * A post-hook for Device delete calls.
	 *
	 * This method is optional.
	 */
	public function onafterdelete($request = [], $flags = [])
	{
		// Do something
		
		return true;
	}
	
	/**
	 * A pre-hook for Device delete calls.
	 *
	 * This method is optional.
	 */
	public function onbeforedelete($request = [], $flags = [])
	{
		// Do something
	}
	
	/**
	 * AJAX Content Response
	 *
	 * This method responds to an AJAX call with HTML content for the module.
	 * This method can be named almost anything; you'll just need to make the appropriate 
	 * AJAX call to it, as illustrated above in the summary() method.
	 *
	 * This method is optional.
	 */
	function ajax_change_case($request = array())
	{
		// Validate input in $request array
		if (empty($request['change_case_input'])) {
			return PEAR::raiseError(uber_i18nf('No %s specified','change_case_input'),1);
		}
		
		// Make some API call based on input, or massage data in some way here
		$data_uppercase = strtoupper($request['change_case_input']);
		$data_lowercase = strtolower($request['change_case_input']);
		
		// Then, generate some HTML to return
		$output = '
	<table width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr>
			<td width="100%">
				<table width="100%" border="0" cellpadding="3" cellspacing="0" style="border-right: 1px solid #cccccc;">
					<tr>
						<td width="35%"><strong>'. htmlentities('Uppercase version of input') .':</strong></td>
						<td>'. htmlentities($data_uppercase) .'</td>
					</tr>
					<tr>
						<td><strong>'. htmlentities('Lowercase version of input') .':</strong></td>
						<td>'. htmlentities($data_lowercase) .'</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>';
		
		return $output;
	}
}

// end of script
