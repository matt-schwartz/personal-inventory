#!/bin/sh
BASEDIR=$(dirname "$0")
BASEDIR="$BASEDIR/.."

cd $BASEDIR

if [ -d "$BASEDIR/vendor" ]; then
    docker-compose up
else
    cp --no-clobber .env.dist .env && 
    docker-compose build &&
    docker-compose up -d && 
    docker-compose exec web sh -c "composer install" &&
    echo "Setup complete"
    echo "When you'd like to stop the application, run 'docker-compose down'"
fi
