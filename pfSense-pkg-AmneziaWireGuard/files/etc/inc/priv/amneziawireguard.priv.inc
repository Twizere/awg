<?php
/*
 * amneziawireguard.priv.inc
 *
 * Part of pfSense (https://www.pfsense.org)
 * Copyright (c) 2024 Your Name or Company
 * All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0
 */

global $priv_list;

$priv_list['page-vpn-amneziawireguard'] = array();
$priv_list['page-vpn-amneziawireguard']['name'] = "WebCfg - VPN: Amnezia-WireGuard";
$priv_list['page-vpn-amneziawireguard']['descr'] = "Allow access to the 'VPN: Amnezia-WireGuard' page.";
$priv_list['page-vpn-amneziawireguard']['match'] = array();

$priv_list['page-vpn-amneziawireguard']['match'][] = "awg/vpn_awg_tunnels.php*";
$priv_list['page-vpn-amneziawireguard']['match'][] = "awg/vpn_awg_tunnels_edit.php*";
$priv_list['page-vpn-amneziawireguard']['match'][] = "awg/vpn_awg_peers.php*";
$priv_list['page-vpn-amneziawireguard']['match'][] = "awg/vpn_awg_peers_edit.php*";
$priv_list['page-vpn-amneziawireguard']['match'][] = "awg/vpn_awg_settings.php";

$priv_list['page-status-amneziawireguard'] = array();
$priv_list['page-status-amneziawireguard']['name'] = "WebCfg - Status: Amnezia-WireGuard Status";
$priv_list['page-status-amneziawireguard']['descr'] = "Allow access to the 'Status: Amnezia-WireGuard' page.";
$priv_list['page-status-amneziawireguard']['match'] = array();

$priv_list['page-status-amneziawireguard']['match'][] = "awg/status_amneziawireguard.php";

?>
