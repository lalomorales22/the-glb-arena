"""
PufferLib environment wrapper for the wrestling arena
"""
import numpy as np
from typing import Dict, List, Tuple, Any
import gymnasium as gym
from gymnasium import spaces

from config import AGENT_CONFIG, ARENA_CONFIG


class WrestlingArenaEnv(gym.Env):
    """
    OpenAI Gym environment wrapper for the wrestling arena.
    Designed to work with PufferLib for reinforcement learning.

    State Space (Observation):
    - Own position (2): [x, z]
    - Own health (1): float
    - Own velocity (2): [vx, vz]
    - Up to 5 nearest enemies (5 * 5 = 25):
      - For each enemy: [distance, rel_x, rel_z, health, threat_level]

    Total observation size: 2 + 1 + 2 + 25 = 30 dimensions

    Action Space:
    - 8 directions (WASD + diagonals) + idle + attack = 10 actions
    """

    metadata = {"render_modes": ["human", "rgb_array"], "render_fps": 60}

    def __init__(self, config: Dict[str, Any] = None):
        super().__init__()

        # Use provided config or defaults
        self.config = config or {}
        self.ring_size = self.config.get("ring_size", ARENA_CONFIG["ring_size"])
        self.max_episode_length = self.config.get("max_episode_length", 5000)

        # Observation space: 30-dimensional vector
        self.observation_space = spaces.Box(
            low=-np.inf,
            high=np.inf,
            shape=(30,),
            dtype=np.float32,
        )

        # Action space: 10 discrete actions
        # 0-7: Movement (N, NE, E, SE, S, SW, W, NW)
        # 8: Idle (no movement)
        # 9: Attack
        self.action_space = spaces.Discrete(10)

        # Current state (will be set in reset)
        self.state = None
        self.fighter_state = None
        self.enemies = []
        self.step_count = 0

        # Reward tracking
        self.cumulative_reward = 0.0
        self.last_health = 100.0

    def reset(self, *, seed=None, options=None):
        """
        Reset the environment for a new episode.
        In real usage, this would be called with game state from the arena.
        """
        super().reset(seed=seed)

        # Initialize fighter state
        self.fighter_state = {
            "position": np.array([0.0, 0.0], dtype=np.float32),
            "health": 100.0,
            "velocity": np.array([0.0, 0.0], dtype=np.float32),
            "alive": True,
        }

        # Initialize enemies (would be populated in real scenario)
        self.enemies = []

        self.step_count = 0
        self.cumulative_reward = 0.0
        self.last_health = 100.0

        obs = self._get_observation()
        info = self._get_info()

        return obs, info

    def step(self, action: int) -> Tuple[np.ndarray, float, bool, bool, Dict]:
        """
        Execute one step in the environment.

        Args:
            action: Integer action (0-9)
                0-7: Movement directions
                8: Idle
                9: Attack

        Returns:
            observation, reward, terminated, truncated, info
        """
        self.step_count += 1

        # Decode action
        move_action, attack_action = self._decode_action(action)

        # Calculate reward for this step
        reward = self._calculate_reward(attack_action)

        # Check termination conditions
        terminated = not self.fighter_state["alive"]
        truncated = self.step_count >= self.max_episode_length

        obs = self._get_observation()
        info = self._get_info()

        self.cumulative_reward += reward
        self.last_health = self.fighter_state["health"]

        return obs, reward, terminated, truncated, info

    def _get_observation(self) -> np.ndarray:
        """
        Get the current observation vector (flattened for NN input).

        Returns numpy array of shape (30,):
        - [0:2] own position
        - [2] own health (normalized to [0, 1])
        - [3:5] own velocity
        - [5:30] enemy observations (up to 5 enemies * 5 values each)
        """
        obs = np.zeros(30, dtype=np.float32)

        # Own position
        pos = self.fighter_state["position"]
        obs[0] = pos[0] / (self.ring_size / 2)  # Normalize by half ring size
        obs[1] = pos[1] / (self.ring_size / 2)

        # Own health (normalized)
        obs[2] = self.fighter_state["health"] / 100.0

        # Own velocity
        vel = self.fighter_state["velocity"]
        obs[3] = vel[0]
        obs[4] = vel[1]

        # Enemy observations (sorted by distance)
        enemies_sorted = sorted(self.enemies, key=lambda e: e["distance"])
        for i, enemy in enumerate(enemies_sorted[:5]):  # Max 5 enemies
            base_idx = 5 + (i * 5)
            obs[base_idx + 0] = enemy["distance"] / (self.ring_size / 2)
            obs[base_idx + 1] = enemy["rel_position"][0] / (self.ring_size / 2)
            obs[base_idx + 2] = enemy["rel_position"][1] / (self.ring_size / 2)
            obs[base_idx + 3] = enemy["health"] / 100.0
            obs[base_idx + 4] = enemy["threat_level"]

        return obs

    def _decode_action(self, action: int) -> Tuple[Tuple[float, float], bool]:
        """
        Decode discrete action to movement vector and attack bool.

        Returns:
            (move_vector, attack_bool)
        """
        # Movement directions
        movements = {
            0: (0.0, 1.0),      # N
            1: (0.707, 0.707),  # NE
            2: (1.0, 0.0),      # E
            3: (0.707, -0.707), # SE
            4: (0.0, -1.0),     # S
            5: (-0.707, -0.707),# SW
            6: (-1.0, 0.0),     # W
            7: (-0.707, 0.707), # NW
            8: (0.0, 0.0),      # Idle
            9: (0.0, 0.0),      # Attack (no movement)
        }

        move = movements[action]
        attack = action == 9

        return move, attack

    def _calculate_reward(self, attack_action: bool) -> float:
        """
        Calculate reward for this step.

        Reward structure:
        - +2: Successful attack on opponent
        - +1: Each frame survived
        - -1: Taking damage
        - -3: Getting knocked to edge (risky position)
        - -5: Knocked out of ring (died)
        """
        reward = 1.0  # Survival reward

        # Penalize damage taken
        health_delta = self.fighter_state["health"] - self.last_health
        if health_delta < 0:
            reward += health_delta  # Negative reward for taking damage

        # Reward for attacking (would be confirmed by arena)
        if attack_action:
            reward += 0.5  # Attempt bonus

        # Penalize being near edge
        pos = self.fighter_state["position"]
        distance_to_edge = min(
            abs(pos[0]) - (self.ring_size / 2 - 5),
            abs(pos[1]) - (self.ring_size / 2 - 5),
        )
        if distance_to_edge > 0:
            reward -= 1.0

        # Check if knocked out
        if not self.fighter_state["alive"]:
            reward -= 10.0

        return reward

    def _get_info(self) -> Dict[str, Any]:
        """Get additional info for debugging/logging"""
        return {
            "step": self.step_count,
            "health": self.fighter_state["health"],
            "position": self.fighter_state["position"].copy(),
            "cumulative_reward": self.cumulative_reward,
            "enemies_visible": len(self.enemies),
        }

    def set_game_state(self, game_state: Dict[str, Any]):
        """
        Update the environment with new game state from the arena.
        Called during actual gameplay to sync the RL env with game reality.

        Args:
            game_state: Dict containing:
                - fighter_position: [x, z]
                - fighter_health: float
                - fighter_velocity: [vx, vz]
                - enemies: List of enemy dicts with pos, health, etc
                - is_alive: bool
        """
        self.fighter_state["position"] = np.array(
            game_state["fighter_position"], dtype=np.float32
        )
        self.fighter_state["health"] = game_state["fighter_health"]
        self.fighter_state["velocity"] = np.array(
            game_state["fighter_velocity"], dtype=np.float32
        )
        self.fighter_state["alive"] = game_state.get("is_alive", True)

        # Process enemies
        self.enemies = []
        own_pos = self.fighter_state["position"]
        for enemy in game_state.get("enemies", []):
            enemy_pos = np.array(enemy["position"], dtype=np.float32)
            rel_pos = enemy_pos - own_pos
            distance = np.linalg.norm(rel_pos)

            self.enemies.append({
                "position": enemy_pos,
                "rel_position": rel_pos,
                "distance": distance,
                "health": enemy["health"],
                "threat_level": self._calculate_threat(distance, enemy["health"]),
                "id": enemy.get("id"),
            })

    def _calculate_threat(self, distance: float, enemy_health: float) -> float:
        """
        Calculate threat level of an enemy (0-1).
        Higher = more dangerous.
        """
        # Closer enemies are more threatening
        distance_threat = 1.0 / (1.0 + distance / 10.0)

        # Healthier enemies are more threatening
        health_threat = enemy_health / 100.0

        return (distance_threat + health_threat) / 2.0

    def render(self):
        """Render the environment (not needed for headless training)"""
        pass

    def close(self):
        """Cleanup"""
        pass
