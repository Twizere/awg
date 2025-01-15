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
    $currentConfig['listen']
))->setHelp('IP address the server will listen on (default is 0.0.0.0).');

$section->addInput(new Form_Input(
    'port',
    '*Listening Port',
    'text',
    $currentConfig['port']
))->setHelp('Port number for the Xray server to listen on.');

// Protocol
$section->addInput(new Form_Select(
    'protocol',
    '*Protocol',
    $currentConfig['protocol'],
    ['vless' => 'VLESS', 'vmess' => 'VMess', 'trojan' => 'Trojan']
))->setHelp('Select the protocol for inbound connections.');



// Clients - Dynamically Generated
$clientsSection = new Form_Section('Clients');
$clientsSection->addInput(new Form_Textarea(
    'clients',
    'Clients (JSON)',
    $currentConfig['clients']
))->setHelp('Provide a JSON array of clients including ID, level, and email. You can add multiple clients here.');

// Decryption Method
$section->addInput(new Form_Select(
    'decryption',
    '*Decryption Method',
    $currentConfig['decryption'],
    ['none' => 'None']
))->setHelp('Select the decryption method.');

// Stream Settings
$streamSection = new Form_Section('Security Settings');

$streamSection->addInput(new Form_Select(
    'network',
    '*Stream Network',
    $currentConfig['network'],
    ['tcp' => 'TCP', 'kcp' => 'KCP', 'ws' => 'WebSocket', 'http' => 'HTTP']
))->setHelp('Select the network protocol.');

$streamSection->addInput(new Form_Select(
    'security',
    '*Stream Security',
    $currentConfig['security'],
    ['tls' => 'TLS', 'none' => 'None']
))->setHelp('Select the security method for the stream.');

// TLS Settings
$tlsSection = new Form_Section('TLS Settings');

$tlsSection->addInput(new Form_Input(
    'tls_server_name',
    '*Server Name',
    'text',
    $currentConfig['tls_server_name']
))->setHelp('Specify the server name for TLS.');

$tlsSection->addInput(new Form_Textarea(
    'tls_alpn',
    'ALPN',
    $currentConfig['tls_alpn']
))->setHelp('Enter Application-Layer Protocol Negotiation (ALPN) values, separated by commas.');

// Server Certificate Selection
$tlsSection->addInput(new Form_Select(
	'server_cert',
	'*Server Certificate',
	$pconfig['server_cert'],
	$client_certificates
))->setHelp('Select a certificate which will be used by the Xray server.');

// CA Certificate Selection
$tlsSection->addInput(new Form_Select(
    'ca_cert',
    '*Peer Certificate Authority',
    $pconfig['ca_cert'],
    $ca_certificates
))->setHelp('Select a certificate authority to validate the peer certificate.');


// Add sections to the form
$form->add($section);
$form->add($clientsSection);
$form->add($streamSection);
$form->add($tlsSection);
print($form);

include("foot.inc");
?>
