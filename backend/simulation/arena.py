"""
Headless arena simulation for training.
Runs battles without graphics, optimized for fast training loops.
"""
import numpy as np
from typing import List, Dict, Tuple, Optional, Any
from dataclasses import dataclass, field


@dataclass
class FighterState:
    """State of a fighter in the arena"""
    id: int
    position: np.ndarray  # [x, z]
    velocity: np.ndarray  # [vx, vz]
    health: float = 100.0
    alive: bool = True
    reward: float = 0.0
    cumulative_reward: float = 0.0


class HeadlessArena:
    """
    Simplified arena simulation without graphics.
    Used for fast training of RL agents.
    """

    def __init__(self, ring_size: float = 100.0, max_steps: int = 5000):
        self.ring_size = ring_size
        self.max_steps = max_steps
        self.step_count = 0

        self.fighters: Dict[int, FighterState] = {}
        self.agent_ids = []  # IDs of agents being trained

    def add_fighter(self, fighter_id: int, is_agent: bool = False):
        """Add a fighter to the arena"""
        self.fighters[fighter_id] = FighterState(
            id=fighter_id,
            position=np.array([np.random.uniform(-20, 20), np.random.uniform(-20, 20)]),
            velocity=np.zeros(2),
        )
        if is_agent:
            self.agent_ids.append(fighter_id)

    def remove_fighter(self, fighter_id: int):
        """Remove a fighter from the arena"""
        if fighter_id in self.fighters:
            del self.fighters[fighter_id]
        if fighter_id in self.agent_ids:
            self.agent_ids.remove(fighter_id)

    def step(self, actions: Dict[int, int]):
        """
        Execute one step of the simulation.

        Args:
            actions: Dict mapping fighter_id to action (0-9)
                0-7: Movement directions
                8: Idle
                9: Attack
        """
        self.step_count += 1

        # Update positions and velocities
        for fighter_id, action in actions.items():
            if fighter_id not in self.fighters:
                continue

            fighter = self.fighters[fighter_id]
            if not fighter.alive:
                continue

            # Decode action
            move_vec = self._decode_movement(action)
            attack = action == 9

            # Update velocity
            fighter.velocity = move_vec * 2.0  # Speed multiplier

            # Update position
            fighter.position += fighter.velocity * 0.1  # Timestep

            # Clamp to ring
            self._apply_ring_boundaries(fighter)

            # Handle attacks
            if attack:
                self._handle_attack(fighter)

            # Natural damage decay (encourages survival)
            fighter.reward = 0.5  # Base survival reward

        # Check ring eliminations
        self._update_ring_status()

    def _decode_movement(self, action: int) -> np.ndarray:
        """Decode action to movement vector"""
        movements = {
            0: np.array([0.0, 1.0]),       # N
            1: np.array([0.707, 0.707]),   # NE
            2: np.array([1.0, 0.0]),       # E
            3: np.array([0.707, -0.707]),  # SE
            4: np.array([0.0, -1.0]),      # S
            5: np.array([-0.707, -0.707]), # SW
            6: np.array([-1.0, 0.0]),      # W
            7: np.array([-0.707, 0.707]),  # NW
            8: np.array([0.0, 0.0]),       # Idle
            9: np.array([0.0, 0.0]),       # Attack
        }
        return movements.get(action, np.array([0.0, 0.0]))

    def _apply_ring_boundaries(self, fighter: FighterState):
        """Apply ring boundaries and penalties for being near edge"""
        half_size = self.ring_size / 2
        edge_buffer = 5.0

        # Clamp position to ring
        fighter.position[0] = np.clip(
            fighter.position[0],
            -half_size + edge_buffer,
            half_size - edge_buffer,
        )
        fighter.position[1] = np.clip(
            fighter.position[1],
            -half_size + edge_buffer,
            half_size - edge_buffer,
        )

        # Penalty for being near edge
        dist_x = min(abs(fighter.position[0]) - (half_size - 10), 0)
        dist_z = min(abs(fighter.position[1]) - (half_size - 10), 0)

        if dist_x > 0 or dist_z > 0:
            fighter.reward -= 1.0  # Risky behavior penalty

    def _update_ring_status(self):
        """Check if any fighters are outside the ring"""
        half_size = self.ring_size / 2

        for fighter in self.fighters.values():
            if not fighter.alive:
                continue

            if abs(fighter.position[0]) > half_size or abs(fighter.position[1]) > half_size:
                fighter.alive = False
                fighter.reward -= 10.0  # Knockout penalty
                fighter.cumulative_reward += fighter.reward

    def _handle_attack(self, attacker: FighterState):
        """Handle attack mechanics"""
        attack_range = 10.0
        attack_damage = 20.0

        for defender in self.fighters.values():
            if defender.id == attacker.id or not defender.alive:
                continue

            distance = np.linalg.norm(defender.position - attacker.position)
            if distance < attack_range:
                # Hit!
                defender.health -= attack_damage
                attacker.reward += 2.0  # Reward for hitting

                if defender.health <= 0:
                    defender.alive = False
                    attacker.reward += 10.0  # Bonus for knockout

    def get_fighter_state(self, fighter_id: int) -> Optional[Dict[str, Any]]:
        """Get current state of a fighter for RL observation"""
        if fighter_id not in self.fighters:
            return None

        fighter = self.fighters[fighter_id]

        # Get visible enemies
        enemies = []
        for other_id, other in self.fighters.items():
            if other_id == fighter_id or not other.alive:
                continue

            distance = np.linalg.norm(other.position - fighter.position)
            if distance < 50.0:  # Visibility range
                enemies.append({
                    "id": other_id,
                    "position": other.position.copy(),
                    "health": other.health,
                    "distance": distance,
                })

        # Sort by distance (closest first)
        enemies.sort(key=lambda e: e["distance"])

        return {
            "fighter_id": fighter_id,
            "position": fighter.position.copy(),
            "health": fighter.health,
            "velocity": fighter.velocity.copy(),
            "is_alive": fighter.alive,
            "enemies": enemies,
        }

    def get_winners(self) -> List[int]:
        """Get list of surviving fighters (winners)"""
        return [f.id for f in self.fighters.values() if f.alive]

    def is_done(self) -> bool:
        """Check if episode is done"""
        alive_count = sum(1 for f in self.fighters.values() if f.alive)
        return alive_count <= 1 or self.step_count >= self.max_steps

    def get_episode_stats(self) -> Dict[str, Any]:
        """Get stats for the completed episode"""
        winners = self.get_winners()
        return {
            "step_count": self.step_count,
            "winners": winners,
            "winner_count": len(winners),
            "total_fighters": len(self.fighters),
            "fighter_stats": {
                f_id: {
                    "health": f.health,
                    "cumulative_reward": f.cumulative_reward,
                    "alive": f.alive,
                }
                for f_id, f in self.fighters.items()
            },
        }

    def reset(self):
        """Reset arena for new episode"""
        self.fighters.clear()
        self.step_count = 0
