<?php
/**
 * Main Menu Hub
 * Central entry point for the GLB Wrestling Arena application
 * Users can access: Game Arena, Analytics Dashboard, or Fighter Trainer
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Wrestling Arena - Main Menu</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0a0a0a 100%);
            color: #aaa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            width: 100%;
        }

        header {
            text-align: center;
            margin-bottom: 60px;
        }

        .logo {
            font-size: 4em;
            margin-bottom: 20px;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        h1 {
            font-size: 3.5em;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 3px;
            background: linear-gradient(45deg, #ff5500, #ffaa00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .tagline {
            font-size: 1.3em;
            color: #ddd;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #888;
            font-size: 1em;
            margin-bottom: 40px;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .menu-card {
            background: rgba(20, 20, 20, 0.8);
            border: 2px solid #333;
            padding: 40px;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 350px;
            position: relative;
            overflow: hidden;
        }

        .menu-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 85, 0, 0.2), transparent);
            transition: left 0.5s ease;
            z-index: 0;
        }

        .menu-card:hover::before {
            left: 100%;
        }

        .menu-card:hover {
            border-color: #ff5500;
            box-shadow: 0 0 40px rgba(255, 85, 0, 0.3);
            transform: translateY(-5px);
        }

        .card-content {
            position: relative;
            z-index: 1;
        }

        .card-icon {
            font-size: 5em;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }

        .card-title {
            font-size: 2em;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #ffaa00;
        }

        .card-description {
            color: #aaa;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 0.95em;
        }

        .card-action {
            margin-top: auto;
            padding-top: 20px;
            color: #ff5500;
            font-weight: bold;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        /* Card specific colors */
        .card-arena {
            border-top: 3px solid #ff5500;
        }

        .card-analytics {
            border-top: 3px solid #ffaa00;
        }

        .card-trainer {
            border-top: 3px solid #ff6600;
        }

        .card-arena:hover {
            box-shadow: 0 0 40px rgba(255, 85, 0, 0.5);
        }

        .card-analytics:hover {
            box-shadow: 0 0 40px rgba(255, 170, 0, 0.5);
        }

        .card-trainer:hover {
            box-shadow: 0 0 40px rgba(255, 102, 0, 0.5);
        }

        footer {
            text-align: center;
            color: #666;
            padding: 20px;
            font-size: 0.9em;
            border-top: 1px solid #333;
            margin-top: 40px;
        }

        .version-info {
            color: #888;
            font-size: 0.85em;
            margin-top: 10px;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            h1 {
                font-size: 2.5em;
            }

            .tagline {
                font-size: 1.1em;
            }

            .menu-card {
                min-height: 280px;
                padding: 30px;
            }

            .card-icon {
                font-size: 3.5em;
            }

            .card-title {
                font-size: 1.5em;
            }

            .logo {
                font-size: 3em;
            }
        }

        /* Loading state */
        .menu-card.loading {
            opacity: 0.5;
            pointer-events: none;
        }

        /* Stats display */
        .stats-container {
            background: rgba(255, 85, 0, 0.1);
            border: 1px solid #444;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 40px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            text-align: center;
        }

        .stat-item {
            padding: 15px;
        }

        .stat-number {
            font-size: 2em;
            color: #ff5500;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #888;
            font-size: 0.85em;
            text-transform: uppercase;
        }

        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #66ff00;
            margin-right: 8px;
            animation: pulse 1s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">🥊</div>
            <h1>3D Wrestling Arena</h1>
            <p class="tagline">AI-Powered Combat Simulation</p>
            <p class="subtitle">Create personalities. Train fighters. Dominate the arena.</p>
        </header>

        <!-- Stats Display -->
        <div class="stats-container" id="statsContainer">
            <div class="stat-item">
                <div class="stat-number" id="fighterCount">0</div>
                <div class="stat-label">Active Fighters</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="profileCount">0</div>
                <div class="stat-label">Personalities</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="battleCount">0</div>
                <div class="stat-label">Total Battles</div>
            </div>
            <div class="stat-item">
                <div style="font-size: 1.2em; margin-bottom: 8px;">
                    <span class="status-indicator"></span>System Status
                </div>
                <div class="stat-label" id="systemStatus">Ready</div>
            </div>
        </div>

        <!-- Main Menu Cards -->
        <div class="menu-grid">
            <!-- Enter Arena -->
            <a href="index.php" class="menu-card card-arena">
                <div class="card-content">
                    <div class="card-icon">⚔️</div>
                    <div class="card-title">ENTER ARENA</div>
                    <div class="card-description">
                        Step into the ring and battle your trained fighters against opponents. Test your personality profiles in real combat.
                    </div>
                    <div class="card-action">→ FIGHT NOW</div>
                </div>
            </a>

            <!-- Analytics Dashboard -->
            <a href="analytics.php" class="menu-card card-analytics">
                <div class="card-content">
                    <div class="card-icon">📊</div>
                    <div class="card-title">ANALYTICS</div>
                    <div class="card-description">
                        Monitor fighter performance, track training progress, and analyze personality traits. Real-time metrics and detailed statistics.
                    </div>
                    <div class="card-action">→ VIEW STATS</div>
                </div>
            </a>

            <!-- Fighter Trainer -->
            <a href="fighter-trainer.php" class="menu-card card-trainer">
                <div class="card-content">
                    <div class="card-icon">🎓</div>
                    <div class="card-title">TRAIN FIGHTER</div>
                    <div class="card-description">
                        Create custom personality profiles with unique parameters. Configure aggression, positioning, targeting, risk tolerance, and endurance.
                    </div>
                    <div class="card-action">→ CREATE PROFILE</div>
                </div>
            </a>
        </div>

        <footer>
            <p>🎪 3D Wrestling Arena | Phase 4: Fighter Personality System</p>
            <div class="version-info">
                Built with: Three.js | FastAPI | PyTorch | SQLite
            </div>
        </footer>
    </div>

    <script>
        const API_BASE = 'http://localhost:8001/api';

        // Load statistics on page load
        async function loadStats() {
            try {
                // Get all fighters
                const fightersResponse = await fetch(`${API_BASE}/fighters`);
                const fighters = await fightersResponse.json();

                // Get profiles for each fighter
                let totalProfiles = 0;
                let totalBattles = 0;

                for (const fighter of fighters) {
                    try {
                        const profilesResponse = await fetch(`${API_BASE}/fighter-profiles/${fighter.id}`);
                        const profiles = await profilesResponse.json() || [];
                        totalProfiles += profiles.length;

                        const episodesResponse = await fetch(`${API_BASE}/fighters/${fighter.id}/episodes`);
                        const episodes = await episodesResponse.json() || [];
                        totalBattles += episodes.length;
                    } catch (error) {
                        console.error(`Error loading data for fighter ${fighter.id}:`, error);
                    }
                }

                // Update display
                document.getElementById('fighterCount').textContent = fighters.length;
                document.getElementById('profileCount').textContent = totalProfiles;
                document.getElementById('battleCount').textContent = totalBattles;
                document.getElementById('systemStatus').textContent = 'Ready';

            } catch (error) {
                console.error('Error loading stats:', error);
                document.getElementById('systemStatus').textContent = 'Offline';
                document.getElementById('systemStatus').style.color = '#ff6600';
            }
        }

        // Load stats when page loads
        window.addEventListener('load', loadStats);

        // Refresh stats every 10 seconds
        setInterval(loadStats, 10000);
    </script>
</body>
</html>
