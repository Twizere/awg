#!/bin/sh

# Log that post_install is being executed
echo "Running post-installation tasks..." 
# Check if bash is installed
if ! pkg info -e bash; then
  echo "bash is not installed, Install it first and try again later."
  #pkg install -y bash >> /var/log/messages 2>&1

else
  echo "bash is already installed."
