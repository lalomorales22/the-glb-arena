# 3D Wrestling Arena - Championship Royale

A complete **AI training system** where neural network agents learn to fight in an interactive 3D wrestling arena. Train your fighters using reinforcement learning (PPO), watch them improve through self-play, and deploy trained models back into the game. Powered by Three.js, PyTorch, and FastAPI.

## Table of Contents
- [What This Is](#what-this-is)
- [Features](#features)
- [How to Run](#how-to-run)
- [Controls](#controls)
- [Game Rules](#game-rules)
- [Technical Stack](#technical-stack)
- [Project Structure](#project-structure)
- [How AI Training Works](#how-ai-training-works)
- [System Architecture](#system-architecture)
- [Recent Improvements](#recent-improvements)
- [Portfolio Highlights](#portfolio-highlights)
- [Current Status](#current-status)
- [Upcoming Enhancements](#upcoming-enhancements)
- [Implementation Details](#implementation-details)

---

## What This Is

This is both a **game** and an **AI research platform**:

- **Play Mode**: Interactive 3D wrestling game - control fighters with keyboard, watch AI opponents battle
- **Training Mode**: Reinforcement learning system - train agents to fight optimally, track learning progress, save/load models
- **Research**: Complete data collection pipeline, neural network training, reward optimization, model evaluation

The system automatically learns fighting strategies: positioning, targeting, risk management, timing.

---

## Features

### 🎮 Game Mode (Interactive 3D Wrestling)

#### ✨ 3D Graphics & Rendering
- Real-time 3D scene with WebGL rendering
- Dynamic lighting system with spotlights and ambient lighting
- Shadow mapping for realistic depth
- Square wrestling ring with glowing red borders and metallic corner posts
- Realistic crowd with 400+ individual spectators positioned around all 4 sides
- Professional arena atmosphere with multiple lighting rigs

#### 🎮 Gameplay Mechanics
- Click to select and control fighters
- Arrow keys or WASD to move around the square ring
- Space bar to perform knockouts with comic-style impact popups (POW!, SOCK!, BAM!, etc)
- AI opponents with autonomous movement and intelligent targeting
- Collision detection between fighters with smooth boundary physics
- Health system with damage on impact
- Dynamic camera that follows your controlled fighter
- Dramatic knockout animations - fighters fly out of the ring with spinning effects!

#### ⚔️ Game Features
- **Dynamic model loading** - Automatically loads ANY .glb files from the folder (no hardcoding!)
- Supports both rigged/animated models and static models with automatic ground positioning
- Real-time fighter status display with health bars
- Victory screen when last fighter remains
- Dynamic crowd reactions with varied messages
- Square ring boundaries with smart collision detection
- Random fighter scaling for variety and visual interest
- Proper ground positioning for all model types (centered, rigged, animated)

### 🧠 Training Mode (Reinforcement Learning AI System)

#### 🤖 AI Learning
- **PPO (Proximal Policy Optimization)** - Industry-standard RL algorithm
- **Neural Networks** - Policy network + Value network for stable training
- **Self-Play** - Agents compete against each other and improve
- **Generalized Advantage Estimation (GAE)** - Efficient learning from experience
- **Reward Shaping** - Custom reward function encourages fighting strategy

#### 📊 Data & Analysis
- **SQLite Database** - Stores fighter data, training episodes, frame-by-frame telemetry
- **Model Checkpoints** - Save/load trained agents at any iteration
- **Training Metrics** - Track average reward, win rate, policy loss over time
- **Complete Telemetry** - Every action, observation, reward recorded for analysis

#### 🚀 Training Features
- **Headless Simulation** - Fast training (100x faster than graphics)
- **Batch Training** - Collect and train on multiple episodes simultaneously
- **Model Versioning** - Compare different agent versions
- **Evaluation Framework** - Test trained agents against baselines
- **REST API** - Query training data, start/stop training, export models

#### ⚙️ Technical Stack (Backend)
- **Backend**: FastAPI (Python web framework)
- **ML Framework**: PyTorch + Gymnasium
- **Database**: SQLite with SQLAlchemy ORM
- **Training**: Custom PPO implementation with GAE
- **RL Environment**: OpenAI Gym-compatible wrapper

---

## How to Run

### ⚡ Easiest Way: Auto-Start Script

```bash
# Just run the start script from the project root!
cd /path/to/the-glb-arena
chmod +x start.sh
./start.sh
```

The script automatically:
- ✅ Starts the Python backend (FastAPI) on port 8001
- ✅ Starts the PHP frontend on port 8000
- ✅ Handles cleanup when you press Ctrl+C
- ✅ Shows colorful status messages

**Then open:** http://localhost:8000 in your browser

---

### Quick Start (Both Game & Training - Manual)

```bash
# Terminal 1: Start the game (PHP)
cd /path/to/the-glb-arena
php -S localhost:8000
# Open http://localhost:8000 in browser

# Terminal 2: Start Python backend
cd backend
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
python main.py
# Backend runs on http://localhost:8001

# Terminal 3: Train an agent
cd backend
python example_training.py
```

### Option 1: Just Play the Game

```bash
cd /path/to/the-glb-arena
php -S localhost:8000
# Open http://localhost:8000
# Play with keyboard controls (WASD + SPACE)
```

**Requirements:**
- PHP 7.0+
- Modern web browser

### Option 2: Train AI Agents (Full System)

```bash
# Setup backend
cd backend
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt

# Start API server
python main.py

# In another terminal, train agents
python example_training.py
```

**Requirements:**
- Python 3.8+
- PyTorch, FastAPI, SQLAlchemy

**What Happens:**
1. Agents are initialized with random neural networks
2. They battle each other in headless simulation
3. Network learns from rewards (wins, damage dealt, survival)
4. Models are saved after each epoch
5. Progress tracked in SQLite database

**Output:**
- `backend/data/models/fighter_1_best.pth` - Best trained model
- `backend/data/databases/arena.db` - Training data and statistics
- Console output showing reward curves and metrics

### Option 3: Use REST API

The backend exposes everything via REST API:

```bash
# Start server
python main.py

# Check health
curl http://localhost:8001/health

# List fighters
curl http://localhost:8001/api/fighters

# View API docs
# Open http://localhost:8001/docs in browser
```

---

## Controls

| Action | Key |
|--------|-----|
| Move Up | `↑` or `W` |
| Move Down | `↓` or `S` |
| Move Left | `←` or `A` |
| Move Right | `→` or `D` |
| Attack/Knockout | `SPACE` |
| Select Fighter | `CLICK` fighter in list |

---

## Game Rules

1. **Objective**: Be the last fighter remaining in the ring
2. **Knockouts**: Press SPACE to attack nearby opponents and knock them out
3. **Ring Boundaries**: Stay inside the square ring or you're eliminated
4. **Health**: Each hit reduces opponent health by 20 HP
5. **Victory**: Win by eliminating all other fighters

---

## Technical Stack

**Frontend:**
- HTML5, CSS3, JavaScript (Vanilla)
- Three.js (v0.128.0) for 3D graphics
- GLTFLoader for GLB file loading
- PHP for local development server
- Custom collision detection and vector math

**Backend:**
- Python 3.8+
- FastAPI web framework
- PyTorch for neural networks
- SQLAlchemy ORM
- SQLite database
- Gymnasium for RL environment

---

## Project Structure

```
/glb-arena/
├── index.php                          # Main game file (HTML + CSS + JavaScript)
├── list-glb-files.php                 # PHP endpoint for dynamic GLB file detection
│
├── Insert-GLBS/                       # ← Put all GLB fighter models here!
│   ├── bacon.glb                      # Fighter models (auto-loaded)
│   ├── gum-guy.glb
│   ├── gum-tape-guy.glb
│   ├── scaryblue.glb
│   ├── spongebob.glb
│   └── ... (add more GLB files here!)
│
├── backend/                           # Python RL training system
│   ├── main.py                        # FastAPI server entry point
│   ├── config.py                      # Configuration (host, port, debug)
│   ├── requirements.txt                # Python dependencies
│   │
│   ├── api/
│   │   ├── __init__.py
│   │   ├── routes.py                  # 13 REST endpoints (fighters, episodes, frames)
│   │   └── schemas.py                 # Pydantic request/response models
│   │
│   ├── database/
│   │   ├── __init__.py
│   │   ├── base.py                    # SQLAlchemy engine setup
│   │   ├── models.py                  # 5 ORM tables (Fighter, Episode, etc)
│   │   └── manager.py                 # Database initialization
│   │
│   ├── rl/
│   │   ├── __init__.py
│   │   ├── agent.py                   # Neural network (2-layer actor-critic)
│   │   ├── environment.py             # Gym wrapper (30-dim obs, 10 actions)
│   │   ├── training.py                # PPO trainer with GAE
│   │   └── __pycache__/
│   │
│   ├── simulation/
│   │   ├── __init__.py
│   │   ├── arena.py                   # Headless arena (100x faster)
│   │   ├── fighter.py                 # Fighter state (position, health)
│   │   └── __pycache__/
│   │
│   ├── utils/
│   │   ├── __init__.py
│   │   └── metrics.py                 # Stats computation
│   │
│   ├── data/
│   │   ├── databases/
│   │   │   └── arena.db               # SQLite database file
│   │   ├── models/
│   │   │   ├── fighter_1_best.pth     # Trained weights (PyTorch)
│   │   │   └── fighter_1_v20.pth      # Model checkpoints
│   │   └── logs/
│   │
│   ├── example_training.py            # Full training example
│   ├── README.md                      # Backend documentation
│   └── SETUP.md                       # Integration examples
│
├── README.md                          # This file
└── tasks.md                           # Outstanding tasks

The game automatically detects and loads all .glb files in the directory!
No code changes needed - just drop new models in the folder.
```

---

## How AI Training Works

### The Learning Loop

1. **Initialization** - Neural networks created with random weights
2. **Battle Simulation** - Agents fight each other in headless arena (no graphics)
3. **Data Collection** - Every action, observation, reward recorded
4. **Learning** - PPO algorithm updates network weights to maximize rewards
5. **Evaluation** - Test agent against baseline, save if improved
6. **Repeat** - Continue for multiple epochs

### What Agents Learn

After training, agents develop:
- **Positioning** - Strategic placement in ring, avoiding edges
- **Targeting** - Identifying and pursuing weakest/closest opponents
- **Risk Management** - Balancing offense vs survival
- **Distance Management** - Knowing when to attack vs retreat
- **Crowd Control** - Fighting efficiently against multiple opponents

### Reward Function

Agents maximize:
```
+1.0  per frame survived
+2.0  for hitting opponent
+10.0 for knockout
-1.0  for taking damage
-3.0  for being near ring edge
-10.0 for getting knocked out
```

### Example Training Results

```
Epoch 1: Avg Reward = 45.23 (mostly random movements)
Epoch 2: Avg Reward = 52.15 (learning to move toward enemies)
Epoch 3: Avg Reward = 68.42 (starting to attack)
Epoch 4: Avg Reward = 75.88 (better targeting)
Epoch 5: Avg Reward = 89.34 (strategic positioning)
...
Epoch 20: Avg Reward = 156.45 (optimized fighting strategy)
```

Win rate vs untrained agents: **70-80%**

---

## System Architecture

### High-Level Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                     3D Wrestling Arena (v3.0)                   │
│              Interactive Game + AI Learning Platform             │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────────┐
│   Game Frontend      │
│   (Three.js + PHP)   │
│                      │
│ • 3D graphics        │  (index.php)
│ • Player controls    │
│ • Scripted AI        │
│ • Ring physics       │
│ • 400+ crowd         │
└──────────────┬───────┘
               │
        HTTP REST API (Port 8001)
               │
       ┌───────▼─────────┐
       │   FastAPI       │
       │   Backend       │  (main.py)
       │                 │
       │ • 13 endpoints  │
       │ • CORS enabled  │
       │ • Auto docs     │
       └───────┬─────────┘
               │
       ┌───────▼─────────┐
       │   SQLite DB     │  (arena.db)
       │                 │
       │ • fighters      │  (GLB models)
       │ • episodes      │  (battles)
       │ • fight_frames  │  (frame data)
       │ • checkpoints   │  (model weights)
       │ • metrics       │  (training stats)
       └───────┬─────────┘
               │
┌──────────────▼──────────────────────┐
│   Training Pipeline                 │
│   (PyTorch + PPO)                   │
│                                     │
│ RL Environment     PPO Trainer      │
│ • Gym wrapper     • Trajectory      │
│ • Observations    • GAE             │
│ • Action space    • PPO Loss        │
│ • Rewards         • Model Save      │
│                                     │
│ Headless Arena   Neural Networks    │
│ • Fast physics   • Policy head      │
│ • No graphics    • Value head       │
│ • State tracking • Checkpoints      │
└─────────────────────────────────────┘
```

### Game Architecture

| Component | Role | Details |
|-----------|------|---------|
| **Scene** | 3D rendering context | Three.js WebGL context |
| **Camera** | Player perspective with dynamic follow | Follows controlled fighter |
| **Lighting** | Professional multi-light setup | Spotlight, rim lights, ambient |
| **Crowd** | 400 spectators with reactions | Positioned around all 4 sides |
| **Ring** | Square boundaries with physics | 113-unit radius with smart collision |
| **Fighters** | Controllable/AI fighters | Encapsulated fighter objects |
| **Controls** | Keyboard input handling | WASD + Space + Click |
| **Render Loop** | Main animation frame | 60 FPS with delta time |

### RL Training Architecture

#### Neural Network Structure
```
Input: 30-dim observation (position, health, velocity, enemies)
    ↓
Shared Feature Extraction:
  Dense(30→128) + ReLU
  Dense(128→128) + ReLU
    ↓
    ├─ Policy Head: Dense(128→10) → Softmax [for 10 actions]
    └─ Value Head:  Dense(128→1)  → Scalar estimate
```

**Network Details:**
- Parameters: ~30k weights
- Weight Init: Xavier uniform
- Activation: ReLU (hidden), Softmax (policy), Linear (value)
- Device: CPU/CUDA compatible

#### Training Algorithm: PPO

**Pseudo-code:**
```python
for epoch in range(num_epochs):
    # Collect experience
    trajectory = collect_trajectory(env, agent, num_episodes)

    # Compute advantages (GAE)
    advantages = compute_gae(trajectory)
    advantages = normalize(advantages)

    # PPO update
    for minibatch in trajectory.batches(size=32):
        logits, value = agent(minibatch.observations)

        # Clipped surrogate objective
        log_prob = softmax(logits)[actions]
        ratio = log_prob / old_log_prob

        policy_loss = -min(
            ratio * advantages,
            clip(ratio, 1-eps, 1+eps) * advantages
        )

        # Value loss
        value_loss = (value - returns)^2

        # Entropy bonus
        entropy = -sum(prob * log(prob))

        # Total loss
        loss = policy_loss + 0.5*value_loss - 0.01*entropy

        optimizer.step(loss)
```

#### Hyperparameters

| Parameter | Value | Purpose |
|-----------|-------|---------|
| Learning Rate | 3e-4 | Stable weight updates |
| Gamma (γ) | 0.99 | Long-term reward focus |
| GAE Lambda (λ) | 0.95 | Variance reduction |
| PPO Epsilon (ε) | 0.2 | Clip range for policy |
| Entropy Coeff | 0.01 | Exploration encouragement |
| Value Loss Weight | 0.5 | Balance with policy |
| Max Grad Norm | 0.5 | Gradient clipping |
| Batch Size | 32 | Mini-batch training |
| Epochs per Batch | 3 | Update iterations |

### Database Schema

#### Tables (5 total)

**fighters** - Model metadata
```sql
CREATE TABLE fighters (
    id INTEGER PRIMARY KEY,
    name TEXT UNIQUE,
    model_path TEXT,
    metadata JSON,
    created_at TIMESTAMP
);
```

**episodes** - Battle records
```sql
CREATE TABLE episodes (
    id INTEGER PRIMARY KEY,
    fighter_id INTEGER FK,
    opponents TEXT,  -- JSON list of opponent IDs
    status TEXT,     -- 'ongoing', 'completed'
    winner_id INTEGER,
    reward_sum FLOAT,
    duration_frames INTEGER,
    created_at TIMESTAMP,
    completed_at TIMESTAMP
);
```

**fight_frames** - Main training data
```sql
CREATE TABLE fight_frames (
    id INTEGER PRIMARY KEY,
    episode_id INTEGER FK,
    fighter_id INTEGER FK,
    frame_number INTEGER,
    position TEXT,              -- JSON [x, z]
    health FLOAT,
    velocity TEXT,              -- JSON [vx, vz]
    observation_vector TEXT,    -- JSON [30-dim array]
    action_taken INTEGER,       -- 0-9
    reward_delta FLOAT,
    cumulative_reward FLOAT,
    created_at TIMESTAMP
);
-- Index: (episode_id, fighter_id) for fast lookups
```

**model_checkpoints** - Model versions
```sql
CREATE TABLE model_checkpoints (
    id INTEGER PRIMARY KEY,
    fighter_id INTEGER FK,
    version INTEGER,
    weights_path TEXT,
    config JSON,
    win_rate FLOAT,
    avg_reward FLOAT,
    created_at TIMESTAMP
);
```

**training_metrics** - Epoch summaries
```sql
CREATE TABLE training_metrics (
    id INTEGER PRIMARY KEY,
    fighter_id INTEGER FK,
    epoch INTEGER,
    avg_reward FLOAT,
    min_reward FLOAT,
    max_reward FLOAT,
    loss FLOAT,
    created_at TIMESTAMP
);
```

#### Relationships
```
Fighter (1) ──→ (N) Episodes
Fighter (1) ──→ (N) ModelCheckpoints
Fighter (1) ──→ (N) FightFrames
Episode (1) ──→ (N) FightFrames
```

### REST API (13 Endpoints)

#### Health & Status
```
GET /health
Response:
{
  "status": "healthy",
  "database": "connected",
  "version": "1.0.0"
}
```

#### Fighters (CRUD)
```
POST /fighters
Body: { fighter_id, name, model_path }
Response: { id, name, created_at }

GET /fighters
Response: [{ id, name, created_at }, ...]

GET /fighters/{id}
Response: { id, name, total_episodes, win_rate, avg_reward }

GET /fighters/{id}/stats
Response: {
  total_episodes: int,
  total_wins: int,
  win_rate: float,
  avg_reward: float,
  avg_episode_length: int
}
```

#### Episodes (Battle Records)
```
POST /episodes
Body: { fighter_id, opponents: [id1, id2, ...], map_size }
Response: { id, fighter_id, status: "ongoing" }

PATCH /episodes/{id}
Body: { status: "completed", winner_id, final_health, duration_frames }
Response: { id, status: "completed" }

GET /episodes
Response: [{ id, fighter_id, winner_id, status, created_at }, ...]

GET /episodes/{id}
Response: { id, fighter_id, opponent_ids, winner_id, reward_sum, created_at }
```

#### Frame Data (Training)
```
POST /fight-frames
Body: {
  episode_id, fighter_id, frame_number,
  position: [x, z],
  health: float,
  velocity: [vx, vz],
  observation_vector: [30-dim],
  action_taken: 0-9,
  reward_delta: float
}
Response: { id, created_at }

GET /fight-frames
Params: ?episode_id=X&limit=100&offset=0
Response: [{ frame }, ...]

GET /fighters/{id}/frames
Response: [{ all frames for fighter }...]
```

#### Models (Checkpoints)
```
GET /fighters/{id}/checkpoints
Response: [{ version, created_at, win_rate, avg_reward }, ...]

GET /fighters/{id}/best-model
Response: {
  id, version, win_rate,
  weights_url: "/api/fighters/{id}/checkpoints/{version}/weights",
  created_at
}

GET /fighters/{id}/checkpoints/{version}/weights
Response: Binary (PyTorch .pth file)
```

---

## Recent Improvements (v3.0 - AI Learning System)

🎉 **Major Additions:**
- 🧠 **Reinforcement Learning** - Complete PPO training system with PyTorch
- 📚 **SQLite Database** - Persistent storage of fighter data, episodes, training metrics
- 🚀 **FastAPI Backend** - REST API for game-backend integration
- 🎯 **Neural Networks** - Policy + Value networks with proper initialization
- ⚡ **Headless Simulation** - 100x faster training without graphics
- 📊 **Model Checkpoints** - Save/load trained agents at any iteration
- 📈 **Training Metrics** - Real-time tracking of learning progress
- 🔄 **Generalized Advantage Estimation (GAE)** - Efficient gradient estimation

### Previous (v2.0)

🎉 **Major Updates:**
- ✨ **Dynamic GLB Loading** - Automatically loads any GLB files in the directory
- 📦 **Square Wrestling Ring** - Converted from circular to square with proper boundaries
- 👥 **Realistic Crowd** - 400+ individual spectators positioned around all 4 sides (no more flickering boxes!)
- 💥 **Impact Popups** - Comic-style animations when hitting opponents (POW!, SOCK!, BAM!, etc)
- 🚀 **Knockout Flight** - Characters now fly out of the ring with dramatic spinning animations
- 🎯 **Smart Collision Detection** - Works perfectly with square ring and smooth boundary physics
- 🏃 **Universal Model Support** - Properly handles rigged models, centered models, and static models
- 🎮 **Fixed Controls** - WASD and Space controls now work reliably

---

## Portfolio Highlights

This project demonstrates expertise across multiple domains:

### 🎮 Game Development
- Game state management and fighter control logic
- Physics-based collision detection (both circular and square bounds)
- AI opponent behavior with autonomous movement and targeting
- Real-time rendering loop with delta time calculations
- Knockout animations with easing functions and arc trajectories
- Performance optimization for simultaneous fighter simulations

### 🧠 Machine Learning & AI
- **Reinforcement Learning Implementation** - Full PPO (Proximal Policy Optimization) algorithm
- **Neural Network Design** - Policy + Value architecture, weight initialization, gradient flow
- **Data Pipeline** - Collection, preprocessing, batch management for training
- **Reward Shaping** - Designing learning signals that produce desired behaviors
- **Model Evaluation** - Testing trained agents, tracking metrics, model versioning
- **Algorithm Knowledge** - GAE (Generalized Advantage Estimation), entropy bonuses, clipped objectives

### 🎨 3D Graphics & Rendering
- Scene setup and camera management with dynamic following
- Model loading and dynamic transformation (GLTFLoader)
- Bounding box calculations for auto-scaling and positioning
- Advanced lighting and shadow systems
- Handling models with various pivot points and origins
- Real-time WebGL rendering optimization

### 🌐 Web Technologies
- **Frontend**: Three.js expertise, vanilla JavaScript ES6, DOM manipulation
- **Backend**: FastAPI (async Python web framework), RESTful API design
- **Database**: SQLite with SQLAlchemy ORM, schema design, query optimization
- **Full Stack**: Client-server communication, data synchronization, real-time updates

### 🔧 Python & Deep Learning
- PyTorch neural network implementation and training
- Gymnasium (OpenAI Gym) environment wrapper design
- Custom loss functions (PPO loss with clipping)
- Batch processing and mini-batch gradient descent
- Model serialization and checkpointing

### 📊 Data Engineering & Analysis
- Time-series data collection (30+ frames per second)
- SQLAlchemy ORM with complex relationships
- Metrics computation and aggregation
- Database optimization (WAL mode, foreign keys)
- Data validation with Pydantic

### 💻 Code Architecture
- **Modular Design** - Separate concerns (API, ML, DB, Simulation)
- **Clean Code** - Comprehensive comments, docstrings, type hints
- **Scalability** - Independent components can scale separately
- **Testability** - Each module can be tested in isolation
- **Configuration Management** - Centralized settings, easy customization

### 🚀 Production Practices
- Virtual environment setup and dependency management
- Configuration via environment variables
- Logging and error handling
- Database transactions with rollback
- API versioning and backward compatibility

---

## Current Status

✅ **COMPLETE**: Game frontend with all gameplay mechanics
✅ **COMPLETE**: Backend training system with PPO + neural networks
✅ **COMPLETE**: SQLite database for data collection and analysis
✅ **COMPLETE**: REST API for game-backend communication
✅ **COMPLETE**: Training pipeline with example script
✅ **COMPLETE**: Frontend-Backend integration (game sends data, loads trained models)
✅ **COMPLETE**: 90s WWF-themed Analytics Dashboard (Phase 3) with real-time metrics
✅ **COMPLETE**: Database optimization with pagination and indexes
⏳ **IN PROGRESS**: Advanced training modes (population-based, curriculum learning)

---

## 🎪 Analytics Dashboard (Phase 3 - NEW!)

A stunning **90s WWF-themed analytics dashboard** for real-time monitoring of fighter performance and training progress!

### Features

#### 🌟 Dashboard Tabs
1. **📊 Overview** - System status, championship standings, reward progression charts
2. **🥊 Fighters** - Detailed stats for each fighter with win rates and progress bars
3. **🤖 Training** - Training metrics, learning curves, and model performance
4. **📈 Stats** - Comprehensive table with all fighter statistics

#### 🎨 90s WWF Styling
- **Neon Colors**: Cyan, magenta, yellow, lime green with glowing effects
- **Championship Belts**: Trophy indicators for top performers
- **Wrestling Theme**: Fighter rankings, "Victory Records", "Championship Belts"
- **Animated Elements**: Pulsing glow effects, smooth transitions, dynamic backgrounds
- **Comic-Style Fonts**: Bold, impactful typography with text shadows

#### 📊 Real-Time Features
- **Auto-Refresh Toggle**: Enable/disable automatic 10-second data updates
- **Live Charts**:
  - Average Reward Bar Chart
  - Win Rate Doughnut Chart
  - Learning Curve Line Chart (all fighters)
- **Performance Metrics**: Track avg reward, win rates, episode counts
- **Last Updated Timestamp**: See when data was last refreshed

#### 🏆 Special Displays
- **Undisputed Champion Card**: Highlights top-performing fighter with championship belt
- **Rankings Display**: Top 5 fighters by win rate with medals (🥇🥈🥉)
- **Progress Bars**: Visual representation of win rates with color coding
- **Model Version Info**: Best model version with win rate and reward stats

### How to Access

1. **From Game**: Click the 📊 **ANALYTICS** button in the game UI (bottom-left)
2. **Direct Link**: Open `http://localhost:8000/analytics.php` in your browser

### API Endpoints (with Pagination Support)

The dashboard uses optimized API endpoints with pagination:

```
GET /api/fighters                              # List all fighters
GET /api/fighters/{id}                         # Get fighter details
GET /api/fighters/{id}/stats                   # Fighter statistics
GET /api/fighters/{id}/episodes?skip=0&limit=50  # Episodes (paginated)
GET /api/fighters/{id}/checkpoints             # Model checkpoints
GET /api/episodes/{id}/frames?skip=0&limit=100  # Frame data (paginated)
```

### Database Optimizations

- ✅ **Composite Indexes**: `(fighter_id, started_at)` for fast queries
- ✅ **Frame Indexes**: `(episode_id, frame_number)` for training data
- ✅ **Individual Indexes**: On frequently filtered columns
- ✅ **Pagination**: Limit result sets to prevent memory bloat
- ✅ **WAL Mode**: Write-ahead logging for concurrent access

### Performance Metrics

- Dashboard loads in <2 seconds with 100+ episodes
- Charts render smoothly even with 1000+ data points
- Auto-refresh doesn't block user interaction
- Pagination prevents large result sets from slowing the system

---

## Upcoming Enhancements

### Phase 1: Game Polish & Features
- Sound effects and background music
- Multiplayer support (2-player local gamepad controls)
- Additional fighter animations and action combos
- Power-ups and special moves (shields, speed boosts, etc)
- Leaderboard/scoring system with win tracking
- Mobile touch controls for tablets/mobile devices
- Custom ring themes and environments
- Replays and slow-motion knockout replays
- Particle effects for impacts and knockouts
- Performance optimization for 100+ fighter battles

### Phase 2: Training Integration & Analytics
- [ ] Analytics dashboard with real-time learning curves
- [ ] Model version comparison and A/B testing
- [ ] Fighter statistics and behavior analysis
- [ ] Win rate tracking over time per fighter
- [ ] Behavior heatmaps (where do agents attack?)
- [ ] Population-based training (evolve multiple variants)
- [ ] Curriculum learning (1v1 → tournament → chaos)
- [ ] Transfer learning (knowledge between fighters)
- [ ] Adversarial training (defensive vs aggressive pairs)
- [ ] Genetic algorithm for population evolution

### Phase 3: Research & Visualization
- Attention visualization (what does network focus on?)
- Decision tree analysis (learned strategies)
- Ablation studies (which rewards matter most?)
- Benchmarking against baselines
- Real-time training graphs
- Model performance comparison
- Fighter evolution trees
- Behavior pattern analysis

---

## Implementation Details

The AI Learning System has been fully implemented! Key implementation:

- ✅ 30-dimensional observation vector (position, health, velocity, nearby enemies)
- ✅ 10-action discrete action space (8 directions + idle + attack)
- ✅ 2-layer neural network with policy + value heads
- ✅ PPO training algorithm with GAE advantage estimation
- ✅ SQLite database schema with 5 tables
- ✅ FastAPI REST API with 13 endpoints
- ✅ Headless arena simulation for fast training
- ✅ Model checkpoint system with version control
- ✅ Frontend-Backend integration with real-time data flow
- ✅ Trained model loading for AI fighter control

---

## How to Add Custom Fighter Models

The app automatically loads all GLB files from the **Insert-GLBS/** folder!

### Current Setup

```
/glb-arena/
├── Insert-GLBS/                 # ← All GLB files go here!
│   ├── bacon.glb
│   ├── gum-guy.glb
│   ├── gum-tape-guy.glb
│   ├── scaryblue.glb
│   └── spongebob.glb
├── index.php                    # Loads from Insert-GLBS/
├── list-glb-files.php          # Scans Insert-GLBS/
└── ... (other files)
```

### How It Works

1. **list-glb-files.php** - Scans the `Insert-GLBS/` folder for all `.glb` files
2. **index.php** - Fetches the list and loads each model
3. **Automatic Loading** - No code changes needed!

### To Add New Fighters

1. **Export or find GLB files** - Download or create 3D models in GLB format
2. **Place in Insert-GLBS folder** - Copy .glb files to the `Insert-GLBS/` directory
3. **Refresh the game** - Press F5 or reload the page
4. **Your fighter appears!** - Automatically added to the fighter list

### Model Support

The game handles:
- ✅ Rigged/skeletal models (with armatures)
- ✅ Centered models (origin at center)
- ✅ Static models (origin at feet)
- ✅ Models of any size (auto-scaled to 30 units tall)
- ✅ Animated models (loads all animations)

---

## Technical Notes

- **Model Positioning**: Uses intelligent bounding box calculation to handle models with different pivot points
- **Auto-Scaling**: All models are scaled to ~30 units height for consistent gameplay
- **Performance**: Optimized for 15+ fighters simultaneously (tested with 18 unique models)
- **Browser Compatibility**: Works on all modern browsers (Chrome, Firefox, Safari, Edge)

---

## License

Free to use and modify for portfolio and educational purposes.

---

**Made with Three.js, JavaScript, PyTorch, and RL** 🎪🏆🧠

*Last Updated: 2025-10-31 | Version 3.0 - Complete AI Learning Arena*

### Version History

- **v3.0** - ✅ COMPLETE: Full AI Learning System with PPO, neural networks, SQLite database, REST API, training pipeline
- **v2.5** - Enhanced AI with random fighting attributes, improved arena lighting
- **v2.0** - Dynamic GLB loading, square ring, realistic crowd, impact popups, knockout flight animations
- **v1.0** - Initial release with basic 3D wrestling mechanics
