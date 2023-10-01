#!/bin/bash



# Load environment variables from the file
source .env



# Start PHP server
php -S localhost:8080 ./src/index.dev.php
