#!/bin/bash
#
# vim:ft=sh

. ./.env.local
uuid=0X3600303238511239343734
uid=1
uname=u1

api=auth/tToken
# curl -d key=$STAMP_APP_KEY -d secret=$STAMP_APP_SECRET "$QSTAMP_API_URL/$api"
# curl "$QSTAMP_API_URL/$api?key=$STAMP_APP_KEY&secret=$STAMP_APP_SECRET"

api=device/list
# curl -H "tToken: $stamp_token" "$QSTAMP_API_URL/$api"

api=finger/list
# curl -H "tToken: $stamp_token" "$QSTAMP_API_URL/$api" -d "uuid=$uuid"

api=device/model
# curl -H "tToken: $stamp_token" "$QSTAMP_API_URL/$api" -d "uuid=$uuid&model=0"

api=finger/add
# curl -H "tToken: $stamp_token" "$QSTAMP_API_URL/$api" -d "userId=$uid&username=houfei&uuid=$uuid"

api=finger/del
# curl -H "tToken: $stamp_token" "$QSTAMP_API_URL/$api" -d "userId=$uid&uuid=$uuid"

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

api=wifi/list
# curl -H "tToken: $stamp_token" "$QSTAMP_API_URL/$api?uuid=$uuid" # -d "uuid=$uuid"
