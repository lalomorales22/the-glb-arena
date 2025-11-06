"""
Example script showing how to train a fighting agent.

This demonstrates the complete training pipeline:
1. Create environment
2. Collect trajectories
3. Train the neural network
4. Save checkpoints
5. Evaluate performance
"""
import logging
from pathlib import Path
from datetime import datetime

from rl.environment import WrestlingArenaEnv
from rl.training import create_training_session, TrainingConfig
from rl.agent import AgentCheckpoint
from config import MODELS_DIR
from database import get_db_manager
from database.models import ModelCheckpoint

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s - %(levelname)s - %(message)s",
)
logger = logging.getLogger(__name__)


def train_fighter(
    fighter_id: int,
    num_training_epochs: int = 10,
    episodes_per_epoch: int = 50,
    training_mode: str = "survival",
):
    """
    Train a fighter agent using reinforcement learning.

    Args:
        fighter_id: ID of the fighter to train
        num_training_epochs: Number of training epochs
        episodes_per_epoch: Episodes to collect per epoch
        training_mode: Training objective ("survival", "aggression", etc)
    """
    logger.info(f"🎯 Starting training for fighter {fighter_id} in {training_mode} mode")

    # Create training configuration
    config = TrainingConfig(
        learning_rate=3e-4,
        gamma=0.99,
        gae_lambda=0.95,
        entropy_coef=0.01,
        batch_size=32,
        num_epochs=3,
    )

    # Create trainer
    trainer = create_training_session(fighter_id, config)

    # Create environment
    env = WrestlingArenaEnv()

    # Training loop
    best_avg_reward = -float("inf")

    for epoch in range(num_training_epochs):
        logger.info(f"\n📊 Epoch {epoch + 1}/{num_training_epochs}")

        epoch_rewards = []

        # Collect trajectories
        for episode in range(episodes_per_epoch):
            stats = trainer.collect_trajectory(env, max_steps=5000)
            epoch_rewards.append(stats["episode_reward"])

        # Train on collected data
        metrics = trainer.train_step()

        # Log metrics
        avg_reward = sum(epoch_rewards) / len(epoch_rewards)
        logger.info(f"  Avg Reward: {avg_reward:.2f}")
        logger.info(f"  Policy Loss: {metrics['policy_loss']:.4f}")
        logger.info(f"  Value Loss: {metrics['value_loss']:.4f}")
        logger.info(f"  Entropy: {metrics['entropy']:.4f}")

        # Save best model
        if avg_reward > best_avg_reward:
            best_avg_reward = avg_reward
            checkpoint_path = MODELS_DIR / f"fighter_{fighter_id}_best.pth"
            trainer.save_checkpoint(
                str(checkpoint_path),
                metadata={
                    "fighter_id": fighter_id,
                    "epoch": epoch,
                    "avg_reward": avg_reward,
                    "training_mode": training_mode,
                },
            )
            logger.info(f"  ✅ New best model saved: {checkpoint_path}")

        # Save periodic checkpoint
        if (epoch + 1) % 5 == 0:
            checkpoint_path = MODELS_DIR / f"fighter_{fighter_id}_epoch_{epoch}.pth"
            trainer.save_checkpoint(str(checkpoint_path))
            logger.info(f"  💾 Checkpoint saved: {checkpoint_path}")

    logger.info(f"\n🏆 Training completed! Best avg reward: {best_avg_reward:.2f}")
    return trainer


def evaluate_agent(
    fighter_id: int,
    model_path: str,
    num_episodes: int = 10,
):
    """
    Evaluate a trained agent.

    Args:
        fighter_id: ID of the fighter
        model_path: Path to saved model weights
        num_episodes: Number of evaluation episodes
    """
    logger.info(f"\n📈 Evaluating fighter {fighter_id}")

    # Load trained model
    model = AgentCheckpoint.load(model_path)

    # Create environment
    env = WrestlingArenaEnv()

    # Run evaluation episodes
    episode_rewards = []

    for episode in range(num_episodes):
        obs, _ = env.reset()
        episode_reward = 0.0

        for step in range(5000):
            # Get action from trained model
            action, _ = model.get_action_and_value(obs)

            # Step environment
            next_obs, reward, terminated, truncated, _ = env.step(action)
            episode_reward += reward

            obs = next_obs

            if terminated or truncated:
                break

        episode_rewards.append(episode_reward)
        logger.info(f"  Episode {episode + 1}: Reward = {episode_reward:.2f}")

    avg_reward = sum(episode_rewards) / len(episode_rewards)
    logger.info(f"\n📊 Average Evaluation Reward: {avg_reward:.2f}")
    logger.info(f"   Max: {max(episode_rewards):.2f}, Min: {min(episode_rewards):.2f}")

    return avg_reward


def register_model_in_database(
    fighter_id: int,
    weights_path: str,
    model_version: int,
    training_iteration: int,
    avg_reward: float = 0.0,
    training_mode: str = "survival",
):
    """Register a trained model in the database"""
    try:
        db_manager = get_db_manager()
        session = db_manager.get_session()

        checkpoint = ModelCheckpoint(
            fighter_id=fighter_id,
            model_version=model_version,
            training_iteration=training_iteration,
            weights_path=str(weights_path),
            avg_reward=avg_reward,
            win_rate=0.0,  # Will be updated by evaluation
            total_episodes_trained=0,
            training_mode=training_mode,
            created_at=datetime.utcnow(),
        )

        session.add(checkpoint)
        session.commit()
        session.close()

        logger.info(f"✅ Model registered in database: v{model_version} - {weights_path}")
        return checkpoint.id
    except Exception as e:
        logger.error(f"❌ Failed to register model in database: {e}")
        return None


def compare_models(fighter_id: int, model_paths: list):
    """Compare performance of multiple model versions"""
    logger.info(f"\n🏁 Comparing models for fighter {fighter_id}")

    for i, path in enumerate(model_paths):
        logger.info(f"\nModel {i + 1}: {path}")
        evaluate_agent(fighter_id, path, num_episodes=5)


if __name__ == "__main__":
    # Ensure models directory exists
    MODELS_DIR.mkdir(parents=True, exist_ok=True)

    # Example: Train a fighter
    logger.info("=" * 60)
    logger.info("WRESTLING ARENA - AGENT TRAINING EXAMPLE")
    logger.info("=" * 60)

    # Train in survival mode (learn to stay alive)
    trainer = train_fighter(
        fighter_id=1,
        num_training_epochs=5,  # Short for demo
        episodes_per_epoch=20,  # Few episodes for demo
        training_mode="survival",
    )

    # Evaluate the trained agent and register in database
    best_model_path = MODELS_DIR / "fighter_1_best.pth"
    if best_model_path.exists():
        logger.info("\n📊 Evaluating best model...")
        avg_eval_reward = evaluate_agent(1, str(best_model_path), num_episodes=5)

        # Register the best model in the database so it can be loaded by the game
        logger.info("\n💾 Registering model in database...")
        checkpoint_id = register_model_in_database(
            fighter_id=1,
            weights_path=str(best_model_path),
            model_version=1,
            training_iteration=5,
            avg_reward=avg_eval_reward,
            training_mode="survival",
        )

        if checkpoint_id:
            logger.info(f"✅ Model is now available for the game (ID: {checkpoint_id})")
        else:
            logger.warning("⚠️ Model was saved but could not be registered in database")
    else:
        logger.warning(f"⚠️ Best model not found at {best_model_path}")

    logger.info("\n✅ Training example completed!")
    logger.info(f"Models saved to: {MODELS_DIR}")
    logger.info("The trained model is now available to the game!")
