PACKAGE=pfSense-pkg-Amneziawireguard
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
	@install -d ${PREFIX}/bin
	@install -d ${PREFIX}/pkg/amneziawireguard/classes
	@install -d ${PREFIX}/pkg/amneziawireguard/includes
	@install -d ${PREFIX}/share/${PACKAGE}
	@install -d ${PREFIX}/share/${PACKAGE}/menu
	@install -d ${PREFIX}/www/awg/js
	@install -d ${PREFIX}/www/awg/api
	@install -d ${PREFIX}/www/widgets
	@install -d ${PREFIX}/www/shortcuts
	@echo "   Directories created ✅"

	# Copy necessary files
	@echo " - Copying files..."
	#@cp -R ${FILESDIR}${PREFIX}/pkg/*.xml ${PREFIX}/pkg/
	@cp -R ${FILESDIR}${PREFIX}/pkg/amneziawireguard/includes/* ${PREFIX}/pkg/amneziawireguard/includes/

	# @cp -R ${FILESDIR}${PREFIX}/share/${PACKAGE}/*.xml ${PREFIX}/share/${PACKAGE}/
	@install  ${FILESDIR}${PREFIX}/share/pfSense/menu/info.xml ${PREFIX}/share/pfSense/menu/info.xml

	@cp -R ${FILESDIR}${PREFIX}/www/awg/js/*.js ${PREFIX}/www/awg/js/
	#@cp -R ${FILESDIR}${PREFIX}/www/widgets ${PREFIX}/www/widgets

	#copying the executable
	@cp -R ${FILESDIR}${PREFIX}/bin/* ${PREFIX}/bin/
	@chmod +x ${PREFIX}/bin/*
	@echo "   Files copied ✅"

	#copying the executable
	@cp -R ${FILESDIR}${PREFIX}/etc/rc.d/* ${PREFIX}/etc/rc.d/
	@chmod +x ${PREFIX}/etc/rc.d/*
	@echo 'awg_api_enable="YES"' >> /etc/rc.conf.d/awg-api
	@echo 'awg_quick_enable="YES"'  >> /etc/rc.conf.d/wg_quick
	@echo "   Service is set ✅"

	@cp -R ${FILESDIR}${PREFIX}/www/awg/* ${PREFIX}/www/awg/
	@cp -R ${FILESDIR}${PREFIX}/www/awg/api/* ${PREFIX}/www/awg/api
	@echo " WEB & API are set ✅"
	
	@cp -R ${FILESDIR}${PREFIX}/www/widgets/include/*.inc ${PREFIX}/www/widgets/include/
	@cp -R ${FILESDIR}${PREFIX}/www/widgets/widgets/*.widget.php ${PREFIX}/www/widgets/widgets/
	@echo " Widget are set ✅"

	@php -f ${PREFIX}/pkg/amneziawireguard/includes/wg_service.inc install
		@echo "pfSense Config init is set ✅"
	#shortcuts
	@cp -R ${FILESDIR}${PREFIX}/www/shortcuts/* ${PREFIX}/www/shortcuts/

	@echo " - Setting permissions..."
	@chmod -R 755 ${PREFIX}/pkg/amneziawireguard
	@chmod -R 755 ${PREFIX}/www/awg/js
	@chmod -R 755 ${PREFIX}/pkg
	@echo "   Permissions set ✅"

	@echo "Installation complete."
