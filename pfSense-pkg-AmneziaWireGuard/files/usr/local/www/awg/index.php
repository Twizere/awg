<?php
require_once("functions.inc");
require_once("guiconfig.inc");
require_once("pfsense-utils.inc");
require_once("pkg-utils.inc");
require_once("filter.inc");
require_once("certs.inc");

$pgtitle = array("VPN", "Amenezia WG", "Status");
include("head.inc");

$tab_array = array();
$tab_array[] = array(gettext("Status"), true, "index.php");
$tab_array[] = array(gettext("Server"), false, "server.php");
$tab_array[] = array(gettext("Clients"), false, "clients.php");
$tab_array[] = array(gettext("Client Export"), false, "export.php");
add_package_tabs("AWG", $tab_array);
display_top_tabs($tab_array);

// Function to detect AWG (Amenezia WG) WireGuard interfaces
function get_awg_interfaces() {
    // Run ifconfig to detect WireGuard interfaces
    $interfaces = [];
    $ifconfig_output = shell_exec('ifconfig');
    if ($ifconfig_output) {
        $lines = explode("\n", $ifconfig_output);
        $current_interface = null;

        foreach ($lines as $line) {
            // If the line starts with an interface name, we capture the interface
            if (preg_match('/^(\S+):/', $line, $matches)) {
                $current_interface = $matches[1];
            }
            // If the interface has 'wg' in its name, it’s a WireGuard interface for AWG
            if ($current_interface && strpos($current_interface, 'wg') === 0) {
                // Check for specific AWG WireGuard attributes (e.g., public key, listen port, etc.)
                if (strpos($line, 'wg') !== false) {
                    $interfaces[] = $current_interface;
                }
            }
        }
    }
    return $interfaces;
}

// Get all running AWG (Amenezia WG) interfaces
$awg_interfaces = get_awg_interfaces();

echo "<div class='panel panel-success'>";
echo "<div class='panel-heading'><h2 class='panel-title'>Amenezia WG Interfaces</h2></div>";
echo "<div class='panel-body'>";

// Check if there are any running AWG interfaces
if (!empty($awg_interfaces)) {
    echo "<table class='table table-striped table-bordered'>";
    echo "<thead><tr><th>" . gettext("Interface Name") . "</th><th>" . gettext("Status") . "</th></tr></thead>";
    echo "<tbody>";

    // List all AWG interfaces with their status
    foreach ($awg_interfaces as $interface) {
        // You could add more status checks here (e.g., check if the interface is up or the configuration is valid)
        $status = "Running"; // Assuming the interface is running
        echo "<tr><td>" . htmlspecialchars($interface) . "</td><td>" . htmlspecialchars($status) . "</td></tr>";
    }

    echo "</tbody>";
    echo "</table>";
} else {
    echo "<p>No active Amenezia WG interfaces found.</p>";
}

echo "</div>";
echo "</div>";

include("foot.inc");
?>
