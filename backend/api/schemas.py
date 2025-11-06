"""
Pydantic schemas for request/response validation
"""
from pydantic import BaseModel
from typing import List, Optional, Dict, Any
from datetime import datetime


class FighterMetadataSchema(BaseModel):
    """Fighter metadata"""
    size: float = 1.0
    speed_multiplier: float = 1.0
    damage_multiplier: float = 1.0


class FighterCreateSchema(BaseModel):
    """Create a new fighter"""
    glb_filename: str
    metadata_json: Optional[Dict[str, Any]] = None


class FighterSchema(BaseModel):
    """Fighter response"""
    id: int
    glb_filename: str
    model_version: int
    created_at: datetime
    metadata_json: Optional[Dict[str, Any]]

    class Config:
        from_attributes = True


class FightFrameCreateSchema(BaseModel):
    """Create a fight frame record"""
    episode_id: int
    frame_number: int
    fighter_position: List[float]  # [x, z]
    fighter_health: float
    fighter_velocity: List[float]  # [vx, vz]
    enemies_state: List[Dict[str, Any]]
    action_vector: List[float]
    reward_delta: float
    cumulative_reward: float
    observation_vector: List[float]


class FightFrameSchema(BaseModel):
    """Fight frame response"""
    id: int
    episode_id: int
    frame_number: int
    fighter_position: List[float]
    fighter_health: float
    reward_delta: float
    cumulative_reward: float

    class Config:
        from_attributes = True


class EpisodeCreateSchema(BaseModel):
    """Create a new episode"""
    fighter_id: int
    episode_number: int
    opponent_ids: List[int]
    total_reward: float = 0.0
    duration_frames: int = 0
    is_victory: bool = False


class EpisodeSchema(BaseModel):
    """Episode response"""
    id: int
    fighter_id: int
    episode_number: int
    opponent_ids: List[int]
    total_reward: float
    duration_frames: int
    is_victory: bool
    rank: int
    started_at: datetime
    ended_at: Optional[datetime]

    class Config:
        from_attributes = True


class EpisodeCompleteSchema(BaseModel):
    """Complete an ongoing episode"""
    total_reward: float
    duration_frames: int
    is_victory: bool
    rank: int


class ModelCheckpointSchema(BaseModel):
    """Model checkpoint response"""
    id: int
    fighter_id: int
    model_version: int
    training_iteration: int
    win_rate: float
    avg_reward: float
    total_episodes_trained: int
    weights_path: str
    training_mode: Optional[str]
    created_at: datetime

    class Config:
        from_attributes = True


class TrainingSessionStartSchema(BaseModel):
    """Start a new training session"""
    fighter_id: int
    training_mode: str = "survival"
    num_episodes: int = 100


class HealthCheckSchema(BaseModel):
    """Health check response"""
    status: str
    timestamp: datetime
    database_ready: bool


class FighterProfileCreateSchema(BaseModel):
    """Create a new fighter personality profile"""
    fighter_id: int
    profile_name: str
    aggression: float = 50.0          # 0-100
    positioning: float = 50.0         # 0-100
    targeting: float = 50.0           # 0-100
    risk_tolerance: float = 50.0      # 0-100
    endurance: float = 50.0           # 0-100
    model_path: str                   # Path to trained model file


class FighterProfileSchema(BaseModel):
    """Fighter personality profile response"""
    id: int
    fighter_id: int
    profile_name: str
    aggression: float
    positioning: float
    targeting: float
    risk_tolerance: float
    endurance: float
    model_path: str
    model_version: int
    win_rate: float
    total_episodes: int
    avg_reward: float
    created_at: datetime
    updated_at: datetime

    class Config:
        from_attributes = True


class TrainingJobCreateSchema(BaseModel):
    """Create a new training job"""
    fighter_id: int
    profile_name: str
    epochs: int
    aggression: float = 50.0
    positioning: float = 50.0
    targeting: float = 50.0
    risk_tolerance: float = 50.0
    endurance: float = 50.0


class TrainingJobSchema(BaseModel):
    """Training job response"""
    id: int
    fighter_id: int
    profile_name: str
    status: str                  # pending, in_progress, completed, failed
    progress: float              # 0-100
    epochs: int
    aggression: float
    positioning: float
    targeting: float
    risk_tolerance: float
    endurance: float
    final_reward: Optional[float]
    final_win_rate: Optional[float]
    error_message: Optional[str]
    created_at: datetime
    started_at: Optional[datetime]
    completed_at: Optional[datetime]

    class Config:
        from_attributes = True
