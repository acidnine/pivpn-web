#!/bin/bash

user=$1
password=$2
config_file="/etc/pivpn/openvpn/setupVars.conf"
if [ -f "$config_file" ]; then
    userPivpn=$(sudo cat /etc/pivpn/openvpn/setupVars.conf | grep install_user | sed s/install_user=//)
else
    userPivpn=$(sudo cat /etc/pivpn/wireguard/setupVars.conf | grep install_user | sed s/install_user=//)
fi

if echo "$password" | su -c "echo" "$user" >/dev/null 2>&1 && [ "$user" == "$userPivpn" ]; then
    echo "Authenticated"
else
    echo "Not Authenticated"
fi
