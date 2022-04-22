#!/bin/bash
#
# vim:ft=sh

. ./.env.local

# api=auth/tToken
# curl -d key=$stamp_app_key -d secret=$stamp_app_secret "$api_url/$api"

# api=device/list
# curl -H "tToken: $stamp_token" "$api_url/$api"

uuid=0X3600303238511239343734
uid=1
uname=u1

api=finger/list
# curl -H "tToken: $stamp_token" "$api_url/$api" -d "uuid=$uuid"

api=device/model
# curl -H "tToken: $stamp_token" "$api_url/$api" -d "uuid=$uuid&model=0"

api=finger/add
# curl -H "tToken: $stamp_token" "$api_url/$api" -d "userId=$uid&username=houfei&uuid=$uuid"

api=application/push
# curl -H "tToken: $stamp_token" "$api_url/$api" -d "applicationId=11113&userId=$uid&totalCount=5&needCount=0&uuid=$uuid"

api=device/idUse
# curl -H "tToken: $stamp_token" "$api_url/$api" -d "userId=$uid&username=$uname&uuid=$uuid"

api=record/list
# curl -H "tToken: $stamp_token" "$api_url/$api" -d "uuid=$uuid"

api=device/sleep
# curl -H "tToken: $stamp_token" "$api_url/$api" -d "uuid=$uuid&sleep=30"

api=finger/clear
# curl -H "tToken: $stamp_token" "$api_url/$api" -d "uuid=$uuid"
