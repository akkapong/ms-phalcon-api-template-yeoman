#!/bin/sh

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

echo $DIR

docker rm -f [dockerNmae]_api
docker rm -f [dockerNmae]_mongo
docker-compose rm

docker-compose build [dockerNmae]_api
WEB_ID=$(docker-compose up -d [dockerNmae]_api)

docker-compose build [dockerNmae]_mongo
DB_ID=$(docker-compose up -d [dockerNmae]_mongo)

sleep 3

docker exec -it [dockerNmae]_api sh /start_script.sh
docker exec -it [dockerNmae]_mongo sh /start_script.sh
