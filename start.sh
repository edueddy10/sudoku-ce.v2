# ~/sudoku-game/start.sh
#!/bin/bash

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== Sudoku Game Local Setup ===${NC}"
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}Docker is not running. Please start Docker Desktop first.${NC}"
    exit 1
fi

# Build and start containers
echo -e "${YELLOW}Building Docker containers...${NC}"
docker-compose up -d --build

echo -e "${YELLOW}Waiting for services to start...${NC}"
sleep 10

# Check if services are running
if curl -s http://localhost:8080 > /dev/null; then
    echo -e "${GREEN}✓ Web server is running${NC}"
else
    echo -e "${RED}✗ Web server failed to start${NC}"
fi

# Display URLs
echo ""
echo -e "${GREEN}===============================${NC}"
echo -e "${GREEN}Your Sudoku Game is ready!${NC}"
echo ""
echo -e "Game URL: ${YELLOW}http://localhost:8080${NC}"
echo -e "phpMyAdmin: ${YELLOW}http://localhost:8081${NC}"
echo ""
echo -e "phpMyAdmin Login:"
echo -e "  Server: ${YELLOW}db${NC}"
echo -e "  Username: ${YELLOW}sudoku_user${NC}"
echo -e "  Password: ${YELLOW}sudoku_password${NC}"
echo ""
echo -e "${GREEN}To stop the game: ${YELLOW}docker-compose down${NC}"
echo -e "${GREEN}To view logs: ${YELLOW}docker-compose logs -f${NC}"
echo -e "${GREEN}===============================${NC}"