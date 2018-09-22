#!/bin/bash

BASEDIR=$(dirname "$0")
BASEDIR="$BASEDIR/.."

(
    cd $BASEDIR
    cp -n .env.dist .env
    docker-compose build 1> /dev/null &&
    docker-compose run web sh -c "composer install" 1> /dev/null &&
    chmod a+w data/images &&
    echo "Setup complete" &&
    echo "Run 'docker-compose up' to start the application"
)
