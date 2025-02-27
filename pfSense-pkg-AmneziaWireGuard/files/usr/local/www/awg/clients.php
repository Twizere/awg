<?php
require_once("functions.inc");
require_once("guiconfig.inc");
require_once("pfsense-utils.inc");
require_once("pkg-utils.inc");
require_once("filter.inc");
require_once("certs.inc");

$pgtitle = array("VPN", "Amenezia WG", "Peers");
include("head.inc");

$tab_array = array();
$tab_array[] = array(gettext("Server"), false, "index.php");
$tab_array[] = array(gettext("Clients"), true, "?tab=clients");
$tab_array[] = array(gettext("Client Export"), false, "index.php");
add_package_tabs("AWG", $tab_array);
display_top_tabs($tab_array);

// Read the WireGuard configuration file
$wg_config_file = "/etc/amnezia/amneziawg/awg0.conf";
$wg_config = file_exists($wg_config_file) ? file_get_contents($wg_config_file) : "Configuration file not found.";

$peers = [];
$parsing_peers = false;
$current_peer = [];

foreach (explode("\n", $wg_config) as $line) {
    $line = trim($line);
    
    if ($line === "[Peer]") {
        $parsing_peers = true;
        $current_peer = []; // Start a new peer entry
    }
    
    if ($parsing_peers) {
        if (strpos($line, "PublicKey") !== false) {
            $current_peer['PublicKey'] = trim(substr($line, strpos($line, '=') + 1));
        }
        if (strpos($line, "PersistentKeepalive") !== false) {
            $current_peer['PersistentKeepalive'] = trim(substr($line, strpos($line, '=') + 1));
        }
        if (strpos($line, "AllowedIPs") !== false) {
            $current_peer['AllowedIPs'] = trim(substr($line, strpos($line, '=') + 1));
        }
        // When a peer section ends, add the peer to the array
        if (empty($line)) {
            if (!empty($current_peer)) {
                $peers[] = $current_peer;
            }
            $parsing_peers = false;
        }
    }
}

// Create the table to display peer information
echo "<div class='panel panel-success'>";
echo "<div class='panel-heading'><h2 class='panel-title'>Peer Information</h2></div>";
echo "<div class='panel-body'>";
echo "<table class='table table-striped table-bordered'>";
echo "<thead><tr>";
echo "<th>" . gettext("Public Key") . "</th>";
echo "<th>" . gettext("Persistent Keepalive") . "</th>";
echo "<th>" . gettext("Allowed IPs") . "</th>";
echo "</tr></thead>";
echo "<tbody>";

foreach ($peers as $peer) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($peer['PublicKey']) . "</td>";
    echo "<td>" . htmlspecialchars($peer['PersistentKeepalive']) . "</td>";
    echo "<td>" . htmlspecialchars($peer['AllowedIPs']) . "</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";
echo "</div>";
echo "</div>";

include("foot.inc");
?>
