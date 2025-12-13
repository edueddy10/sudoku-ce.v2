#!/bin/bash

echo "Setting up Sudoku Game..."
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "Error: Docker is not running. Please start Docker Desktop first."
    exit 1
fi

# Clean up old containers
echo "Cleaning up old containers..."
docker compose down 2>/dev/null

# Build and start
echo "Building and starting containers..."
docker compose up -d --build

# Wait for services
echo "Waiting for services to be ready..."
sleep 15

# Check services
echo ""
echo "Checking services..."
if curl -s http://localhost:8080 > /dev/null; then
    echo "✓ Web server is running at http://localhost:8080"
else
    echo "✗ Web server failed to start"
fi

echo ""
echo "Setup complete!"
echo ""
echo "Access URLs:"
echo "- Game:      http://localhost:8080"
echo "- phpMyAdmin: http://localhost:8081"
echo ""
echo "Database credentials:"
echo "- Server:   db"
echo "- Username: sudoku_user"
echo "- Password: sudoku_password"
echo ""
echo "To stop: docker compose down"
echo "To view logs: docker compose logs -f"
