<?php
/*
 * wg_globals.inc
 *
 * part of pfSense (https://www.pfsense.org)
 * Copyright (c) 2021 R. Christian McDonald (https://github.com/rcmcdonald91)
 * Copyright (c) 2022-2023 Rubicon Communications, LLC (Netgate)
 * All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the 'License');
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an 'AS IS' BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

// Global defines

// pfSense includes
require_once('config.inc');
require_once('globals.inc');

global $g, $wgg;

define('WG_ERROR_IF_NAME',		1);
define('WG_ERROR_IF_CREATE',		2);
define('WG_ERROR_IF_GROUP',		4);
define('WG_ERROR_IF_SETADDR',		8);
define('WG_ERROR_IF_DELADDR',		16);
define('WG_ERROR_IF_UP',		32);
define('WG_ERROR_IF_DOWN',		64);
define('WG_ERROR_IF_SYNC',		128);
define('WG_ERROR_IF_DESTROY',		256);

define('WG_ERROR_KLD_LOAD',		1);
define('WG_ERROR_KLD_UNLOAD',		2);

define('WG_ERROR_PEER_SET',		1);
define('WG_ERROR_PEER_ENDPOINT',	2);
define('WG_ERROR_PEER_REMOVE',		4);

define('WG_ERROR_SVC_DISABLED',		1);
define('WG_ERROR_SVC_RUNNING',		2);
define('WG_ERROR_SVC_START',		4);
define('WG_ERROR_SVC_STOP',		8);
define('WG_ERROR_SVC_CREATE',		16);

$wgg = array(
	'wg'			=> '/usr/local/bin/awg',
	'awg-quick'		=> '/usr/local/bin/awg-quick',
	'ifconfig'		=> '/sbin/ifconfig',
	'pkg'			=> '/usr/sbin/pkg',
	'kldload'		=> '/sbin/kldload',
	'kldunload'		=> '/sbin/kldunload',
	'php'			=> '/usr/local/bin/php',
	'php_wg'		=> '/usr/local/bin/php_wg', // it was /usr/local/bin/php_wg , just changed it remporary 
	'wg_pkg_root'	=> '/usr/local/pkg/amneziawireguard',
	'kmod'			=> 'if_awg',
	'subsystem_prefix'	=> 'amneziawg',
	'service_name'		=> 'amneziawg',
	'nrb'			=> '-NoReMoTeBaCkUp',
	'applylist'		=> array(
					'tunnels' => "{$g['tmp_path']}/.wireguard_tunnel.apply"),
	'pkg_name'		=> 'pfSense-pkg-AmneziaWireGuard',
	'conf_path'		=> '/usr/local/etc/amneziawireguard',
	'xml_path'		=> array('installedpackages', 'amneziawg'),
	'if_prefix'		=> 'tun_awg',
	'ifgroupentry'		=> array(
					'ifname' => 'AmneziaWG', 
					'descr' => gettext('AmneziaWireGuard Interface Group (DO NOT EDIT/DELETE!)'),
					'members' => null),
	'unboundaclentry'	=> array(
					'aclname' => 'AmneziaWG',
					'aclaction' => 'allow',
					'description' => gettext('AmneziaWireGuard Unbound ACL (DO NOT EDIT/DELETE!)')),
	'listenportalias'	=> array(
					'name' => 'AmneziaWGListenPorts',
					'type' => 'port',
					'descr' => gettext('AmneziaWireGuard ListenPort Alias (DO NOT EDIT/DELETE!)')),
	'handshake_thresholds' 	=> array(
					86400 => array('class' => 'text-danger', 'title' => gettext('Greater than 1 day')),
					300 => array('class' => 'text-warning', 'title' => gettext('Greater than 5 minutes')),
					0 => array('class' => 'text-success', 'title' => gettext('Less than 5 minutes'))),
	'error_flags'		=> array(
					'interface' => array(
						WG_ERROR_IF_NAME	=> gettext('Invalid AmneziaWireGuard tunnel name'),
						WG_ERROR_IF_CREATE	=> gettext('Unable to create AmneziaWireGuard tunnel interface'),
						WG_ERROR_IF_GROUP	=> gettext('Unable to add AmneziaWireGuard tunnel interface to the AmneziaWireGuard interface group'),
						WG_ERROR_IF_SETADDR	=> gettext('Unable to set AmneziaWireGuard tunnel interface address(es)'),
						WG_ERROR_IF_DELADDR	=> gettext('Unable to delete AmneziaWireGuard tunnel interface address(es)'),
						WG_ERROR_IF_UP		=> gettext('Unable to bring up AmneziaWireGuard tunnel interface'),
						WG_ERROR_IF_DOWN	=> gettext('Unable to bring down AmneziaWireGuard tunnel interface'),
						WG_ERROR_IF_SYNC	=> gettext('Unable to sync AmneziaWireGuard tunnel configuration with wg(8)'),
						WG_ERROR_IF_DESTROY	=> gettext('Unable to destroy AmneziaWireGuard tunnel interface')),
					'kld' => array(
						WG_ERROR_KLD_LOAD	=> gettext('Unable to load AmneziaWireGuard kernel module'),
						WG_ERROR_KLD_UNLOAD	=> gettext('Unable to unload AmneziaWireGuard kernel module')),
					'peer' => array(
						WG_ERROR_PEER_SET	=> gettext('Unable to set peer configuration'),
						WG_ERROR_PEER_ENDPOINT	=> gettext('Unable to resolve peer endpoint'),
						WG_ERROR_PEER_REMOVE	=> gettext('Unable to remove peer')),
					'service' => array(
						WG_ERROR_SVC_DISABLED	=> gettext('AmneziaWireGuard service is disabled'),
						WG_ERROR_SVC_RUNNING	=> gettext('AmneziaWireGuard service is already running'),
						WG_ERROR_SVC_START	=> gettext('Unable to start AmneziaWireGuard service'),
						WG_ERROR_SVC_STOP	=> gettext('Unable to stop AmneziaWireGuard service'),
						WG_ERROR_SVC_CREATE	=> gettext('Unable to create AmneziaWireGuard tunnel(s)'))),
	'default_mtu'				=> 1420,
	'default_port'				=> 51820,
	'default_resolve_interval'		=> 300,
	'default_widget_activity_threshold'	=> 120,
	'default_widget_refresh_interval'	=> 1,
	'max_port'				=> 65535,
	'max_tunnels'				=> 32768
);

// These all depend on one more more of the above values

$wgg['wg_classes']		= "{$wgg['wg_pkg_root']}/classes";
$wgg['wg_includes']		= "{$wgg['wg_pkg_root']}/includes";

$wgg['conf_paths_to_clean']	= array($wgg['conf_path'], '/etc/wireguard', '/etc/wg');
$wgg['xml_paths_to_clean']	= array($wgg['xml_path'], array('amneziawg'));

$wgg['depends_names']		= array(
					$wgg['pkg_name'],
					//'wireguard-kmod',
					//'wireguard-tools-lite'
					);

$wgg['pid_path']		= "{$g['varrun_path']}/{$wgg['service_name']}.pid";
$wgg['subsystems']		= array(
					'wg' => $wgg['subsystem_prefix'], 
					'postboot' => "{$wgg['subsystem_prefix']}_postboot");
$wgg['wg_daemon'] 		= "{$wgg['php_wg']} -f {$wgg['wg_includes']}/wg_service.inc";

$wgg['shellcmdentries']		= array(
					array(
						'cmd' => "service {$wgg['service_name']} start",
						'cmdtype' => 'earlyshellcmd',
						'description' => 'AmneziaWireGuard earlyshellcmd (DO NOT EDIT/DELETE!)'));

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
 * This is a generalized implementation of init_config_arr
 */
function wg_init_config_arr(&$a_config, $keys) {
	$c = &$a_config;

	if (!is_array($keys)) {
		return null;
	}

	foreach ($keys as $k) {
		if (!is_array($c[$k])) {
			$c[$k] = array();
		}

		$c = &$c[$k];
	}
}

/*
 * A wrapper for write_config() with the ability to easily toggle ACB on or off
 * 
 * Ephemeral changes to the config should not hammer the ACB system. Only changes
 * to <installedpackages><wireguard>...</wireguard></installedpackages> tree should
 * invoke a ACB backup event.
 */
function wg_write_config($desc = 'unknown', $do_acb = false, $backup = true, $write_config_only = false) {
	global $wgg;

	$descACB = fn($desc, $do_acb) => $do_acb ? $desc : "{$desc}{$wgg['nrb']}";

	$desc = "[{$wgg['pkg_name']}] {$descACB($desc, $do_acb)}";

	return write_config($desc, $backup, $write_config_only);
}

/*
 * This populates the $wgg with the latest information from config.xml
 */
function wg_globals() {
	global $config, $wgg;

	// Reload config.xml to get any recent changes
	$config = parse_config(true);

	// An array of configuration paths we need
	$conf_paths 	= array(
				'config' => array_merge($wgg['xml_path'], array('config', 0)),
				'tunnels' => array_merge($wgg['xml_path'], array('tunnels', 'item')),
				'peers' => array_merge($wgg['xml_path'], array('peers', 'item')));

	// Need to initialize these config paths as arrays and then assign by reference to $wgg
	foreach ($conf_paths as $key => $path) {
		wg_init_config_arr($config, $path);
		$wgg[$key] = &array_get_value($config, $path);
	}
}

// Call this often to read the latest configuration information
wg_globals();

?>
