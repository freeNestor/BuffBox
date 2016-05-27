#!/bin/bash

. /etc/profile

HOSTNAME=$(hostname)
kernelName=$(uname -s)
kernelver=""
machineType=""
osDescription=""

res=""

if [ "$kernelName" = "Linux" ];then
    kernelver=$(uname -r)
    machineType=$(uname -o)
    osDescription=$(lsb_release -d 2>/dev/null|awk -F: '{print $2}')
    
elif [ "$kernelName" = "AIX" ];then
    kernelver="Unknown"
    machineType=$(uname -M)
    osDescription=$(oslevel)
else
    kernelver="Unknown"
    machineType="Unsupport Type"
    osDescription="Unknown"
fi
res="{\"hostname\":\"$HOSTNAME\",\"kernel\":\"$kernelName\",\"kernelv\":\"$kernelver\",\"os\":\"$osDescription\",\"vendor\":\"$machineType\"}"
echo $res
