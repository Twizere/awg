#!/bin/bash

# Default variables
AWG_CONFIG_DIR="/etc/amnezia/amneziawg"
AWG_CONFIG_FILE="$AWG_CONFIG_DIR/awg0.conf"
MAIN_CONFIG_FILE="./.main.config"
IP="10.9.9.1/24"        # Your network IP range
PORT="46999"            # Default port
CLIENT_NAME="my_phone"  # Default client name
FORCE_OVERWRITE=false   # Default: do not overwrite existing config

# Parse command-line arguments
while getopts "p:n:f" opt; do
    case "$opt" in
        p) PORT="$OPTARG" ;;
        n) CLIENT_NAME="$OPTARG" ;;
        f) FORCE_OVERWRITE=true ;;
        *) echo "Usage: $0 [-p port] [-n client_name] [-f]"; exit 1 ;;
    esac
done

# Check if the configuration file exists
if [ -f "$AWG_CONFIG_FILE" ]; then
    if [ "$FORCE_OVERWRITE" = true ]; then
        echo "Overwriting existing AWG config file: $AWG_CONFIG_FILE"
        rm -f "$AWG_CONFIG_FILE" "$MAIN_CONFIG_FILE"
    else
        read -p "File $AWG_CONFIG_FILE exists. Overwrite? (y/n): " response
        case "$response" in
            [yY][eE][sS]|[yY]) 
                echo "Overwriting existing AWG config file: $AWG_CONFIG_FILE"
                rm -f "$AWG_CONFIG_FILE" "$MAIN_CONFIG_FILE"
                ;;
            *)
                echo "Aborting operation. Existing config file will not be overwritten."
                exit 1
                ;;
        esac
    fi
fi

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

# Step 3: Add a client to the main AWG config
echo "Adding client to AWG config..."
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
echo "Port: $PORT, Client Name: $CLIENT_NAME"
