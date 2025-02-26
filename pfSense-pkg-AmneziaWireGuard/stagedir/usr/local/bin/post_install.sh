#!/bin/sh

# Log that post-installation tasks are starting
echo "Running post-installation tasks..."

# Check if bash is installed, suppressing output for a clean check
if ! pkg info -e bash > /dev/null 2>&1; then
  echo "Error: bash is not installed."
  echo "Amnezia Wireguard (awg-quick) may not run properly."
  echo "Please install bash and try again."
  # Optionally, you can auto-install bash by uncommenting the following line:
  # pkg install -y bash >> /var/log/messages 2>&1
  exit 1
else
  echo "bash is already installed."
fi