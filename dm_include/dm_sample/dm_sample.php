<?php

/**
 * My Device Module
 *
 * You will want to change dm_mydevicemodule to something a little more
 * descriptive, however be sure to retain the dm_ prefix.
 *
 * When complete, place your finished module into include/device_modules/
 *
 * @package ubersmith_customizations
 */

/**
 * My Device Module Class
 *
 * @package ubersmith_customizations
 */
class dm_sample extends device_module
{
	/**
	 * Device Module Title
	 *
	 * This function returns the title of your device module. It will be
	 * included in the light blue title bar on the Device Manager page
	 * for your device, as well as in the configuration drop down in the
	 * Device Types section of 'Setup & Admin'.
	 *
	 * @return string
	 */
	public static function title()
	{
		return uber_i18n('My Device Module');
	}

	/**
	 * Device Module Initialization
	 *
	 * This function will perform some basic initialization routines. In this
	 * example, custom data for the device is retrieved. Other useful calls
	 * could be included in this step, if needed.
	 *
	 * @return void
	 */
	public function init($request = array())
	{
		if (isset($this->device)) {
			$this->metas = device_metadata($this->device);
		}
	}

	/**
	 * Device Module Summary
	 *
	 * This function returns a string (usually of HTML) that will be displayed
	 * in the Device Module's 'box' on the device's Device Manager page
	 *
	 * This can be used to display retrieved information, or provide a link
	 * to a utility outside of Ubersmith, or to call some functionality within
	 * Ubersmith.
	 *
	 * @return string
	 */
	public function summary($request = array())
	{
		/*
		 * These two lines request the metadata for the device, which can
		 * be useful if you need to make an external request or some other
		 * decision based on the information associated with the device.
		 */
		$this->init($request);
		$metas  =& $this->metas;
		$device =& $this->device;
		/*
		 * Use this->config('variable') to retrieve values set in
		 * 'Setup & Admin', if necessary.
		 */
		$username = $this->config('username');

		$output = '<b>My Device Module</b><br />';
		$output .= 'Today is ' . date("D M j") . '</br>';
		$output .= 'Configured username is: ' . h($username) . '<br />';

		foreach ($device as $key => $value) {
			if (is_array($value)) {
				$value = implode(',', $value);
			}
			$output .= h($key) . ' = ' . h($value) . '<br />';
		}

		foreach ($metas as $key => $value) {
			if (is_array($value)) {
				$value = implode(',', $value);
			}
			$output .= h($key) . ' = ' . h($value) . '<br />';
		}

		return $output;
	}

	/**
	 * Device Module Configuration Items
	 *
	 * This function returns an array of configuration options that will be
	 * displayed when the module is configured in the Device Types section of
	 * Setup & Admin.
	 *
	 * @return array
	 */
	public function config_items()
	{
		return array(
			'username' => array(
				'label' => uber_i18n('Username'),
				'type' => 'text',
				'size' => 30,
				'default' => '',
			),
			'decision' => array(
				'label'   => uber_i18n('Make a decision'),
				'type'    => 'select',
				'options' => array(
					'0' => 'No',
					'1' => 'Yes',
				),
				'default' => '0',
			),
		);
	}

	/**
	 * Device Module Monitor Function
	 *
	 * This function allows you to include a monitor for your device that
	 * will trigger based on some output provided by your module, or some
	 * other calculation.
	 *
	 * This function returns a PEAR::raiseError object on error, otherwise
	 * returns true, indicating a successful run of the monitor.
	 *
	 * This function is optional.
	 *
	 * @return mixed
	 */
	public function monitor($monitor)
	{
		/*
		 * This code creates a bogus monitor that will probably fail
		 * most of the time.
		 */
		$value = rand(0, $monitor['extra']['min']) + 5;

		if (isset($monitor['extra']['min']) && strlen($monitor['extra']['min']) > 0) {
			if ($value < $monitor['extra']['min']) {
				return PEAR::raiseError(uber_i18nf('Value (%1$s) is less than minimum (%2$s)',$value,$monitor['extra']['min']),1);
			}
		}

		if (isset($monitor['extra']['max']) && strlen($monitor['extra']['max']) > 0) {
			if ($value > $monitor['extra']['max']) {
				return PEAR::raiseError(uber_i18nf('Value (%1$s) is greater than maximum (%2$s)',$value,$monitor['extra']['max']),1);
			}
		}

		return true;
	}

	/**
	 * Device Module Monitor Configuration Items
	 *
	 * If your device module is going to have a monitor, this function will
	 * return an array of the possible configuration options. For example, if
	 * you want to check if a value returned by your module is between two
	 * other values, you could set a minimum and maximum value, and use them
	 * in the monitor() code above.
	 *
	 * This function is optional.
	 *
	 * @return array
	 */
	public function monitor_config_items()
	{
		return array(
			'min' => array(
				'label' => uber_i18n('Minimum'),
				'type' => 'text',
				'size' => 10,
				'default' => '',
			),
			'max' => array(
				'label' => uber_i18n('Maximum'),
				'type' => 'text',
				'size' => 10,
				'default' => '',
			),
		);
	}
}

// end of script
