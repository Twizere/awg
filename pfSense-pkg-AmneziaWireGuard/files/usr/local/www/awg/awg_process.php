<?php
require_once("functions.inc");
require_once("guiconfig.inc");
require_once("pfsense-utils.inc");
require_once("pkg-utils.inc");
require_once("filter.inc");
require_once("certs.inc");

$pgtitle = array("VPN", "Amenezia WG");
include("head.inc");

 
$tab_array = array();
$tab_array[] = array(gettext("Server"), true, "index.php");
$tab_array[] = array(gettext("Clients"), false, "clients.php");
$tab_array[] = array(gettext("Client Export"), false, "index.php");
add_package_tabs("AWG", $tab_array);
display_top_tabs($tab_array);

// Read the WireGuard configuration file
$wg_config_file = "/etc/amnezia/amneziawg/awg0.conf";
$wg_config = file_exists($wg_config_file) ? file_get_contents($wg_config_file) : "Configuration file not found.";

$server_config = "";
$peers_config = "";
$parsing_peers = false;

foreach (explode("\n", $wg_config) as $line) {
    if (trim($line) === "[Peer]") {
        $parsing_peers = true;
    }
    
    if ($parsing_peers) {
        $peers_config .= htmlspecialchars($line) . "<br>";
    } else {
        $server_config .= htmlspecialchars($line) . "<br>";
    }
}

// Display the server configuration
echo "<div class='panel panel-primary'>";
echo "<div class='panel-heading'><h2 class='panel-title'>Server Configuration</h2></div>";
echo "<div class='panel-body'><pre>{$server_config}</pre></div>";
echo "</div>";

// Display the peers configuration if on Clients tab
if ($_GET['tab'] === "clients") {
    echo "<div class='panel panel-success'>";
    echo "<div class='panel-heading'><h2 class='panel-title'>Client Peers</h2></div>";
    echo "<div class='panel-body'><pre>{$peers_config}</pre></div>";
    echo "</div>";
}

include("foot.inc");
?>
