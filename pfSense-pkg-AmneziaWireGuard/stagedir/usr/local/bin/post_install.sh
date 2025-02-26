#!/bin/sh

# Log that post-installation tasks are starting
echo "Checking pre requisites ..."

# Check if bash is installed, suppressing output for a clean check
if ! pkg info -e bash > /dev/null 2>&1; then
  printf "\033[31mError: bash is not installed.\033[0m\n"
  printf "\033[31mAmnezia Wireguard (awg-quick) may not run properly.\033[0m\n"
  printf "\033[31mPlease install bash by running:\n pkg install -y bash \nand try again.\033[0m\n"
  # Optionally, you can auto-install bash by uncommenting the following line:
  # pkg install -y bash >> /var/log/messages 2>&1
  exit 1
else
  echo "bash is installed: OK"
fi

# Check if wg exists
if command -v wg >/dev/null 2>&1; then
  echo "wg found at: $(command -v wg)"
  
  # Backup the existing wg binary to awg1
  cp /usr/bin/wg /usr/bin/awg1
  echo "Backup created: /usr/bin/awg1"

  # Copy the custom awg binary over to /usr/bin/wg
  cp /usr/local/bin/awg /usr/bin/wg
  echo "Custom awg binary installed to /usr/bin/wg"
else
  echo "wg not found. Skipping wg backup and update."
fi

# If nothing failed, indicate that the environment is ready
echo "Amnezia Wireguard is ready"
