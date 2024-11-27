PACKAGE=pfSense-pkg-AmneziaWireGuard
VERSION=1.0.0
CATEGORIES=	net
MASTER_SITES=	# empty
DISTFILES=	# empty
EXTRACT_ONLY=	# empty
MAINTAINER=ptwizere@qtglobalsoftwareltd.com
COMMENT=Amnezia-WireGuard VPN integration for pfSense

# Directories
PREFIX=/usr/local
FILESDIR =./files
PLIST=./pkg-plist

NO_BUILD=	yes
NO_MTREE=	yes

SUB_FILES=	pkg-install pkg-deinstall
SUB_LIST=	PORTNAME=${PACKAGE}


# .include <bsd.port.mk>


do-extract:
	${MKDIR} ${WRKSRC}

# Post-install commands
post-install:
	@echo "Installing Amnezia-WireGuard package..."
	@mkdir -p ${PREFIX}/pkg/amneziawireguard
	@mkdir -p ${PREFIX}/www/widgets
	@mkdir -p ${PREFIX}/xml
	@cp -R ${FILESDIR}/${PREFIX}/pkg/* ${PREFIX}/pkg/amneziawireguard/
	@cp -R ${FILESDIR}/${PREFIX}/www/* ${PREFIX}/www/
	@cp -R ${FILESDIR}/${PREFIX}/pkg/*.xml ${PREFIX}/pkg/
	@echo "Installation complete."
