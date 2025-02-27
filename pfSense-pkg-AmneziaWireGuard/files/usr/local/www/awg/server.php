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
$tab_array[] = array(gettext("Status"), true, "index.php");
$tab_array[] = array(gettext("Server"), true, "server.php");
$tab_array[] = array(gettext("Clients"), false, "clients");
$tab_array[] = array(gettext("Client Export"), false, "export.php");
add_package_tabs("AWG", $tab_array);
display_top_tabs($tab_array);

include("foot.inc");
?>
