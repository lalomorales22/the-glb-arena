"""
FastAPI routes for the wrestling arena backend
"""
from fastapi import APIRouter, Depends, HTTPException, status, BackgroundTasks
from sqlalchemy.orm import Session
from datetime import datetime
from typing import List, Optional

from database import get_db, Fighter, Episode, FightFrame, ModelCheckpoint, TrainingMetrics, FighterProfile, TrainingJob
from api.schemas import (
    FighterCreateSchema,
    FighterSchema,
    EpisodeCreateSchema,
    EpisodeSchema,
    EpisodeCompleteSchema,
    FightFrameCreateSchema,
    FightFrameSchema,
    ModelCheckpointSchema,
    HealthCheckSchema,
    FighterProfileCreateSchema,
    FighterProfileSchema,
    TrainingJobCreateSchema,
    TrainingJobSchema,
)

router = APIRouter()


# ==================== Health Check ====================
@router.get("/health", response_model=HealthCheckSchema)
def health_check(db: Session = Depends(get_db)):
    """Health check endpoint"""
    try:
        # Test database connection
        db.execute("SELECT 1")
        db_ready = True
    except Exception:
        db_ready = False

    return HealthCheckSchema(
        status="ok" if db_ready else "degraded",
        timestamp=datetime.utcnow(),
        database_ready=db_ready,
    )


# ==================== Fighters ====================
@router.post("/fighters", response_model=FighterSchema, status_code=status.HTTP_201_CREATED)
def create_fighter(fighter: FighterCreateSchema, db: Session = Depends(get_db)):
    """Register a new fighter (GLB model)"""
    # Check if fighter already exists
    existing = db.query(Fighter).filter(Fighter.glb_filename == fighter.glb_filename).first()
    if existing:
        raise HTTPException(
            status_code=status.HTTP_409_CONFLICT,
            detail=f"Fighter '{fighter.glb_filename}' already exists",
        )

    new_fighter = Fighter(
        glb_filename=fighter.glb_filename,
        metadata_json=fighter.metadata_json or {},
    )
    db.add(new_fighter)
    db.commit()
    db.refresh(new_fighter)
    return new_fighter


@router.get("/fighters", response_model=List[FighterSchema])
def list_fighters(db: Session = Depends(get_db)):
    """List all fighters"""
    fighters = db.query(Fighter).all()
    return fighters


@router.get("/fighters/{fighter_id}", response_model=FighterSchema)
def get_fighter(fighter_id: int, db: Session = Depends(get_db)):
    """Get a specific fighter"""
    fighter = db.query(Fighter).filter(Fighter.id == fighter_id).first()
    if not fighter:
        raise HTTPException(status_code=404, detail="Fighter not found")
    return fighter


# ==================== Episodes ====================
@router.post("/episodes", response_model=EpisodeSchema, status_code=status.HTTP_201_CREATED)
def create_episode(episode: EpisodeCreateSchema, db: Session = Depends(get_db)):
    """Start a new training episode"""
    # Verify fighter exists
    fighter = db.query(Fighter).filter(Fighter.id == episode.fighter_id).first()
    if not fighter:
        raise HTTPException(status_code=404, detail="Fighter not found")

    new_episode = Episode(
        fighter_id=episode.fighter_id,
        episode_number=episode.episode_number,
        opponent_ids=episode.opponent_ids,
        total_reward=episode.total_reward,
        duration_frames=episode.duration_frames,
        is_victory=episode.is_victory,
    )
    db.add(new_episode)
    db.commit()
    db.refresh(new_episode)
    return new_episode


@router.get("/episodes/{episode_id}", response_model=EpisodeSchema)
def get_episode(episode_id: int, db: Session = Depends(get_db)):
    """Get episode details"""
    episode = db.query(Episode).filter(Episode.id == episode_id).first()
    if not episode:
        raise HTTPException(status_code=404, detail="Episode not found")
    return episode


@router.patch("/episodes/{episode_id}", response_model=EpisodeSchema)
def complete_episode(
    episode_id: int,
    data: EpisodeCompleteSchema,
    db: Session = Depends(get_db),
):
    """Complete an ongoing episode"""
    episode = db.query(Episode).filter(Episode.id == episode_id).first()
    if not episode:
        raise HTTPException(status_code=404, detail="Episode not found")

    episode.total_reward = data.total_reward
    episode.duration_frames = data.duration_frames
    episode.is_victory = data.is_victory
    episode.rank = data.rank
    episode.ended_at = datetime.utcnow()

    db.commit()
    db.refresh(episode)
    return episode


@router.get("/fighters/{fighter_id}/episodes", response_model=List[EpisodeSchema])
def get_fighter_episodes(
    fighter_id: int,
    skip: int = 0,
    limit: int = 50,
    db: Session = Depends(get_db)
):
    """Get episodes for a fighter with pagination"""
    episodes = db.query(Episode).filter(
        Episode.fighter_id == fighter_id
    ).order_by(Episode.id.desc()).offset(skip).limit(limit).all()
    return episodes


# ==================== Fight Frames ====================
@router.post("/fight-frames", response_model=FightFrameSchema, status_code=status.HTTP_201_CREATED)
def create_fight_frame(frame: FightFrameCreateSchema, db: Session = Depends(get_db)):
    """Record a single frame in a fight"""
    # Verify episode exists
    episode = db.query(Episode).filter(Episode.id == frame.episode_id).first()
    if not episode:
        raise HTTPException(status_code=404, detail="Episode not found")

    new_frame = FightFrame(
        episode_id=frame.episode_id,
        frame_number=frame.frame_number,
        fighter_position=frame.fighter_position,
        fighter_health=frame.fighter_health,
        fighter_velocity=frame.fighter_velocity,
        enemies_state=frame.enemies_state,
        action_vector=frame.action_vector,
        reward_delta=frame.reward_delta,
        cumulative_reward=frame.cumulative_reward,
        observation_vector=frame.observation_vector,
    )
    db.add(new_frame)
    db.commit()
    db.refresh(new_frame)
    return new_frame


@router.get("/episodes/{episode_id}/frames", response_model=List[FightFrameSchema])
def get_episode_frames(
    episode_id: int,
    skip: int = 0,
    limit: int = 100,
    db: Session = Depends(get_db)
):
    """Get frames for an episode with pagination"""
    frames = db.query(FightFrame).filter(
        FightFrame.episode_id == episode_id
    ).order_by(FightFrame.frame_number.asc()).offset(skip).limit(limit).all()
    return frames


# ==================== Model Checkpoints ====================
@router.get("/fighters/{fighter_id}/checkpoints", response_model=List[ModelCheckpointSchema])
def get_fighter_checkpoints(fighter_id: int, db: Session = Depends(get_db)):
    """Get all model checkpoints for a fighter"""
    checkpoints = db.query(ModelCheckpoint).filter(
        ModelCheckpoint.fighter_id == fighter_id
    ).order_by(ModelCheckpoint.model_version.desc()).all()
    return checkpoints


@router.get("/fighters/{fighter_id}/best-model", response_model=Optional[ModelCheckpointSchema])
def get_best_model(fighter_id: int, db: Session = Depends(get_db)):
    """Get the best performing model for a fighter"""
    best = db.query(ModelCheckpoint).filter(
        ModelCheckpoint.fighter_id == fighter_id
    ).order_by(ModelCheckpoint.win_rate.desc(), ModelCheckpoint.model_version.desc()).first()
    return best


# ==================== Statistics ====================
@router.get("/fighters/{fighter_id}/stats")
def get_fighter_stats(fighter_id: int, db: Session = Depends(get_db)):
    """Get comprehensive statistics for a fighter"""
    fighter = db.query(Fighter).filter(Fighter.id == fighter_id).first()
    if not fighter:
        raise HTTPException(status_code=404, detail="Fighter not found")

    episodes = db.query(Episode).filter(Episode.fighter_id == fighter_id).all()

    if not episodes:
        return {
            "fighter_id": fighter_id,
            "glb_filename": fighter.glb_filename,
            "total_episodes": 0,
            "win_rate": 0.0,
            "avg_reward": 0.0,
            "avg_episode_length": 0,
        }

    total_episodes = len(episodes)
    victories = len([e for e in episodes if e.is_victory])
    win_rate = victories / total_episodes if total_episodes > 0 else 0.0
    avg_reward = sum(e.total_reward for e in episodes) / total_episodes
    avg_length = sum(e.duration_frames for e in episodes) / total_episodes

    return {
        "fighter_id": fighter_id,
        "glb_filename": fighter.glb_filename,
        "total_episodes": total_episodes,
        "win_rate": win_rate,
        "avg_reward": avg_reward,
        "avg_episode_length": int(avg_length),
    }


# ==================== Model Inference ====================
@router.post("/fighters/{fighter_id}/inference")
def run_model_inference(fighter_id: int, data: dict, db: Session = Depends(get_db)):
    """
    Run neural network inference on observations
    Used by game frontend for real-time AI decisions

    Supports both:
    - Default model: Best checkpoint for the fighter
    - Profile-specific model: Named personality profile model
    """
    # Check if profile name specified in request
    profile_name = data.get('profile_name', None)

    model_path = None
    model_info = None

    if profile_name and profile_name != 'default':
        # Try to load profile-specific model
        profile = db.query(FighterProfile).filter(
            FighterProfile.fighter_id == fighter_id,
            FighterProfile.profile_name == profile_name
        ).first()

        if profile:
            model_path = profile.model_path
            model_info = f"{profile.profile_name} (Aggression: {profile.aggression})"
        else:
            # Profile not found, fall back to default
            pass

    # Fall back to best default model if no profile specified
    if not model_path:
        best_model = db.query(ModelCheckpoint).filter(
            ModelCheckpoint.fighter_id == fighter_id
        ).order_by(ModelCheckpoint.win_rate.desc()).first()

        if not best_model:
            # No trained models available - this is normal for new games
            logger.debug(f"No trained model available for fighter {fighter_id}, using scripted AI")
            raise HTTPException(status_code=404, detail="No trained model available")

        model_path = best_model.weights_path
        model_info = f"v{best_model.model_version}"

    try:
        # Import trainer to use for inference
        from rl.training import Trainer
        from rl.agent import FighterPolicyNetwork
        import torch
        import os

        # Load the trained model
        if not os.path.exists(model_path):
            # Model file doesn't exist - this is normal for new fighters
            logger.debug(f"Model weights file not found: {model_path}, using scripted AI")
            raise HTTPException(status_code=404, detail=f"Model weights file not found: {model_path}")

        # Create agent and load weights
        agent = FighterPolicyNetwork()
        checkpoint = torch.load(model_path, map_location='cpu')
        agent.load_state_dict(checkpoint['model_state_dict'])
        agent.eval()  # Inference mode

        # Get observation from request
        observation = data.get('observation', [])
        if not observation or len(observation) != 30:
            raise HTTPException(status_code=400, detail="Invalid observation vector")

        # Convert to tensor
        obs_tensor = torch.tensor(observation, dtype=torch.float32).unsqueeze(0)

        # Run inference (no gradients)
        with torch.no_grad():
            logits, value = agent(obs_tensor)

        # Get action probabilities
        action_probs = torch.softmax(logits[0], dim=0).cpu().numpy().tolist()

        # Sample action from distribution
        import numpy as np
        action = int(np.argmax(action_probs))  # Greedy: take best action

        return {
            "action": action,
            "action_probs": action_probs,
            "value": float(value[0].item()),
            "model_info": model_info,
        }

    except HTTPException:
        # Re-raise HTTP exceptions (404 for missing models, etc)
        raise
    except Exception as e:
        import logging
        logging.error(f"Inference failed: {str(e)}")
        raise HTTPException(status_code=500, detail=f"Inference failed: {str(e)}")


# ==================== Fighter Profiles ====================
@router.post("/fighter-profiles", response_model=FighterProfileSchema, status_code=status.HTTP_201_CREATED)
def create_fighter_profile(profile: FighterProfileCreateSchema, db: Session = Depends(get_db)):
    """Create a new personality profile for a fighter"""
    # Verify fighter exists
    fighter = db.query(Fighter).filter(Fighter.id == profile.fighter_id).first()
    if not fighter:
        raise HTTPException(status_code=404, detail="Fighter not found")

    # Check if profile name already exists for this fighter
    existing = db.query(FighterProfile).filter(
        FighterProfile.fighter_id == profile.fighter_id,
        FighterProfile.profile_name == profile.profile_name
    ).first()
    if existing:
        raise HTTPException(
            status_code=status.HTTP_409_CONFLICT,
            detail=f"Profile '{profile.profile_name}' already exists for this fighter"
        )

    new_profile = FighterProfile(
        fighter_id=profile.fighter_id,
        profile_name=profile.profile_name,
        aggression=profile.aggression,
        positioning=profile.positioning,
        targeting=profile.targeting,
        risk_tolerance=profile.risk_tolerance,
        endurance=profile.endurance,
        model_path=profile.model_path,
    )
    db.add(new_profile)
    db.commit()
    db.refresh(new_profile)
    return new_profile


@router.get("/fighter-profiles/{fighter_id}", response_model=List[FighterProfileSchema])
def get_fighter_profiles(fighter_id: int, db: Session = Depends(get_db)):
    """Get all personality profiles for a fighter"""
    # Verify fighter exists
    fighter = db.query(Fighter).filter(Fighter.id == fighter_id).first()
    if not fighter:
        raise HTTPException(status_code=404, detail="Fighter not found")

    profiles = db.query(FighterProfile).filter(
        FighterProfile.fighter_id == fighter_id
    ).order_by(FighterProfile.created_at.desc()).all()
    return profiles


@router.get("/fighter-profiles/{fighter_id}/{profile_name}", response_model=FighterProfileSchema)
def get_fighter_profile(fighter_id: int, profile_name: str, db: Session = Depends(get_db)):
    """Get a specific personality profile"""
    profile = db.query(FighterProfile).filter(
        FighterProfile.fighter_id == fighter_id,
        FighterProfile.profile_name == profile_name
    ).first()
    if not profile:
        raise HTTPException(status_code=404, detail="Profile not found")
    return profile


@router.patch("/fighter-profiles/{fighter_id}/{profile_name}", response_model=FighterProfileSchema)
def update_fighter_profile(fighter_id: int, profile_name: str, data: dict, db: Session = Depends(get_db)):
    """Update a personality profile"""
    profile = db.query(FighterProfile).filter(
        FighterProfile.fighter_id == fighter_id,
        FighterProfile.profile_name == profile_name
    ).first()
    if not profile:
        raise HTTPException(status_code=404, detail="Profile not found")

    # Update performance metrics
    if "win_rate" in data:
        profile.win_rate = data["win_rate"]
    if "total_episodes" in data:
        profile.total_episodes = data["total_episodes"]
    if "avg_reward" in data:
        profile.avg_reward = data["avg_reward"]

    profile.updated_at = datetime.utcnow()
    db.commit()
    db.refresh(profile)
    return profile


@router.delete("/fighter-profiles/{fighter_id}/{profile_name}", status_code=status.HTTP_204_NO_CONTENT)
def delete_fighter_profile(fighter_id: int, profile_name: str, db: Session = Depends(get_db)):
    """Delete a personality profile"""
    profile = db.query(FighterProfile).filter(
        FighterProfile.fighter_id == fighter_id,
        FighterProfile.profile_name == profile_name
    ).first()
    if not profile:
        raise HTTPException(status_code=404, detail="Profile not found")

    db.delete(profile)
    db.commit()
    return None


# ==================== Training Jobs ====================
@router.post("/training-jobs", response_model=TrainingJobSchema, status_code=status.HTTP_201_CREATED)
def create_training_job(job: TrainingJobCreateSchema, db: Session = Depends(get_db)):
    """Create a new training job for a personality profile"""
    # Verify fighter exists
    fighter = db.query(Fighter).filter(Fighter.id == job.fighter_id).first()
    if not fighter:
        raise HTTPException(status_code=404, detail="Fighter not found")

    new_job = TrainingJob(
        fighter_id=job.fighter_id,
        profile_name=job.profile_name,
        epochs=job.epochs,
        aggression=job.aggression,
        positioning=job.positioning,
        targeting=job.targeting,
        risk_tolerance=job.risk_tolerance,
        endurance=job.endurance,
        status="pending",
        progress=0.0,
    )
    db.add(new_job)
    db.commit()
    db.refresh(new_job)
    return new_job


@router.get("/training-jobs/{job_id}", response_model=TrainingJobSchema)
def get_training_job(job_id: int, db: Session = Depends(get_db)):
    """Get status of a training job"""
    job = db.query(TrainingJob).filter(TrainingJob.id == job_id).first()
    if not job:
        raise HTTPException(status_code=404, detail="Training job not found")
    return job


@router.get("/training-jobs/fighter/{fighter_id}", response_model=List[TrainingJobSchema])
def get_fighter_training_jobs(fighter_id: int, db: Session = Depends(get_db)):
    """Get all training jobs for a fighter"""
    # Verify fighter exists
    fighter = db.query(Fighter).filter(Fighter.id == fighter_id).first()
    if not fighter:
        raise HTTPException(status_code=404, detail="Fighter not found")

    jobs = db.query(TrainingJob).filter(
        TrainingJob.fighter_id == fighter_id
    ).order_by(TrainingJob.created_at.desc()).all()
    return jobs


@router.patch("/training-jobs/{job_id}", response_model=TrainingJobSchema)
def update_training_job(job_id: int, data: dict, db: Session = Depends(get_db)):
    """Update training job status (used by training backend)"""
    job = db.query(TrainingJob).filter(TrainingJob.id == job_id).first()
    if not job:
        raise HTTPException(status_code=404, detail="Training job not found")

    if "status" in data:
        job.status = data["status"]
    if "progress" in data:
        job.progress = data["progress"]
    if "started_at" in data:
        job.started_at = data["started_at"]
    if "completed_at" in data:
        job.completed_at = data["completed_at"]
    if "final_reward" in data:
        job.final_reward = data["final_reward"]
    if "final_win_rate" in data:
        job.final_win_rate = data["final_win_rate"]
    if "error_message" in data:
        job.error_message = data["error_message"]

    db.commit()
    db.refresh(job)
    return job


def _execute_training_background(job_id: int):
    """Background task to execute training job"""
    from datetime import datetime as dt
    import random
    import time
    from database import get_db_manager

    db_manager = get_db_manager()
    session = db_manager.get_session()

    try:
        job = session.query(TrainingJob).filter(TrainingJob.id == job_id).first()
        if not job:
            session.close()
            return

        # Update job status to in_progress
        job.status = "in_progress"
        job.started_at = dt.utcnow()
        session.commit()

        # Simulate training with progress updates
        epochs = int(job.epochs)
        simulated_rewards = []

        for epoch in range(epochs):
            # Simulate training step
            time.sleep(0.05)  # Simulate some work

            progress = ((epoch + 1) / epochs) * 100
            job.progress = min(progress, 99)  # Cap at 99 until complete

            # Simulate improving rewards
            reward = 50 + (epoch * 2) + random.uniform(-5, 5)
            simulated_rewards.append(reward)

            session.commit()

        # Mark as complete
        job.status = "completed"
        job.progress = 100
        job.completed_at = dt.utcnow()
        job.final_reward = sum(simulated_rewards) / len(simulated_rewards) if simulated_rewards else 0
        job.final_win_rate = min(0.5 + (epochs / 100), 0.95)  # Up to 95% win rate

        # Create a FighterProfile record from this job
        profile = FighterProfile(
            fighter_id=job.fighter_id,
            profile_name=job.profile_name,
            aggression=job.aggression,
            positioning=job.positioning,
            targeting=job.targeting,
            risk_tolerance=job.risk_tolerance,
            endurance=job.endurance,
            model_path=f"/data/models/{job.fighter_id}_{job.profile_name}.pth",
            model_version=1,
            win_rate=job.final_win_rate,
            avg_reward=job.final_reward,
            total_episodes=epochs * 10,
        )
        session.add(profile)
        session.commit()

    except Exception as e:
        import logging
        logging.error(f"Training execution failed: {str(e)}")
        job.status = "failed"
        job.error_message = str(e)
        job.completed_at = dt.utcnow()
        session.commit()
    finally:
        session.close()


@router.post("/training-jobs/{job_id}/execute")
def execute_training_job(job_id: int, background_tasks: BackgroundTasks, db: Session = Depends(get_db)):
    """
    Execute a training job with simulated progress updates
    Returns immediately and runs training in the background
    """
    job = db.query(TrainingJob).filter(TrainingJob.id == job_id).first()
    if not job:
        raise HTTPException(status_code=404, detail="Training job not found")

    if job.status != "pending":
        raise HTTPException(status_code=400, detail=f"Job is already {job.status}")

    # Add background task to execute training
    background_tasks.add_task(_execute_training_background, job_id)

    return {
        "status": "queued",
        "job_id": job_id,
        "message": "Training job queued for execution. Poll the job status for progress updates."
    }
