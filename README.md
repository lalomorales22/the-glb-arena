# 🎪 The GLB Arena - 3D Wrestling + AI Training

A **game + AI research platform** where neural network agents learn to fight in a 3D wrestling arena using reinforcement learning. Play interactive battles, train custom AI personalities, and watch agents improve through self-play.

**Live Features:** Game, Analytics Dashboard, Personality Trainer, AI Training System

---

## ⚡ Quick Start (ONE COMMAND)

```bash
chmod +x start.sh
./start.sh
```

Then open: **http://localhost:8000** in your browser

That's it! Both servers start automatically:
- 🎮 **Game Frontend** → http://localhost:8000
- 🤖 **AI Backend** → http://localhost:8001 (auto-running in background)

---

## 📋 What You Get

### 🎮 **Game Mode**
- Interactive 3D wrestling arena with real-time physics
- Control fighters with keyboard (WASD + SPACE)
- Play against AI opponents or watch AI battles
- Knockout animations and crowd reactions
- Automatic GLB model loading from `Insert-GLBS/` folder

### 🤖 **AI Training Mode**
- PPO (Proximal Policy Optimization) reinforcement learning
- Neural networks that learn fighting strategies
- Custom personality profiles (Aggression, Positioning, Targeting, etc.)
- Save/load trained models
- 100x faster headless training (no graphics)

### 📊 **Analytics Dashboard**
- Real-time fighter statistics
- Training progress curves
- Win rates and performance metrics
- Championship rankings
- Auto-refresh every 10 seconds

### 🥊 **Fighter Personality Trainer**
- Create custom AI personalities with 5 personality sliders
- Train specific fighters with custom parameters
- Monitor training progress in real-time
- Dynamically loads any GLB files from Insert-GLBS/

---

## 🚀 How to Run

### Option 1: Auto-Start (Easiest)
```bash
./start.sh
```

### Option 2: Manual (Multiple Terminals)
```bash
# Terminal 1: Frontend (PHP)
php -S localhost:8000

# Terminal 2: Backend (Python)
cd backend
python3 -m venv venv
source venv/bin/activate  # or `venv\Scripts\activate` on Windows
pip install -r requirements.txt
python main.py

# Terminal 3: Train (Optional)
cd backend
python example_training.py
```

---

## 🎮 Game Controls

| Action | Key |
|--------|-----|
| Move | WASD or Arrow Keys |
| Attack | SPACE |
| Select Fighter | Click from list |

---

## 🎨 Add Custom Fighters

1. Export or download a GLB 3D model
2. Place it in the **`Insert-GLBS/`** folder
3. Refresh the game (F5) or restart
4. Your fighter appears automatically in the game, trainer, and analytics!

---

## 📂 Project Structure

```
/the-glb-arena/
├── index.php                 # Game frontend
├── analytics.php             # Analytics dashboard
├── fighter-trainer.php       # Personality trainer
├── list-glb-files.php        # Dynamic GLB loader
├── start.sh                  # Auto-start script
│
├── Insert-GLBS/              # Put your GLB fighter models here!
│   ├── bacon.glb
│   ├── spongebob.glb
│   └── ... (add more!)
│
└── backend/                  # Python RL training system
    ├── main.py               # FastAPI server
    ├── requirements.txt      # Python dependencies
    ├── api/                  # REST API endpoints
    ├── rl/                   # Reinforcement learning (PPO)
    ├── simulation/           # Headless arena
    ├── database/             # SQLite database
    └── example_training.py   # Training example
```

---

## 🧠 How AI Training Works

1. **Observation** → Agent sees: position, health, enemy locations
2. **Action** → Agent chooses: move, attack, defend
3. **Reward** → +1 for survival, +10 for knockout, -1 for damage
4. **Learning** → PPO algorithm updates neural network weights
5. **Repeat** → After 20-100 epochs, agent becomes skilled fighter

Agents learn:
- ✅ Strategic positioning (stay in ring center)
- ✅ Targeting (focus weakest opponent)
- ✅ Risk management (attack when safe)
- ✅ Crowd control (fight multiple opponents)

---

## 🔗 REST API Endpoints

Key endpoints for training and data:

```
GET  /api/fighters                    # List all fighters
POST /api/training-jobs               # Start training
GET  /api/training-jobs/{id}          # Check training progress
GET  /api/fighters/{id}/stats         # Fighter statistics
GET  /api/fighters/{id}/episodes      # Battle history
```

**Full API docs:** http://localhost:8001/docs

---

## 📝 Version Status

**v3.2** - Phase 2 Complete ✅
- ✅ Game frontend with 3D graphics
- ✅ PPO reinforcement learning system
- ✅ SQLite database with persistent storage
- ✅ REST API with 13+ endpoints
- ✅ Analytics dashboard with real-time charts
- ✅ Fighter personality trainer
- ✅ FastAPI backend (modern best practices)
- ✅ Headless simulation (100x faster training)
- ✅ Dynamic GLB loading

---

## 🛠️ Requirements

- **Python 3.8+** (for backend)
- **PHP 7.0+** (for frontend)
- **Modern web browser** (Chrome, Firefox, Safari, Edge)
- **PyTorch, FastAPI, SQLAlchemy** (installed via pip)

---

## 📚 Need More Info?

Check the detailed documentation:
- `backend/README.md` - Backend API and training details
- `backend/SETUP.md` - Advanced setup and integration
- `tasks.md` - Outstanding tasks and roadmap
- `start.sh` - Startup script details

---

## 🎉 What's Next (Roadmap)

- [ ] Phase 3: Advanced visualizations (attention maps, strategy analysis)
- [ ] Population-based training (evolve multiple variants)
- [ ] Curriculum learning (1v1 → tournament → chaos)
- [ ] Multiplayer support (local 2-player controls)
- [ ] Sound effects and background music

---

## 📄 License

Free to use and modify for portfolio and educational purposes.

---

**Made with ❤️ using Three.js, PyTorch, and FastAPI**

*Last Updated: 2025-11-06 | Version 3.2*
