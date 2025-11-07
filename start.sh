#!/bin/bash

# GLB Arena - Complete Startup Script
# Starts both the backend (Python FastAPI) and frontend (PHP) servers

set -e  # Exit on error

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Configuration
BACKEND_PORT=8001
FRONTEND_PORT=8000
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  🎪 GLB Arena - Championship Royale${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Function to cleanup on exit
cleanup() {
    echo ""
    echo -e "${YELLOW}Shutting down servers...${NC}"

    # Kill backend if it's running
    if [ ! -z "$BACKEND_PID" ]; then
        echo -e "${YELLOW}Stopping backend (PID: $BACKEND_PID)...${NC}"
        kill $BACKEND_PID 2>/dev/null || true
    fi

    # Kill frontend if it's running
    if [ ! -z "$FRONTEND_PID" ]; then
        echo -e "${YELLOW}Stopping frontend (PID: $FRONTEND_PID)...${NC}"
        kill $FRONTEND_PID 2>/dev/null || true
    fi

    echo -e "${GREEN}Servers stopped.${NC}"
    exit 0
}

# Set trap to cleanup on exit
trap cleanup EXIT INT TERM

# Check if we're in the right directory
if [ ! -f "$PROJECT_DIR/backend/main.py" ] || [ ! -f "$PROJECT_DIR/index.php" ]; then
    echo -e "${RED}Error: This script must be run from the GLB Arena root directory${NC}"
    exit 1
fi

# Start Backend (Python)
echo -e "${BLUE}[1/2]${NC} Starting backend server..."
echo -e "      Port: ${YELLOW}http://localhost:${BACKEND_PORT}${NC}"
echo -e "      Docs: ${YELLOW}http://localhost:${BACKEND_PORT}/docs${NC}"

cd "$PROJECT_DIR/backend"
python main.py &
BACKEND_PID=$!

# Give backend time to start
sleep 3

# Check if backend started successfully
if ! kill -0 $BACKEND_PID 2>/dev/null; then
    echo -e "${RED}✗ Backend failed to start${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Backend started (PID: $BACKEND_PID)${NC}"
echo ""

# Start Frontend (PHP)
echo -e "${BLUE}[2/2]${NC} Starting frontend server..."
echo -e "      Port: ${YELLOW}http://localhost:${FRONTEND_PORT}${NC}"
echo -e "      Game:  ${YELLOW}http://localhost:${FRONTEND_PORT}/index.php${NC}"

cd "$PROJECT_DIR"
php -S localhost:${FRONTEND_PORT} &
FRONTEND_PID=$!

# Give frontend time to start
sleep 2

# Check if frontend started successfully
if ! kill -0 $FRONTEND_PID 2>/dev/null; then
    echo -e "${RED}✗ Frontend failed to start${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Frontend started (PID: $FRONTEND_PID)${NC}"
echo ""

# Display status
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  ✓ All servers running!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "Backend:  ${YELLOW}http://localhost:${BACKEND_PORT}${NC}"
echo -e "Frontend: ${YELLOW}http://localhost:${FRONTEND_PORT}${NC}"
echo ""
echo -e "${YELLOW}Press Ctrl+C to stop all servers${NC}"
echo ""

# Keep the script running and forward signals
wait
