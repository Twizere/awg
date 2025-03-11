<?php



$pgtitle = array("VPN", "Amenezia WG");
$shortcut_section = "Amnezia WG";

$tab_array = array();
$tab_array[] = array(gettext("Status"), $active_tab == "Status", "status_amneziawg.php");
$tab_array[] = array(gettext("Server"), $active_tab == "Server", "server.php");
$tab_array[] = array(gettext("Clients"), $active_tab == "Clients", "clients.php");
$tab_array[] = array(gettext("Client Export"), $active_tab == "Export", "export.php");
add_package_tabs("AWG", $tab_array);
display_top_tabs($tab_array);

?>
