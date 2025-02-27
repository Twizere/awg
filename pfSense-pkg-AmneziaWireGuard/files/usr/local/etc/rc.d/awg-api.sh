#!/bin/sh

# PROVIDE: awg-api
# REQUIRE: NETWORKING
# BEFORE:  DAEMON
# KEYWORD: shutdown

. /etc/rc.subr

name="awg-api"
rcvar="awg_api_enable"

command="/root/awg/awg-api"  # Full path to your executable
command_args=""  # This will be populated dynamically from the command line

start_precmd="awg_api_prestart"

awg_api_prestart() {
    # Custom pre-start logic if needed
    echo "Starting awg-api with arguments: ${command_args}"
}

# Define service functions
start_cmd="${command} ${command_args}"

load_rc_config $name

# If arguments are passed, use them
if [ -n "$2" ]; then
    command_args="$2"
fi

run_rc_command "$1"
