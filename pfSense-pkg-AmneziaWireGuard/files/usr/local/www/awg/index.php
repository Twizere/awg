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
$tab_array[] = array(gettext("Clients"), false, "?tab=clients");
$tab_array[] = array(gettext("Client Export"), false, "index.php");
add_package_tabs("AWG", $tab_array);
display_top_tabs($tab_array);

// Read the WireGuard configuration file
$wg_config_file = "/etc/amnezia/amneziawg/awg0.conf";
$wg_config = file_exists($wg_config_file) ? file_get_contents($wg_config_file) : "Configuration file not found.";

// Initialize variables for each configuration
$privateKey = '';
$listenPort = '';
$jC = '';
$jMin = '';
$jMax = '';
$s1 = '';
$s2 = '';
$h1 = '';
$h2 = '';
$h3 = '';
$h4 = '';

// Parse the configuration file
foreach (explode("\n", $wg_config) as $line) {
    // Extract values from the configuration file
    if (strpos($line, 'PrivateKey') !== false) {
        $privateKey = trim(substr($line, strpos($line, '=') + 1));
    }
    if (strpos($line, 'ListenPort') !== false) {
        $listenPort = trim(substr($line, strpos($line, '=') + 1));
    }
    if (strpos($line, 'Jc') !== false) {
        $jC = trim(substr($line, strpos($line, '=') + 1));
    }
    if (strpos($line, 'Jmin') !== false) {
        $jMin = trim(substr($line, strpos($line, '=') + 1));
    }
    if (strpos($line, 'Jmax') !== false) {
        $jMax = trim(substr($line, strpos($line, '=') + 1));
    }
    if (strpos($line, 'S1') !== false) {
        $s1 = trim(substr($line, strpos($line, '=') + 1));
    }
    if (strpos($line, 'S2') !== false) {
        $s2 = trim(substr($line, strpos($line, '=') + 1));
    }
    if (strpos($line, 'H1') !== false) {
        $h1 = trim(substr($line, strpos($line, '=') + 1));
    }
    if (strpos($line, 'H2') !== false) {
        $h2 = trim(substr($line, strpos($line, '=') + 1));
    }
    if (strpos($line, 'H3') !== false) {
        $h3 = trim(substr($line, strpos($line, '=') + 1));
    }
    if (strpos($line, 'H4') !== false) {
        $h4 = trim(substr($line, strpos($line, '=') + 1));
    }
}

// Create the form
$form = new Form();

// Server Configuration Section
$section = new Form_Section('Server Configuration');
$section->addInput(new Form_Input(
    'private_key',
    '*Private Key',
    'text',
    $privateKey
))->setHelp('Enter the Private Key for the server.')
  ->setAttribute('readonly', true);  // Make this field read-only

$section->addInput(new Form_Input(
    'listen_port',
    '*Listen Port',
    'text',
    $listenPort
))->setHelp('Port for the server to listen on.')
  ->setAttribute('readonly', true);  // Make this field read-only

$section->addInput(new Form_Input(
    'jc',
    'Jc',
    'text',
    $jC
))->setHelp('Value for Jc.')
  ->setAttribute('readonly', true);  // Make this field read-only

$section->addInput(new Form_Input(
    'jmin',
    'Jmin',
    'text',
    $jMin
))->setHelp('Value for Jmin.')
  ->setAttribute('readonly', true);  // Make this field read-only

$section->addInput(new Form_Input(
    'jmax',
    'Jmax',
    'text',
    $jMax
))->setHelp('Value for Jmax.')
  ->setAttribute('readonly', true);  // Make this field read-only

$section->addInput(new Form_Input(
    's1',
    'S1',
    'text',
    $s1
))->setHelp('Value for S1.')
  ->setAttribute('readonly', true);  // Make this field read-only

$section->addInput(new Form_Input(
    's2',
    'S2',
    'text',
    $s2
))->setHelp('Value for S2.')
  ->setAttribute('readonly', true);  // Make this field read-only

$section->addInput(new Form_Input(
    'h1',
    'H1',
    'text',
    $h1
))->setHelp('Value for H1.')
  ->setAttribute('readonly', true);  // Make this field read-only

$section->addInput(new Form_Input(
    'h2',
    'H2',
    'text',
    $h2
))->setHelp('Value for H2.')
  ->setAttribute('readonly', true);  // Make this field read-only

$section->addInput(new Form_Input(
    'h3',
    'H3',
    'text',
    $h3
))->setHelp('Value for H3.')
  ->setAttribute('readonly', true);  // Make this field read-only

$section->addInput(new Form_Input(
    'h4',
    'H4',
    'text',
    $h4
))->setHelp('Value for H4.')
  ->setAttribute('readonly', true);  // Make this field read-only

// Add section to the form
$form->add($section);

// Display the form
print($form);
include("foot.inc");
?>
