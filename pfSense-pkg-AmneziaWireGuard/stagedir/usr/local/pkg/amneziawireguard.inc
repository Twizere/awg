<?php
/*
 * amneziawireguard.inc
 *
 * Part of pfSense (https://www.pfsense.org)
 * Copyright (c) 2024 Your Name or Company
 * All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0
 */

global $g, $awg;

// Error codes for Amnezia-WireGuard
define('AWG_ERROR_IF_NAME', 1);
define('AWG_ERROR_IF_CREATE', 2);
define('AWG_ERROR_IF_DESTROY', 256);

$awg = array(
    'wg'             => '/usr/bin/awg', // Custom binary for Amnezia-WireGuard
    'ifconfig'       => '/sbin/ifconfig',
    'pkg_root'       => '/usr/local/pkg/amneziawireguard',
    'conf_path'      => '/usr/local/etc/amneziawireguard',
    'service_name'   => 'amneziawireguardd',
    'default_mtu'    => 1420,
    'default_port'   => 51821, // Avoid conflict with the default WireGuard
    'max_tunnels'    => 32768,
    'error_flags'    => array(
        'interface' => array(
            AWG_ERROR_IF_NAME    => gettext('Invalid Amnezia-WireGuard tunnel name'),
            AWG_ERROR_IF_CREATE  => gettext('Unable to create Amnezia-WireGuard tunnel interface'),
            AWG_ERROR_IF_DESTROY => gettext('Unable to destroy Amnezia-WireGuard tunnel interface'),
        ),
    ),
);

// Function to initialize global variables
function awg_globals() {
    global $awg;

    // Reload config.xml to reflect any changes
    config_read_file(false, true);
}

// Utility functions
function &array_get_value(array &$array, $parents) {
    $ref = &$array;

    foreach ((array) $parents as $parent) {
        if (is_array($ref) && array_key_exists($parent, $ref)) {
            $ref = &$ref[$parent];
        } else {
            return null;
        }
    }

    return $ref;
}

function array_set_value(array &$array, $parents, $value) {
    $ref = &$array;

    foreach ((array) $parents as $parent) {
        if (isset($ref) && !is_array($ref)) {
            $ref = array();
        }

        $ref = &$ref[$parent];
    }

    $ref = $value;
}

function array_unset_value(&$array, $parents) {
    $key = array_shift($parents);

    if (empty($parents)) {
        unset($array[$key]);
    } else {
        array_unset_value($array[$key], $parents);
    }
}

/*
 * Write to configuration with an optional automatic configuration backup (ACB)
 */
function awg_write_config($desc = 'unknown', $backup = true) {
    global $awg;

    $desc = "[{$awg['service_name']}] {$desc}";
    return write_config($desc, $backup);
}

// Initialize globals
awg_globals();
?>
