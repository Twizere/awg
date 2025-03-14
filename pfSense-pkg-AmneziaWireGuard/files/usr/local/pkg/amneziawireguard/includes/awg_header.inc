<?php

$pgtitle = array("VPN", "Amenezia WG");
$shortcut_section = "Amnezia WG";
$tab_array = array();
$tab_array[] = array(gettext("Status"), $active_tab == "Status", "/awg/status_amneziawg.php");
$tab_array[] = array(gettext("Servers"), $active_tab == "Servers", "/awg/awg_tunnels.php");
$tab_array[] = array(gettext("Clients"), $active_tab == "Clients", "/awg/awg_peers.php");
$tab_array[] = array(gettext("Settings"), $active_tab == "Settings", "/awg/awg_settings.php");
$tab_array[] = array(gettext("Client Export"), $active_tab == "Export", "export.php");
add_package_tabs("AWG", $tab_array);

?>
<script>
	function wgRegTrimHandler() {
		$('body').on('change', '.trim', function () {
			$(this).val($(this).val().replace(/\s/g, ''));
		});
	}
</script>
