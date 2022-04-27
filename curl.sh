#!/bin/bash
#
# vim:ft=sh

. ./.env.local
uuid=0X3600303238511239343734
uid=1
uname=u1

api=auth/tToken
# curl -d key=$stamp_app_key -d secret=$stamp_app_secret "$QSTAMP_API_URL/$api"
# curl "$QSTAMP_API_URL/$api?key=$stamp_app_key&secret=$stamp_app_secret"

api=device/list
# curl -H "tToken: $stamp_token" "$QSTAMP_API_URL/$api"

api=finger/list
# curl -H "tToken: $stamp_token" "$QSTAMP_API_URL/$api" -d "uuid=$uuid"

api=device/model
# curl -H "tToken: $stamp_token" "$QSTAMP_API_URL/$api" -d "uuid=$uuid&model=0"

api=finger/add
# curl -H "tToken: $stamp_token" "$QSTAMP_API_URL/$api" -d "userId=$uid&username=houfei&uuid=$uuid"

api=application/push
# curl -H "tToken: $stamp_token" "$QSTAMP_API_URL/$api" -d "applicationId=2147483647&userId=$uid&totalCount=5&needCount=0&uuid=$uuid"

api=device/idUse
# curl -H "tToken: $stamp_token" "$QSTAMP_API_URL/$api" -d "userId=$uid&username=$uname&uuid=$uuid"

api=record/list
# curl -H "tToken: $stamp_token" "$QSTAMP_API_URL/$api" -d "uuid=$uuid"

api=device/sleep
# curl -H "tToken: $stamp_token" "$QSTAMP_API_URL/$api" -d "uuid=$uuid&sleep=30"

api=finger/clear
# curl -H "tToken: $stamp_token" "$QSTAMP_API_URL/$api" -d "uuid=$uuid"
