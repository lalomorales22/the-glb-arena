# 🎪 GLB Arena - Quick Start Guide

## Starting the Game

### Easy Method (Recommended)

From the project root directory, simply run:

```bash
./glb-arena
```

or

```bash
./start.sh
```

Both commands do the same thing - they start both servers automatically.

### What It Does

The startup script:
1. **Starts Backend** - Python FastAPI server on `http://localhost:8001`
2. **Starts Frontend** - PHP development server on `http://localhost:8000`
3. **Displays URLs** - Shows where to access the game and API docs
4. **Auto-cleanup** - Stops both servers when you press `Ctrl+C`

### Output Example

```
========================================
  🎪 GLB Arena - Championship Royale
========================================

[1/2] Starting backend server...
      Port: http://localhost:8001
      Docs: http://localhost:8001/docs
✓ Backend started (PID: 12345)

[2/2] Starting frontend server...
      Port: http://localhost:8000
      Game:  http://localhost:8000/index.php
✓ Frontend started (PID: 12346)

========================================
  ✓ All servers running!
========================================

Backend:  http://localhost:8001
Frontend: http://localhost:8000

Press Ctrl+C to stop all servers
```

## Manual Method (If Needed)

If you prefer to start servers separately:

**Terminal 1 - Backend:**
```bash
cd backend
python main.py
```

**Terminal 2 - Frontend:**
```bash
php -S localhost:8000
```

Then open: `http://localhost:8000/index.php`

## Accessing the Game

1. **Game**: `http://localhost:8000/index.php`
2. **API Docs**: `http://localhost:8001/docs`
3. **Analytics**: `http://localhost:8000/analytics.php`
4. **Fighter Trainer**: `http://localhost:8000/fighter-trainer.php`

## Stopping the Servers

Press `Ctrl+C` in the terminal running `./glb-arena` to stop both servers gracefully.

## Troubleshooting

### "Permission denied" when running `./glb-arena`

Make the script executable:
```bash
chmod +x start.sh glb-arena
```

### Port already in use

If ports 8000 or 8001 are already in use, kill the existing process:

```bash
# Find what's using port 8001
lsof -i :8001

# Kill the process (replace PID with actual process ID)
kill -9 <PID>
```

Then try again.

### Backend fails to start

Make sure you're in the correct directory:
```bash
cd /Users/megabrain/Desktop/the-glb-arena
./glb-arena
```

---

**Happy fighting!** 🥊⚔️
