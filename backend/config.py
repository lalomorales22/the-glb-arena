"""
Configuration management for the wrestling arena backend
"""
import os
from pathlib import Path

# Project paths
PROJECT_ROOT = Path(__file__).parent
DATA_DIR = PROJECT_ROOT / "data"
DATABASE_DIR = DATA_DIR / "databases"
MODELS_DIR = DATA_DIR / "models"

# Ensure directories exist
DATABASE_DIR.mkdir(parents=True, exist_ok=True)
MODELS_DIR.mkdir(parents=True, exist_ok=True)

# Database
DATABASE_URL = f"sqlite:///{DATABASE_DIR}/arena.db"
DB_PATH = DATABASE_DIR / "arena.db"

# Server
HOST = os.getenv("HOST", "localhost")
PORT = int(os.getenv("PORT", 8001))
DEBUG = os.getenv("DEBUG", "True").lower() == "true"

# Training config
TRAINING_CONFIG = {
    "batch_size": 32,
    "learning_rate": 3e-4,
    "episodes_per_training": 100,
    "max_episode_length": 5000,  # frames
    "num_workers": 4,
}

# Arena config
ARENA_CONFIG = {
    "ring_size": 100,  # units
    "num_spectators": 400,
    "max_fighters": 8,
}

# Agent config
AGENT_CONFIG = {
    "observation_size": 38,  # Defined in environment.py
    "hidden_size": 128,
    "num_hidden_layers": 2,
    "action_size": 10,  # 8 directions + idle + attack
}
