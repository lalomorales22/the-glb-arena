# Backend Setup & Integration Guide

This guide explains how to set up the Python backend and integrate it with the game frontend.

## Installation

### 1. Create Virtual Environment
```bash
cd /path/to/glbs/backend
python3 -m venv venv
source venv/bin/activate  # macOS/Linux
# or
venv\Scripts\activate  # Windows
```

### 2. Install Dependencies
```bash
pip install -r requirements.txt
```

### 3. Start the Backend Server
```bash
python main.py
```

The server will start on `http://localhost:8001`

Check health: `curl http://localhost:8001/health`

View API docs: `http://localhost:8001/docs`

---

## Frontend-Backend Integration

### Flow: Game → Backend → Database → Training

```
1. Game Frontend (index.php)
   ↓ (HTTP POST)
2. REST API (/api/...)
   ↓
3. SQLite Database
   ↓
4. Python Training Loop
   ↓
5. Model Checkpoints
   ↓
6. Game Frontend (Load trained models)
```

---

## API Endpoints

### Health Check
```bash
GET /health
```

### Fighter Management
```bash
# Register a new fighter
POST /fighters
{
  "glb_filename": "bacon-cyborg.glb",
  "metadata_json": {"size": 1.0, "speed": 1.0}
}

# List all fighters
GET /fighters

# Get specific fighter
GET /fighters/{fighter_id}
```

### Training Sessions
```bash
# Create episode
POST /episodes
{
  "fighter_id": 1,
  "episode_number": 1,
  "opponent_ids": [2, 3, 4],
  "total_reward": 0.0,
  "duration_frames": 0,
  "is_victory": false
}

# Record fight frame (call every game frame)
POST /fight-frames
{
  "episode_id": 1,
  "frame_number": 1,
  "fighter_position": [0.0, 5.0],
  "fighter_health": 100.0,
  "fighter_velocity": [1.0, 0.5],
  "enemies_state": [{"id": 2, "pos": [10.0, 0.0], "health": 90}],
  "action_vector": [1.0, 0.0, 0.0, 0],
  "reward_delta": 1.0,
  "cumulative_reward": 1.0,
  "observation_vector": [0.0, 0.05, 1.0, ...]
}

# Complete episode
PATCH /episodes/{episode_id}
{
  "total_reward": 150.5,
  "duration_frames": 3000,
  "is_victory": true,
  "rank": 1
}
```

### Statistics
```bash
# Get fighter statistics
GET /fighters/{fighter_id}/stats

# Get all episodes for fighter
GET /fighters/{fighter_id}/episodes

# Get episode frames
GET /episodes/{episode_id}/frames

# Get best model
GET /fighters/{fighter_id}/best-model
```

---

## Integration from JavaScript

### Example: Send Fight Data to Backend

```javascript
// 1. Register a fighter on startup
async function registerFighter(glbFilename) {
  const response = await fetch('http://localhost:8001/api/fighters', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      glb_filename: glbFilename,
      metadata_json: { size: 1.0, speed: 1.0 }
    })
  });
  const fighter = await response.json();
  return fighter.id;
}

// 2. Start a training episode
async function startEpisode(fighterId, opponentIds) {
  const response = await fetch('http://localhost:8001/api/episodes', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      fighter_id: fighterId,
      episode_number: 1,
      opponent_ids: opponentIds,
      total_reward: 0.0,
      duration_frames: 0,
      is_victory: false
    })
  });
  const episode = await response.json();
  return episode.id;
}

// 3. Record each frame during battle (call ~60 times per second)
async function recordFrame(episodeId, frameData) {
  await fetch('http://localhost:8001/api/fight-frames', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      episode_id: episodeId,
      frame_number: frameData.frameNumber,
      fighter_position: frameData.fighterPos,
      fighter_health: frameData.fighterHealth,
      fighter_velocity: frameData.fighterVel,
      enemies_state: frameData.enemies,
      action_vector: frameData.action,
      reward_delta: frameData.reward,
      cumulative_reward: frameData.cumulativeReward,
      observation_vector: frameData.observation
    })
  });
}

// 4. Complete episode when battle ends
async function completeEpisode(episodeId, stats) {
  await fetch(`http://localhost:8001/api/episodes/${episodeId}`, {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      total_reward: stats.totalReward,
      duration_frames: stats.duration,
      is_victory: stats.isVictory,
      rank: stats.finalRank
    })
  });
}
```

---

## Training Workflow

### Phase 1: Data Collection
- Game runs battles and records all frame data to database
- Data accumulated for 100+ episodes per fighter

### Phase 2: Training
```python
from rl.training import create_training_session, TrainingConfig

# Create trainer
config = TrainingConfig(
    learning_rate=3e-4,
    episodes_per_training=100,
    batch_size=32,
)
trainer = create_training_session(fighter_id=1, config=config)

# Collect trajectories and train
for epoch in range(10):
    for episode in range(100):
        stats = trainer.collect_trajectory(env, max_steps=5000)
    metrics = trainer.train_step()
    print(f"Epoch {epoch}: {metrics}")

    # Save checkpoint
    trainer.save_checkpoint(f"models/fighter_1_v{epoch}.pth")
```

### Phase 3: Deployment
- Load trained models in game frontend
- Apply neural network decisions to AI fighters
- Run inference in real-time during battles

---

## Database Schema

Tables created automatically on first run:

- `fighters` - Fighter definitions and metadata
- `episodes` - Complete battle records
- `fight_frames` - Frame-by-frame battle data (for training)
- `model_checkpoints` - Saved neural network weights
- `training_metrics` - Aggregated training statistics

Location: `backend/data/databases/arena.db`

---

## Troubleshooting

### CORS Errors
If frontend can't reach backend, check CORS in `main.py`:
```python
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:8000"],  # Your game server
    ...
)
```

### Database Locked
If getting "database is locked":
- Ensure only one training process at a time
- Check for hanging connections

### Out of Memory
For large training:
- Reduce batch_size in config
- Use streaming API calls instead of loading all frames

---

## Next Steps

1. Update `index.php` to call backend APIs during battles
2. Implement data collection pipeline
3. Run training loop on collected data
4. Load trained models for AI fighter decisions
