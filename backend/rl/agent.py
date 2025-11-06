"""
Neural network agent for fighting strategy learning
"""
import torch
import torch.nn as nn
from typing import Tuple, List
import numpy as np


class FighterPolicyNetwork(nn.Module):
    """
    Neural network for learning fighter behavior.

    Input: 30-dimensional observation vector
    Hidden: 2 layers of 128 neurons with ReLU activation
    Output:
      - Policy head: 10 action logits (softmax for action selection)
      - Value head: 1 scalar estimate of episode return (baseline for variance reduction)
    """

    def __init__(
        self,
        observation_size: int = 30,
        hidden_size: int = 128,
        num_hidden_layers: int = 2,
        action_size: int = 10,
    ):
        super().__init__()

        self.observation_size = observation_size
        self.hidden_size = hidden_size
        self.action_size = action_size

        # Shared feature extraction layers
        layers = []
        in_features = observation_size

        for i in range(num_hidden_layers):
            layers.append(nn.Linear(in_features, hidden_size))
            layers.append(nn.ReLU())
            in_features = hidden_size

        self.feature_layers = nn.Sequential(*layers)

        # Policy head (outputs action logits)
        self.policy_head = nn.Sequential(
            nn.Linear(hidden_size, hidden_size),
            nn.ReLU(),
            nn.Linear(hidden_size, action_size),
        )

        # Value head (outputs single value estimate)
        self.value_head = nn.Sequential(
            nn.Linear(hidden_size, hidden_size),
            nn.ReLU(),
            nn.Linear(hidden_size, 1),
        )

        # Initialize weights
        self._initialize_weights()

    def _initialize_weights(self):
        """Initialize weights using Xavier initialization"""
        for module in self.modules():
            if isinstance(module, nn.Linear):
                nn.init.xavier_uniform_(module.weight)
                if module.bias is not None:
                    nn.init.constant_(module.bias, 0.0)

    def forward(self, observation: torch.Tensor) -> Tuple[torch.Tensor, torch.Tensor]:
        """
        Forward pass through the network.

        Args:
            observation: Tensor of shape (batch_size, observation_size)

        Returns:
            (action_logits, value_estimate)
            - action_logits: (batch_size, action_size)
            - value_estimate: (batch_size, 1)
        """
        # Extract features
        features = self.feature_layers(observation)

        # Policy and value outputs
        action_logits = self.policy_head(features)
        value_estimate = self.value_head(features)

        return action_logits, value_estimate

    def get_action_and_value(
        self,
        observation: np.ndarray,
        device: torch.device = torch.device("cpu"),
    ) -> Tuple[int, float]:
        """
        Get action and value estimate from observation.
        Used during gameplay.

        Args:
            observation: numpy array of shape (30,)
            device: torch device

        Returns:
            (action, value_estimate)
        """
        with torch.no_grad():
            obs_tensor = torch.from_numpy(observation).float().unsqueeze(0).to(device)
            logits, value = self.forward(obs_tensor)

            # Sample action from policy distribution
            probs = torch.softmax(logits, dim=-1)
            action = torch.multinomial(probs, num_samples=1).item()

            # Get value estimate
            value_est = value.squeeze().item()

        return action, value_est

    def get_action_probs(self, observation: np.ndarray) -> np.ndarray:
        """Get action probabilities from observation"""
        with torch.no_grad():
            obs_tensor = torch.from_numpy(observation).float().unsqueeze(0)
            logits, _ = self.forward(obs_tensor)
            probs = torch.softmax(logits, dim=-1)
        return probs.squeeze().numpy()


class AgentCheckpoint:
    """Manages saving and loading agent checkpoints"""

    @staticmethod
    def save(
        model: FighterPolicyNetwork,
        filepath: str,
        metadata: dict = None,
    ):
        """Save model checkpoint"""
        checkpoint = {
            "model_state_dict": model.state_dict(),
            "model_config": {
                "observation_size": model.observation_size,
                "hidden_size": model.hidden_size,
                "action_size": model.action_size,
            },
            "metadata": metadata or {},
        }
        torch.save(checkpoint, filepath)

    @staticmethod
    def load(filepath: str, device: torch.device = torch.device("cpu")) -> FighterPolicyNetwork:
        """Load model from checkpoint"""
        checkpoint = torch.load(filepath, map_location=device)
        config = checkpoint["model_config"]

        model = FighterPolicyNetwork(
            observation_size=config["observation_size"],
            hidden_size=config["hidden_size"],
            action_size=config["action_size"],
        )
        model.load_state_dict(checkpoint["model_state_dict"])
        model.to(device)
        model.eval()

        return model
