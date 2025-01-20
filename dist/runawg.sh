#!/bin/bash

# Define variables
AWG_CONFIG_DIR="/etc/amnezia/amneziawg"
AWG_CONFIG_FILE="$AWG_CONFIG_DIR/awg0.conf"
IP="10.9.9.1/24"        # Your network IP range
PORT="46999"            # Your desired port for the AWG configuration
CLIENT_NAME="my_phone"  # Example client name, you can modify as needed

# Step 1: Generate the main AWG config
echo "Generating main AWG config..."
./awgcfg --make "$AWG_CONFIG_FILE" -i "$IP" -p "$PORT"
if [ $? -ne 0 ]; then
    echo "Error: Failed to generate AWG config."
    exit 1
fi

# Step 2: Generate the config template for clients and QR codes
echo "Generating client config template..."
./awgcfg --create
if [ $? -ne 0 ]; then
    echo "Error: Failed to generate config template."
    exit 1
fi

# Step 3: Add a couple of clients to the main AWG config
echo "Adding clients to AWG config..."
./awgcfg -a "$CLIENT_NAME"
if [ $? -ne 0 ]; then
    echo "Error: Failed to add client $CLIENT_NAME to the AWG config."
    exit 1
fi

# Step 4: Generate client configs and QR codes
echo "Generating client configs and QR codes..."
./awgcfg -c -q
if [ $? -ne 0 ]; then
    echo "Error: Failed to generate client configs and QR codes."
    exit 1
fi

echo "AWG configuration and client setup complete!"
