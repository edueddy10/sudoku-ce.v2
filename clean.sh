#!/bin/bash

echo "Cleaning up..."
docker compose down -v
docker system prune -f
echo "Cleanup complete."