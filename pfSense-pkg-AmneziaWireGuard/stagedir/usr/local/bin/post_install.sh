#!/bin/sh

# Log that post-installation tasks are starting
echo "Cheking pre requisites ..."

# Check if bash is installed, suppressing output for a clean check
if ! pkg info -e bash > /dev/null 2>&1; then
  printf "\033[31mError: bash is not installed.\033[0m\n"
  printf "\033[31mAmnezia Wireguard (awg-quick) may not run properly.\033[0m\n"
  printf "\033[31mPlease install bash by running \n pkg install -y bash \n and Try again.\033[0m\n"
  # Optionally, you can auto-install bash by uncommenting the following line:
  # pkg install -y bash >> /var/log/messages 2>&1
  exit 1
else
  echo "OK"
fi
