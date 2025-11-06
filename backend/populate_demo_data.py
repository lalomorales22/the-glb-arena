"""
Populate database with demo data for testing the analytics dashboard
"""
import random
from datetime import datetime, timedelta
from database import get_db_manager, Fighter, Episode, ModelCheckpoint, FightFrame
from sqlalchemy.orm import Session

def populate_demo_data():
    """Create sample fighters, episodes, and training data"""
    db_manager = get_db_manager()

    with db_manager.session_scope() as db:
        # Create additional fighters
        fighter_names = [
            "bacon.glb",
            "gum-guy.glb",
            "scaryblue.glb",
            "spongebob.glb",
            "gum-tape-guy.glb"
        ]

        fighters = []
        existing_fighters = db.query(Fighter).all()
        existing_names = {f.glb_filename for f in existing_fighters}

        # Add missing fighters
        for name in fighter_names:
            if name not in existing_names:
                fighter = Fighter(
                    glb_filename=name,
                    metadata_json={"type": "sample", "created_by": "demo"}
                )
                db.add(fighter)
                db.flush()
                fighters.append(fighter)

        # Also include existing fighters
        fighters.extend(existing_fighters)

        if not fighters:
            print("No fighters to work with")
            return

        print(f"Working with {len(fighters)} fighters")

        # Create episodes for each fighter
        for fighter in fighters:
            num_episodes = random.randint(15, 50)
            print(f"Creating {num_episodes} episodes for {fighter.glb_filename}")

            for ep_num in range(num_episodes):
                # Random victory rate (70-90% for trained agents)
                is_victory = random.random() < 0.75

                # Random reward progression (should increase over time)
                progression_factor = ep_num / num_episodes
                base_reward = 50 + (progression_factor * 100)
                total_reward = base_reward + random.uniform(-30, 30)

                # Random episode duration
                duration = random.randint(300, 1200)
                rank = random.randint(1, 4) if is_victory else random.randint(2, 4)

                episode = Episode(
                    fighter_id=fighter.id,
                    episode_number=ep_num + 1,
                    opponent_ids=[str(f.id) for f in random.sample(fighters, k=random.randint(1, 3)) if f.id != fighter.id],
                    total_reward=total_reward,
                    duration_frames=duration,
                    is_victory=is_victory,
                    rank=rank,
                    started_at=datetime.utcnow() - timedelta(hours=random.randint(0, 100))
                )
                db.add(episode)
                db.flush()

                # Create frame data for this episode
                for frame_num in range(0, duration, 30):  # Every 30 frames
                    frame = FightFrame(
                        episode_id=episode.id,
                        frame_number=frame_num,
                        fighter_position=f"[{random.uniform(-50, 50)}, {random.uniform(-50, 50)}]",
                        fighter_health=100 - (frame_num / duration * 50),
                        fighter_velocity=f"[{random.uniform(-5, 5)}, {random.uniform(-5, 5)}]",
                        enemies_state="[[10, 20, 50], [-30, 15, 75]]",
                        action_vector=random.randint(0, 9),
                        reward_delta=random.uniform(-1, 10),
                        cumulative_reward=frame_num * random.uniform(0.1, 1),
                        observation_vector="[" + ", ".join([str(random.uniform(-1, 1)) for _ in range(30)]) + "]"
                    )
                    db.add(frame)

        # Create model checkpoints
        for fighter in fighters:
            # Create 3-5 checkpoints per fighter
            num_checkpoints = random.randint(3, 5)

            for version in range(1, num_checkpoints + 1):
                checkpoint = ModelCheckpoint(
                    fighter_id=fighter.id,
                    model_version=version,
                    training_iteration=version * 100,
                    weights_path=f"/data/models/{fighter.glb_filename}_{version}.pth",
                    win_rate=0.5 + (version * 0.15),  # Improving win rate
                    avg_reward=50 + (version * 30),  # Improving reward
                    total_episodes_trained=version * 50
                )
                db.add(checkpoint)

        db.commit()
        print("✅ Demo data populated successfully!")

if __name__ == "__main__":
    populate_demo_data()
