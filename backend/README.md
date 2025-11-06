# Wrestling Arena Backend - RL Training System

A comprehensive Python backend for training reinforcement learning agents to learn fighting strategies in the 3D wrestling arena game.

## 🎯 What This Does

- **REST API** - Connect the game frontend to the training backend
- **Data Pipeline** - Automatically collect and store battle data in SQLite
- **Neural Networks** - Train agents to learn optimal fighting strategies using PPO
- **Model Management** - Save, load, and evaluate trained models
- **Headless Simulation** - Fast battle simulation for training without graphics

## 🏗️ Architecture

```
Game Frontend (JavaScript)
    ↓ (HTTP REST API)
FastAPI Backend
    ↓
SQLite Database
    ├─ fighters (fighter definitions)
    ├─ episodes (complete battles)
    ├─ fight_frames (frame-by-frame data)
    └─ model_checkpoints (saved weights)
    ↓
Training Pipeline
    ├─ Environment (Gym wrapper)
    ├─ Neural Network (Policy + Value)
    ├─ PPO Trainer
    └─ Model Checkpoints
```

## 📦 Project Structure

```
backend/
├── main.py                 # FastAPI app entry point
├── config.py              # Configuration settings
│
├── database/              # SQLite & ORM
│   ├── models.py          # SQLAlchemy models
│   ├── db.py              # Connection management
│   └── __init__.py
│
├── api/                   # REST API layer
│   ├── routes.py          # Endpoints (fighters, episodes, frames)
│   ├── schemas.py         # Pydantic validation
│   └── __init__.py
│
├── rl/                    # Reinforcement Learning
│   ├── environment.py     # Gym environment wrapper
│   ├── agent.py           # Neural network models
│   ├── training.py        # PPO trainer & rollout buffer
│   └── __init__.py
│
├── simulation/            # Headless arena
│   ├── arena.py           # Fast battle simulation
│   └── __init__.py
│
├── utils/                 # Helper functions
│   ├── helpers.py
│   └── __init__.py
│
├── requirements.txt       # Dependencies
├── SETUP.md              # Setup & integration guide
├── example_training.py   # Training example script
└── README.md             # This file
```

## 🚀 Quick Start

### 1. Install Dependencies

```bash
cd backend
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
```

### 2. Start the Backend Server

```bash
python main.py
```

Server runs on `http://localhost:8001`

API Documentation: `http://localhost:8001/docs`

### 3. Train an Agent

```bash
python example_training.py
```

This demonstrates the complete training pipeline.

## 📡 REST API Overview

### Health & Status
```
GET  /health                              # Server health check
```

### Fighter Management
```
POST   /api/fighters                      # Register new fighter
GET    /api/fighters                      # List all fighters
GET    /api/fighters/{id}                 # Get fighter details
GET    /api/fighters/{id}/stats           # Get fighter statistics
```

### Training Episodes
```
POST   /api/episodes                      # Start new episode
GET    /api/episodes/{id}                 # Get episode details
PATCH  /api/episodes/{id}                 # Complete episode
GET    /api/fighters/{id}/episodes        # Get all fighter episodes
```

### Fight Data
```
POST   /api/fight-frames                  # Record frame data
GET    /api/episodes/{id}/frames          # Get all frames in episode
```

### Models
```
GET    /api/fighters/{id}/checkpoints     # List saved models
GET    /api/fighters/{id}/best-model      # Get best performing model
```

**Full API docs available at** `/docs` endpoint

## 🧠 Neural Network Architecture

**Input**: 30-dimensional observation vector
- Own position (2)
- Own health (1)
- Own velocity (2)
- 5 closest enemies × 5 values each (25)

**Hidden Layers**: 2 layers × 128 neurons with ReLU activation

**Output**:
- **Policy Head**: 10 action logits (softmax for action selection)
- **Value Head**: 1 scalar (baseline for variance reduction)

**Actions**:
- 0-7: Movement (8 directions)
- 8: Idle
- 9: Attack

## 🏋️ Training Process

### Proximal Policy Optimization (PPO)

```python
# 1. Collect trajectories
for episode in range(episodes_per_epoch):
    trajectory = collect_trajectory(env, agent)

# 2. Compute advantages using GAE
advantages = compute_gae(rewards, values, gamma=0.99)

# 3. Train policy and value networks
for epoch in range(num_epochs):
    for batch in data_loader:
        policy_loss = ppo_loss(batch)
        value_loss = mse_loss(values, returns)
        total_loss = policy_loss + value_loss - entropy_bonus
        backprop()

# 4. Save checkpoint
save_model(agent, "models/fighter_1_v5.pth")
```

### Reward Function

The agent learns to maximize:
- **+1**: Each frame survived (encourages survival)
- **+2**: Successful attack on opponent
- **+10**: Knockout
- **-1**: Taking damage
- **-3**: Being near ring edge (risky)
- **-10**: Knocked out of ring (death)

## 💾 Database Schema

### fighters
- id (PRIMARY KEY)
- glb_filename (fighter model)
- model_version (current version)
- created_at
- metadata_json (size, speed, etc)

### episodes
- id (PRIMARY KEY)
- fighter_id (FK)
- episode_number
- opponent_ids (JSON list)
- total_reward
- duration_frames
- is_victory
- rank (1st, 2nd, etc)
- started_at
- ended_at

### fight_frames
- id (PRIMARY KEY)
- episode_id (FK)
- frame_number
- fighter_position (JSON)
- fighter_health
- fighter_velocity (JSON)
- enemies_state (JSON)
- action_vector (JSON)
- reward_delta
- cumulative_reward
- observation_vector (JSON)

### model_checkpoints
- id (PRIMARY KEY)
- fighter_id (FK)
- model_version
- training_iteration
- win_rate
- avg_reward
- total_episodes_trained
- weights_path
- training_mode (survival, aggression, etc)
- created_at

### training_metrics
- id (PRIMARY KEY)
- fighter_id (FK)
- training_session_id
- iteration_number
- avg_reward_last_100
- win_rate_last_100
- loss
- policy_entropy
- recorded_at

## 🔗 Integration with Game Frontend

The game (index.php) should:

1. **On Battle Start**
   ```javascript
   const episodeId = await startEpisode(fighterId, opponentIds);
   ```

2. **Each Game Frame** (~60fps)
   ```javascript
   await recordFrame(episodeId, frameData);
   ```

3. **On Battle End**
   ```javascript
   await completeEpisode(episodeId, {
     totalReward: 150,
     duration: 3000,
     isVictory: true,
     finalRank: 1
   });
   ```

See `SETUP.md` for detailed integration examples.

## 📊 Example Training Session

```bash
$ python example_training.py

🎯 Starting training for fighter 1 in survival mode

📊 Epoch 1/10
  Avg Reward: 45.23
  Policy Loss: 0.1234
  Value Loss: 0.0567
  Entropy: 0.8932
  ✅ New best model saved: models/fighter_1_best.pth

📊 Epoch 2/10
  Avg Reward: 52.15
  ...

📈 Evaluating fighter 1
  Episode 1: Reward = 68.45
  Episode 2: Reward = 71.23
  ...
  Average Evaluation Reward: 69.84

✅ Training completed! Best avg reward: 75.42
```

## 🎓 Training Modes

The system supports different training objectives:

- **Survival**: Maximize longevity, avoid damage
- **Aggression**: Maximize damage dealt, knockouts
- **Tactical**: Learn positioning and distance management
- **Adaptation**: Learn to counter specific opponent types
- **Tournament**: Optimize for multi-opponent battles

Configure in the training config:
```python
trainer = create_training_session(
    fighter_id=1,
    config=TrainingConfig(
        learning_rate=3e-4,
        num_epochs=5,
        batch_size=32,
    )
)
```

## 🛠️ Advanced Usage

### Load and Evaluate a Model

```python
from rl.agent import AgentCheckpoint
from rl.environment import WrestlingArenaEnv

# Load trained model
model = AgentCheckpoint.load("models/fighter_1_best.pth")

# Create environment
env = WrestlingArenaEnv()

# Run inference
obs, _ = env.reset()
for step in range(1000):
    action, value = model.get_action_and_value(obs)
    obs, reward, done, truncated, info = env.step(action)
    if done or truncated:
        break
```

### Compare Model Versions

```python
models = [
    "models/fighter_1_v1.pth",
    "models/fighter_1_v5.pth",
    "models/fighter_1_best.pth",
]

from example_training import compare_models
compare_models(fighter_id=1, model_paths=models)
```

### Access Training Data

```python
from database import get_db_manager
from database.models import Fighter, Episode

db_manager = get_db_manager()
with db_manager.session_scope() as session:
    fighter = session.query(Fighter).filter(Fighter.id == 1).first()
    episodes = fighter.episodes

    for episode in episodes[:5]:
        print(f"Episode {episode.episode_number}:")
        print(f"  Reward: {episode.total_reward}")
        print(f"  Victory: {episode.is_victory}")
        print(f"  Frames: {len(episode.fight_frames)}")
```

## 📈 Monitoring Training

The database stores everything. Query progress:

```python
from database import get_db_manager
from database.models import TrainingMetrics

with get_db_manager().session_scope() as session:
    metrics = session.query(TrainingMetrics)\
        .filter(TrainingMetrics.fighter_id == 1)\
        .order_by(TrainingMetrics.iteration_number)\
        .all()

    for m in metrics:
        print(f"Iteration {m.iteration_number}: "
              f"Reward={m.avg_reward_last_100:.2f}, "
              f"WinRate={m.win_rate_last_100:.2%}")
```

## ⚙️ Configuration

Edit `config.py` to customize:

- **Server**: HOST, PORT, DEBUG
- **Training**: Learning rate, batch size, episodes
- **Arena**: Ring size, max fighters
- **Agent**: Network architecture, observation size

## 🐛 Troubleshooting

### Database Locked
- Ensure only one trainer process runs at a time
- Delete `data/databases/arena.db` to reset

### Out of Memory
- Reduce batch_size in TrainingConfig
- Use streaming API calls instead of loading all episodes

### Training Not Improving
- Check reward function (is agent getting feedback?)
- Increase entropy coefficient for more exploration
- Verify observation vector is being computed correctly

## 🔮 Future Enhancements

- [ ] Distributed training (multiple workers)
- [ ] Curriculum learning (start with 1v1, scale to tournaments)
- [ ] Transfer learning between models
- [ ] Adversarial training (defensive vs aggressive)
- [ ] Genetic algorithm for population evolution
- [ ] WASM inference for real-time in-browser predictions

## 📚 References

- PufferLib: https://github.com/pufferai/pufferlib
- PPO Paper: https://arxiv.org/abs/1707.06347
- Gymnasium (Gym): https://gymnasium.farama.org/
- PyTorch: https://pytorch.org/

## 📝 License

Same as main project.

---

**Last Updated**: 2025-10-31
**Version**: 1.0.0
