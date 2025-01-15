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

 
// Create the form
$form = new Form('Save Configurations'); 
$form->setAction('awg_process.php');
$section = new Form_Section('Inbound Settings');

// Server Listening
$section->addInput(new Form_Input(
    'listen',
    '*Server Listening Address',
    'text',
    ''
))->setHelp('IP address the server will listen on (default is 0.0.0.0).');

$section->addInput(new Form_Input(
    'port',
    '*Listening Port',
    'text',
    ''
))->setHelp('Port number for the Xray server to listen on.');

 
// Stream Settings
$streamSection = new Form_Section('Security Settings');

$streamSection->addInput(new Form_Select(
    'network',
    '*Stream Network',
    'text',
    ['tcp' => 'TCP', 'kcp' => 'KCP', 'ws' => 'WebSocket', 'http' => 'HTTP']
))->setHelp('Select the network protocol.');


// Add sections to the form
$form->add($section);
$form->add($clientsSection);
$form->add($streamSection);
$form->add($tlsSection);
print($form);

include("foot.inc");
?>
