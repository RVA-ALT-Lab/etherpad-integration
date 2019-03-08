<?php
/**
 * Plugin Name:     Etherpad Integration
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     etherpad-integration
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Etherpad_Integration
 */

// Your code starts here.

require_once dirname(__FILE__) . '/class-etherpad-integration.php';

$etherpad_integration = new EtherpadIntegration();
$etherpad_integration->init();




