PACKAGE=pfSense-pkg-amneziawireguard
VERSION=1.0.0
CATEGORIES=net
MASTER_SITES=	# empty
DISTFILES=	# empty
EXTRACT_ONLY=	# empty
MAINTAINER=ptwizere@qtglobalsoftwareltd.com
COMMENT=Amnezia-WireGuard VPN integration for pfSense

# Directories
PREFIX=/usr/local
FILESDIR=./files
PLIST=./pkg-plist

NO_BUILD=yes
NO_MTREE=yes

SUB_FILES=pkg-install pkg-deinstall
SUB_LIST=PORTNAME=${PACKAGE}

# .include <bsd.port.mk>

# Extract phase
do-extract:
	${MKDIR} ${WRKSRC}

# Post-install commands
install:
	@echo "Installing Amnezia-WireGuard package..."
	
	# Prepare directory tree
	@echo " - Creating necessary directories..."
	@install -d /etc/inc/priv
	@install -d ${PREFIX}/bin
	@install -d ${PREFIX}/pkg/amneziawireguard/classes
	@install -d ${PREFIX}/pkg/amneziawireguard/includes
	@install -d ${PREFIX}/share/${PACKAGE}
	@install -d ${PREFIX}/www/awg/js
	@install -d ${PREFIX}/www/widgets
	@install -d ${PREFIX}/www/shortcuts
	@echo "   Directories created ✅"

	# Copy necessary files
	@echo " - Copying files..."
	@cp -R ${FILESDIR}/etc/inc/priv/*.priv.inc /etc/inc/priv/

	#@cp -R ${FILESDIR}${PREFIX}/pkg/*.xml ${PREFIX}/pkg/
	#@cp -R ${FILESDIR}${PREFIX}/pkg/amneziawireguard/* ${PREFIX}/pkg/amneziawireguard/

	@cp -R ${FILESDIR}${PREFIX}/share/${PACKAGE}/*.xml ${PREFIX}/share/${PACKAGE}/
	@install  ${FILESDIR}${PREFIX}/share/${PACKAGE}/info.xml ${PREFIX}/share/${PACKAGE}/menu/info.xml

	@cp -R ${FILESDIR}${PREFIX}/www/awg/js/*.js ${PREFIX}/www/awg/js/
	#@cp -R ${FILESDIR}${PREFIX}/www/widgets ${PREFIX}/www/widgets

	//copying the executable
	@cp -R ${FILESDIR}${PREFIX}/bin/* ${PREFIX}/bin/
	@chmod +x ${PREFIX}/bin/*
	
	@echo "   Files copied ✅"

	@cp -R ${FILESDIR}${PREFIX}/www/awg/* ${PREFIX}/www/awg/

	#shortcuts
	@cp -R ${FILESDIR}${PREFIX}/www/shortcuts/* ${PREFIX}/www/shortcuts/

	# Ensure proper file permissions
	@echo " - Setting permissions..."
	@chmod -R 755 ${PREFIX}/pkg/amneziawireguard
	@chmod -R 755 ${PREFIX}/www/awg/js
	@chmod -R 755 ${PREFIX}/pkg
	@echo "   Permissions set ✅"

	@echo "Installation complete."
