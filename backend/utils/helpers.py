"""Utility helper functions"""
import numpy as np
from typing import List, Tuple


def calculate_distance(pos1: np.ndarray, pos2: np.ndarray) -> float:
    """Calculate Euclidean distance between two positions"""
    return np.linalg.norm(pos2 - pos1)


def normalize_angle(angle: float) -> float:
    """Normalize angle to [0, 2π)"""
    while angle < 0:
        angle += 2 * np.pi
    while angle >= 2 * np.pi:
        angle -= 2 * np.pi
    return angle


def clamp(value: float, min_val: float, max_val: float) -> float:
    """Clamp value between min and max"""
    return max(min_val, min(max_val, value))


def ring_collision(position: np.ndarray, ring_size: float) -> bool:
    """
    Check if position is outside the ring.

    Args:
        position: [x, z] coordinates
        ring_size: size of the ring

    Returns:
        True if outside ring (should be eliminated)
    """
    half_size = ring_size / 2
    return abs(position[0]) > half_size or abs(position[1]) > half_size


def get_closest_enemies(
    fighter_pos: np.ndarray,
    enemies: List[dict],
    max_enemies: int = 5,
) -> List[dict]:
    """Get the closest enemies to the fighter"""
    if not enemies:
        return []

    # Sort by distance
    distances = [
        (i, np.linalg.norm(e["position"] - fighter_pos))
        for i, e in enumerate(enemies)
    ]
    distances.sort(key=lambda x: x[1])

    return [enemies[i] for i, _ in distances[:max_enemies]]
