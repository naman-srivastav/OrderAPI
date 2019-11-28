    
#!/bin/bash -e

PROJECT_NAME=orders_php
echo " --Docker setup initiated -- "
docker-compose down -v && docker-compose up --build -d

check_dependencies() {
  [ -d /var/www/html/vendor ]
}

echo "-- Checking for dependencies --"
if ! check_dependencies; then
  echo "Installing dependencies..."
  docker exec $PROJECT_NAME composer install
  echo "Dependencies installed"
else
  echo "Updating dependencies..."
  docker exec $PROJECT_NAME composer update
  echo "Dependencies updated"
fi

docker exec orders_php bash -c 'chmod 777 -R /var/www/html'

echo " -- Migration initiated -- "
docker exec $PROJECT_NAME php artisan migrate
echo " -- Seeding initiated -- "
docker exec $PROJECT_NAME php artisan db:seed

echo "-- Initiatated all test cases --"
docker exec $PROJECT_NAME ./vendor/bin/phpunit ./tests/Unit
docker exec $PROJECT_NAME ./vendor/bin/phpunit ./tests/Integration
echo "-- All test cases done --"

