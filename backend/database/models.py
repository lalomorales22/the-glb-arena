"""
SQLAlchemy ORM models for the wrestling arena database
"""
from sqlalchemy import Column, Integer, String, Float, DateTime, ForeignKey, JSON, Boolean, Text, Index
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import relationship
from datetime import datetime

Base = declarative_base()


class Fighter(Base):
    """Represents a fighter (GLB model variant)"""
    __tablename__ = "fighters"

    id = Column(Integer, primary_key=True)
    glb_filename = Column(String(255), nullable=False)
    model_version = Column(Integer, default=1)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    # Metadata about the fighter
    metadata_json = Column(JSON, default={})  # size, speed_mult, etc

    # Relationships
    episodes = relationship("Episode", back_populates="fighter")
    checkpoints = relationship("ModelCheckpoint", back_populates="fighter")
    profiles = relationship("FighterProfile", back_populates="fighter")
    training_jobs = relationship("TrainingJob", back_populates="fighter")

    def __repr__(self):
        return f"<Fighter(id={self.id}, glb={self.glb_filename}, v{self.model_version})>"


class Episode(Base):
    """Represents a complete battle episode"""
    __tablename__ = "episodes"

    id = Column(Integer, primary_key=True)
    fighter_id = Column(Integer, ForeignKey("fighters.id"), nullable=False, index=True)
    episode_number = Column(Integer, nullable=False)

    # Battle metadata
    opponent_ids = Column(JSON, default=[])  # List of fighter IDs in the battle
    total_reward = Column(Float, default=0.0)
    duration_frames = Column(Integer, default=0)

    # Results
    is_victory = Column(Boolean, default=False, index=True)
    rank = Column(Integer, default=0)  # 1st place, 2nd place, etc

    # Timestamps
    started_at = Column(DateTime, default=datetime.utcnow, index=True)
    ended_at = Column(DateTime, nullable=True)

    # Relationship
    fighter = relationship("Fighter", back_populates="episodes")
    fight_frames = relationship("FightFrame", back_populates="episode")

    # Index for common queries
    __table_args__ = (
        Index('idx_fighter_started', 'fighter_id', 'started_at'),
    )

    def __repr__(self):
        return f"<Episode(id={self.id}, fighter_id={self.fighter_id}, ep#{self.episode_number})>"


class FightFrame(Base):
    """Represents a single frame/timestep in a fight"""
    __tablename__ = "fight_frames"

    id = Column(Integer, primary_key=True)
    episode_id = Column(Integer, ForeignKey("episodes.id"), nullable=False, index=True)

    # Frame timing
    frame_number = Column(Integer, nullable=False)
    timestamp = Column(DateTime, default=datetime.utcnow)

    # Game state snapshot (JSON for flexibility)
    fighter_position = Column(JSON)      # [x, z]
    fighter_health = Column(Float)
    fighter_velocity = Column(JSON)      # [vx, vz]

    # All visible enemies (for observation vector)
    enemies_state = Column(JSON)  # List of {pos, health, distance}

    # Action taken
    action_vector = Column(JSON)  # [move_x, move_z, attack_bool, target_id]

    # Reward received this frame
    reward_delta = Column(Float, default=0.0)
    cumulative_reward = Column(Float, default=0.0)

    # Neural network observation (flattened for training)
    observation_vector = Column(JSON)  # Raw NN input

    # Relationship
    episode = relationship("Episode", back_populates="fight_frames")

    # Indexes for common queries
    __table_args__ = (
        Index('idx_episode_frame', 'episode_id', 'frame_number'),
    )

    def __repr__(self):
        return f"<FightFrame(episode_id={self.episode_id}, frame={self.frame_number})>"


class ModelCheckpoint(Base):
    """Stores trained model weights and metadata"""
    __tablename__ = "model_checkpoints"

    id = Column(Integer, primary_key=True)
    fighter_id = Column(Integer, ForeignKey("fighters.id"), nullable=False)

    # Model versioning
    model_version = Column(Integer, nullable=False)
    training_iteration = Column(Integer, nullable=False)

    # Performance metrics
    win_rate = Column(Float, default=0.0)
    avg_reward = Column(Float, default=0.0)
    total_episodes_trained = Column(Integer, default=0)

    # File path to saved weights
    weights_path = Column(String(255), nullable=False)

    # Training metadata
    training_mode = Column(String(50))  # "aggression", "survival", "tactical", etc
    created_at = Column(DateTime, default=datetime.utcnow)

    # Relationship
    fighter = relationship("Fighter", back_populates="checkpoints")

    def __repr__(self):
        return f"<ModelCheckpoint(fighter_id={self.fighter_id}, v{self.model_version})>"


class TrainingMetrics(Base):
    """Aggregated training metrics for analysis"""
    __tablename__ = "training_metrics"

    id = Column(Integer, primary_key=True)
    fighter_id = Column(Integer, ForeignKey("fighters.id"), nullable=False)

    # Training session info
    training_session_id = Column(String(100), nullable=False)
    iteration_number = Column(Integer, nullable=False)

    # Metrics snapshot
    avg_reward_last_100 = Column(Float, default=0.0)
    win_rate_last_100 = Column(Float, default=0.0)
    avg_episode_length = Column(Float, default=0.0)

    # Learning progress
    loss = Column(Float, nullable=True)
    policy_entropy = Column(Float, nullable=True)

    recorded_at = Column(DateTime, default=datetime.utcnow)

    def __repr__(self):
        return f"<TrainingMetrics(fighter_id={self.fighter_id}, iter={self.iteration_number})>"


class FighterProfile(Base):
    """Stores personality profile configurations for trained fighter models"""
    __tablename__ = "fighter_profiles"

    id = Column(Integer, primary_key=True)
    fighter_id = Column(Integer, ForeignKey("fighters.id"), nullable=False, index=True)
    profile_name = Column(String(255), nullable=False)

    # Personality parameters (0-100 scale)
    aggression = Column(Float, default=50.0)          # How likely to attack vs defend
    positioning = Column(Float, default=50.0)         # Strategic ring positioning vs random
    targeting = Column(Float, default=50.0)           # Smart target selection vs random
    risk_tolerance = Column(Float, default=50.0)      # Willing to take damage for big hits
    endurance = Column(Float, default=50.0)           # Energy management strategy

    # Reward function weights (normalized)
    reward_weights = Column(JSON, default={})         # Custom weights for reward calculation

    # Model file reference
    model_path = Column(String(255), nullable=False)  # Path to trained .pth file
    model_version = Column(Integer, default=1)

    # Performance metrics
    win_rate = Column(Float, default=0.0)
    total_episodes = Column(Integer, default=0)
    avg_reward = Column(Float, default=0.0)

    # Timestamps
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    # Relationship
    fighter = relationship("Fighter", back_populates="profiles")

    # Index for common queries
    __table_args__ = (
        Index('idx_fighter_profile', 'fighter_id', 'profile_name'),
    )

    def __repr__(self):
        return f"<FighterProfile(fighter_id={self.fighter_id}, profile={self.profile_name})>"


class TrainingJob(Base):
    """Tracks active and completed training jobs for fighter personalities"""
    __tablename__ = "training_jobs"

    id = Column(Integer, primary_key=True)
    fighter_id = Column(Integer, ForeignKey("fighters.id"), nullable=False, index=True)
    profile_name = Column(String(255), nullable=False)

    # Training status
    status = Column(String(50), default="pending")    # pending, in_progress, completed, failed
    progress = Column(Float, default=0.0)             # 0-100 percentage

    # Training configuration
    epochs = Column(Integer, nullable=False)
    learning_rate = Column(Float, default=0.001)

    # Personality parameters used for training
    aggression = Column(Float, default=50.0)
    positioning = Column(Float, default=50.0)
    targeting = Column(Float, default=50.0)
    risk_tolerance = Column(Float, default=50.0)
    endurance = Column(Float, default=50.0)

    # Results
    final_reward = Column(Float, nullable=True)
    final_win_rate = Column(Float, nullable=True)
    error_message = Column(Text, nullable=True)

    # Timestamps
    created_at = Column(DateTime, default=datetime.utcnow)
    started_at = Column(DateTime, nullable=True)
    completed_at = Column(DateTime, nullable=True)

    # Relationship
    fighter = relationship("Fighter", back_populates="training_jobs")

    # Index for common queries
    __table_args__ = (
        Index('idx_training_status', 'fighter_id', 'status'),
    )

    def __repr__(self):
        return f"<TrainingJob(fighter_id={self.fighter_id}, profile={self.profile_name}, status={self.status})>"
