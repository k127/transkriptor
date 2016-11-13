#!/bin/bash
# 2015-01-08 <klaus.hartl@digitalmobil.com>
export ENV=dev
source ~/.bashrc 2> /dev/null || source ~/.bash_profile
if [[ "Stopped" -eq "$( docker-machine status default )" ]]; then
	echo "default docker-machine is stopped."
else
	eval "$(docker-machine env default)"
fi
