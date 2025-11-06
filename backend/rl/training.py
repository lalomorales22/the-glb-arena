"""
PufferLib training loop for the wrestling arena
"""
import torch
import torch.nn as nn
import torch.optim as optim
import numpy as np
from typing import Dict, List, Tuple, Any
from dataclasses import dataclass
import logging

from rl.agent import FighterPolicyNetwork, AgentCheckpoint
from rl.environment import WrestlingArenaEnv

logger = logging.getLogger(__name__)


@dataclass
class TrainingConfig:
    """Training hyperparameters"""
    learning_rate: float = 3e-4
    gamma: float = 0.99  # Discount factor
    gae_lambda: float = 0.95  # GAE lambda for advantage estimation
    entropy_coef: float = 0.01  # Entropy bonus to encourage exploration
    value_loss_coef: float = 0.5  # Value function weight
    max_grad_norm: float = 0.5  # Gradient clipping
    num_epochs: int = 3  # PPO epochs per batch
    batch_size: int = 32
    num_workers: int = 4


class RolloutBuffer:
    """Stores trajectories from environment interaction"""

    def __init__(self):
        self.observations = []
        self.actions = []
        self.rewards = []
        self.values = []
        self.log_probs = []
        self.dones = []
        self.advantages = []
        self.returns = []

    def add(
        self,
        obs: np.ndarray,
        action: int,
        reward: float,
        value: float,
        log_prob: float,
        done: bool,
    ):
        """Add transition to buffer"""
        self.observations.append(obs)
        self.actions.append(action)
        self.rewards.append(reward)
        self.values.append(value)
        self.log_probs.append(log_prob)
        self.dones.append(done)

    def compute_returns_and_advantages(self, gamma: float = 0.99, gae_lambda: float = 0.95):
        """
        Compute returns and advantages using Generalized Advantage Estimation (GAE).
        """
        self.advantages = []
        self.returns = []

        gae = 0.0
        next_value = 0.0

        for t in reversed(range(len(self.rewards))):
            if t == len(self.rewards) - 1:
                next_non_terminal = 0.0 if self.dones[t] else 1.0
            else:
                next_non_terminal = 0.0 if self.dones[t] else 1.0
                next_value = self.values[t + 1]

            delta = (
                self.rewards[t]
                + gamma * next_value * next_non_terminal
                - self.values[t]
            )

            gae = delta + gamma * gae_lambda * next_non_terminal * gae
            ret = gae + self.values[t]

            self.advantages.insert(0, gae)
            self.returns.insert(0, ret)

    def get_batch(self, batch_size: int):
        """Get a batch of data for training"""
        indices = np.random.permutation(len(self.observations))
        for start in range(0, len(self.observations), batch_size):
            batch_indices = indices[start : start + batch_size]
            yield {
                "observations": torch.FloatTensor(
                    np.array([self.observations[i] for i in batch_indices])
                ),
                "actions": torch.LongTensor(
                    np.array([self.actions[i] for i in batch_indices])
                ),
                "old_log_probs": torch.FloatTensor(
                    np.array([self.log_probs[i] for i in batch_indices])
                ),
                "returns": torch.FloatTensor(
                    np.array([self.returns[i] for i in batch_indices])
                ),
                "advantages": torch.FloatTensor(
                    np.array([self.advantages[i] for i in batch_indices])
                ),
            }

    def clear(self):
        """Clear the buffer"""
        self.observations.clear()
        self.actions.clear()
        self.rewards.clear()
        self.values.clear()
        self.log_probs.clear()
        self.dones.clear()
        self.advantages.clear()
        self.returns.clear()


class Trainer:
    """
    Trainer for the fighting agents using PPO (Proximal Policy Optimization).
    """

    def __init__(
        self,
        model: FighterPolicyNetwork,
        config: TrainingConfig = None,
        device: torch.device = None,
    ):
        self.model = model
        self.config = config or TrainingConfig()
        self.device = device or torch.device("cuda" if torch.cuda.is_available() else "cpu")

        self.model.to(self.device)

        self.optimizer = optim.Adam(self.model.parameters(), lr=self.config.learning_rate)
        self.buffer = RolloutBuffer()

        logger.info(f"Trainer initialized on device: {self.device}")

    def collect_trajectory(
        self,
        env: WrestlingArenaEnv,
        max_steps: int = 1000,
    ) -> Dict[str, Any]:
        """
        Collect one trajectory (episode) from the environment.

        Returns:
            Dict with episode statistics
        """
        obs, _ = env.reset()
        episode_reward = 0.0
        episode_length = 0

        for step in range(max_steps):
            # Get action and value from model
            with torch.no_grad():
                obs_tensor = torch.from_numpy(obs).float().unsqueeze(0).to(self.device)
                action_logits, value = self.model(obs_tensor)

                # Sample action from policy
                probs = torch.softmax(action_logits, dim=-1)
                action = torch.multinomial(probs, num_samples=1).item()
                log_prob = torch.log(probs[0, action]).item()

            # Step environment
            next_obs, reward, terminated, truncated, info = env.step(action)
            done = terminated or truncated

            # Store in buffer
            self.buffer.add(
                obs=obs,
                action=action,
                reward=reward,
                value=value.squeeze().item(),
                log_prob=log_prob,
                done=done,
            )

            episode_reward += reward
            episode_length += 1
            obs = next_obs

            if done:
                break

        return {
            "episode_reward": episode_reward,
            "episode_length": episode_length,
        }

    def train_step(self) -> Dict[str, float]:
        """
        Perform one training step using PPO.

        Returns:
            Dict with training metrics
        """
        # Compute advantages
        self.buffer.compute_returns_and_advantages(
            gamma=self.config.gamma,
            gae_lambda=self.config.gae_lambda,
        )

        # Normalize advantages
        advantages = np.array(self.buffer.advantages)
        advantages = (advantages - advantages.mean()) / (advantages.std() + 1e-8)

        metrics = {
            "policy_loss": 0.0,
            "value_loss": 0.0,
            "entropy": 0.0,
        }

        # Train for N epochs on batches
        for epoch in range(self.config.num_epochs):
            for batch in self.buffer.get_batch(self.config.batch_size):
                self._train_batch(batch, advantages, metrics)

        # Normalize metrics
        for key in metrics:
            metrics[key] /= (self.config.num_epochs * len(list(self.buffer.get_batch(self.config.batch_size))))

        self.buffer.clear()
        return metrics

    def _train_batch(self, batch: Dict, advantages: np.ndarray, metrics: Dict):
        """Train on a single batch"""
        obs = batch["observations"].to(self.device)
        actions = batch["actions"].to(self.device)
        old_log_probs = batch["old_log_probs"].to(self.device)
        returns = batch["returns"].to(self.device)

        # Forward pass
        action_logits, values = self.model(obs)
        values = values.squeeze()

        # Compute new log probabilities
        probs = torch.softmax(action_logits, dim=-1)
        log_probs = torch.log(probs.gather(1, actions.unsqueeze(1)).squeeze())

        # Advantage estimation
        advantages_t = returns - values.detach()
        advantages_t = (advantages_t - advantages_t.mean()) / (advantages_t.std() + 1e-8)

        # Policy loss (PPO)
        ratio = torch.exp(log_probs - old_log_probs)
        surr1 = ratio * advantages_t
        surr2 = torch.clamp(ratio, 1 - 0.2, 1 + 0.2) * advantages_t
        policy_loss = -torch.min(surr1, surr2).mean()

        # Value loss
        value_loss = nn.MSELoss()(values, returns)

        # Entropy bonus (exploration)
        entropy = -(probs * torch.log(probs + 1e-8)).sum(1).mean()

        # Total loss
        total_loss = (
            policy_loss
            + self.config.value_loss_coef * value_loss
            - self.config.entropy_coef * entropy
        )

        # Backprop
        self.optimizer.zero_grad()
        total_loss.backward()
        nn.utils.clip_grad_norm_(self.model.parameters(), self.config.max_grad_norm)
        self.optimizer.step()

        # Track metrics
        metrics["policy_loss"] += policy_loss.item()
        metrics["value_loss"] += value_loss.item()
        metrics["entropy"] += entropy.item()

    def save_checkpoint(self, filepath: str, metadata: dict = None):
        """Save model checkpoint"""
        AgentCheckpoint.save(self.model, filepath, metadata)
        logger.info(f"Model saved to {filepath}")

    def load_checkpoint(self, filepath: str):
        """Load model from checkpoint"""
        self.model = AgentCheckpoint.load(filepath, self.device)
        logger.info(f"Model loaded from {filepath}")


def create_training_session(fighter_id: int, config: TrainingConfig = None) -> Trainer:
    """
    Create a new training session for a fighter.

    Args:
        fighter_id: ID of the fighter to train
        config: Training configuration

    Returns:
        Trainer instance ready for training
    """
    # Initialize model
    model = FighterPolicyNetwork(
        observation_size=30,
        hidden_size=128,
        num_hidden_layers=2,
        action_size=10,
    )

    # Create trainer
    trainer = Trainer(model, config or TrainingConfig())

    logger.info(f"Training session created for fighter {fighter_id}")
    return trainer
