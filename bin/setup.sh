#!/bin/sh

BASEDIR=$(dirname "$0")
BASEDIR="$BASEDIR/.."

(
    cd $BASEDIR &&
    cp --no-clobber .env.dist .env && 
    docker-compose build 1> /dev/null &&
    docker-compose run web sh -c "composer install" 1> /dev/null &&
    echo "Setup complete"
    echo "Run 'docker-compose up' to start the application"
)
