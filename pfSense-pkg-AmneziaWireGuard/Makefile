PACKAGE=pfSense-pkg-AmneziaWireGuard
VERSION=1.0.0
MAINTAINER=your-email@example.com
COMMENT=Amnezia-WireGuard VPN integration for pfSense

# Directories
PREFIX=/usr/local
PLIST=./pkg-plist

.include <bsd.port.mk>

# Post-install commands
post-install:
	@echo "Installing Amnezia-WireGuard package..."
	@mkdir -p ${PREFIX}/pkg/amneziawireguard
	@mkdir -p ${PREFIX}/www/widgets
	@mkdir -p ${PREFIX}/xml
	@cp -R ./pkg/* ${PREFIX}/pkg/amneziawireguard/
	@cp -R ./www/* ${PREFIX}/www/
	@cp -R ./xml/* ${PREFIX}/xml/
	@echo "Installation complete."
