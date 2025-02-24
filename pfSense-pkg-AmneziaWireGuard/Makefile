PACKAGE=pfSense-pkg-AmneziaWireGuard
VERSION=1.0.0

PREFIX=/usr/local
FILESDIR=./files

NO_BUILD=yes
NO_MTREE=yes

do-install:
	@echo "Installing Amnezia-WireGuard package..."
	
	# Create necessary directories
	@install -d /etc/inc/priv
	@install -d ${PREFIX}/bin
	@install -d ${PREFIX}/pkg/amneziawireguard/classes
	@install -d ${PREFIX}/pkg/amneziawireguard/includes
	@install -d ${PREFIX}/share/${PACKAGE}
	@install -d ${PREFIX}/www/awg/js
	@install -d ${PREFIX}/www/widgets
	@install -d ${PREFIX}/www/shortcuts

	# Copy necessary files
	@cp -R ${FILESDIR}/etc/inc/priv/*.priv.inc /etc/inc/priv/
	@cp -R ${FILESDIR}${PREFIX}/share/${PACKAGE}/*.xml ${PREFIX}/share/${PACKAGE}/
	@install ${FILESDIR}${PREFIX}/share/${PACKAGE}/info.xml ${PREFIX}/share/pfSense/menu/awg.xml
	@cp -R ${FILESDIR}${PREFIX}/www/awg/js/*.js ${PREFIX}/www/awg/js/
	@cp -R ${FILESDIR}${PREFIX}/www/awg/* ${PREFIX}/www/awg/
	@cp -R ${FILESDIR}${PREFIX}/www/shortcuts/* ${PREFIX}/www/shortcuts/

	# Copying the executable
	@cp -R ${FILESDIR}${PREFIX}/bin/* ${PREFIX}/bin/
	@chmod +x ${PREFIX}/bin/*

	# Set permissions
	@chmod -R 755 ${PREFIX}/pkg/amneziawireguard
	@chmod -R 755 ${PREFIX}/www/awg/js
	@chmod -R 755 ${PREFIX}/pkg

	@echo "Installation complete."

.PHONY: do-install
