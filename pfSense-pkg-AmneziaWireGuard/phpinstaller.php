<?php


$package_name = 'amneziawireguard'; 
$package = '/usr/local/pkg/'.$package_name.'.xml';

// Load package manager functions if needed
require_once 'pkg-utils.inc';

global $g;
echo "Package Prefix :" .g_get('pkg_prefix')."\n";

// Check if the package file exists
if (file_exists($package)) {
    // Install the package using the XML descriptor
    install_package_xml($package);
    echo "Package installation initiated for: {$package}\n";
} else {
    echo "Package XML file not found: {$package}\n";
}

?>