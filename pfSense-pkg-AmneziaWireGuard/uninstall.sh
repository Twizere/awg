#!/bin/sh
# Uninstall script for pfSense-pkg-Amneziawireguard

echo "Uninstalling Amnezia-WireGuard package..."

# Remove files
echo " - Removing files..."
rm -f /etc/inc/priv/amneziawireguard.priv.inc 
rm -f /usr/local/pkg/amneziawireguard*.xml
rm -f /usr/local/pkg/amneziawireguard/*.*
rm -f /usr/local/pkg/amneziawireguard/classes/*.*
rm -f /usr/local/pkg/amneziawireguard/includes/*.*
rm -f /usr/local/share/pfSense-pkg-Amneziawireguard/*.xml
rm -f /usr/local/www/awg/js/*.js
rm -rf /usr/local/www/awg/*
rm -rf /usr/local/www/shortcuts/*

# Remove directories (only if empty, to avoid unintended deletions)
echo " - Removing directories..."
rmdir /etc/inc/priv
rmdir /usr/local/pkg/amneziawireguard/classes
rmdir /usr/local/pkg/amneziawireguard/includes
rmdir /usr/local/share/pfSense-pkg-Amneziawireguard
rmdir /usr/local/www/awg/js
rmdir /usr/local/www/widgets
rmdir /usr/local/www/shortcuts

echo "Uninstallation complete."
