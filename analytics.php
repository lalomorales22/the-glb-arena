<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏆 WRESTLING CHAMPIONSHIP ANALYTICS 🏆</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial Black', sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0a0a0a 100%);
            color: #e8e8e8;
            overflow-x: hidden;
            position: relative;
            min-height: 100vh;
        }

        /* Animated background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 50%, rgba(255, 80, 0, 0.05) 0%, transparent 50%),
                        radial-gradient(circle at 80% 50%, rgba(220, 20, 20, 0.05) 0%, transparent 50%);
            animation: pulse-bg 8s ease-in-out infinite;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes pulse-bg {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        .container {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* ==================== HEADER STYLING ==================== */
        .header {
            text-align: center;
            margin-bottom: 40px;
            text-shadow: 0 0 15px rgba(255, 100, 0, 0.5);
            animation: glow-pulse 2s ease-in-out infinite;
        }

        .header h1 {
            font-size: 4rem;
            font-weight: 900;
            letter-spacing: 3px;
            background: linear-gradient(45deg, #ff5500, #ffaa00, #ff5500);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .header .tagline {
            font-size: 1.5rem;
            color: #ffaa00;
            text-shadow: 0 0 8px rgba(255, 170, 0, 0.6);
            letter-spacing: 2px;
            font-weight: bold;
        }

        @keyframes glow-pulse {
            0%, 100% { text-shadow: 0 0 12px rgba(255, 100, 0, 0.4); }
            50% { text-shadow: 0 0 20px rgba(255, 170, 0, 0.6); }
        }

        /* ==================== NAVIGATION ==================== */
        .nav-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .nav-btn {
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: bold;
            border: 2px solid;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
            overflow: hidden;
            border-radius: 5px;
            background: rgba(30, 30, 30, 0.8);
        }

        .nav-btn.active {
            background: linear-gradient(45deg, #cc2200, #ff5500);
            border-color: #ffaa00;
            color: #fff;
            box-shadow: 0 0 15px rgba(255, 85, 0, 0.6), 0 0 25px rgba(204, 34, 0, 0.4);
            transform: scale(1.05);
        }

        .nav-btn:not(.active) {
            background: rgba(30, 30, 30, 0.8);
            border-color: #666;
            color: #aaa;
            box-shadow: 0 0 5px rgba(100, 100, 100, 0.3);
        }

        .nav-btn:not(.active):hover {
            background: rgba(50, 50, 50, 0.9);
            border-color: #ff5500;
            color: #ffaa00;
            box-shadow: 0 0 12px rgba(255, 85, 0, 0.4);
        }

        /* ==================== GRID LAYOUT ==================== */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        /* ==================== CARD STYLING ==================== */
        .card {
            background: linear-gradient(135deg, rgba(25, 25, 25, 0.9) 0%, rgba(35, 35, 35, 0.9) 100%);
            border: 2px solid;
            border-radius: 10px;
            padding: 25px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }

        .card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 85, 0, 0.08), transparent);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .card > * {
            position: relative;
            z-index: 2;
        }

        /* Champion Card - Special styling */
        .card.champion {
            border-color: #ffaa00;
            box-shadow: 0 0 15px rgba(255, 170, 0, 0.5), 0 0 30px rgba(204, 34, 0, 0.3), inset 0 0 15px rgba(255, 85, 0, 0.1);
            background: linear-gradient(135deg, rgba(50, 25, 0, 0.95) 0%, rgba(60, 30, 0, 0.95) 100%);
        }

        /* Standard Card - Orange/red accent */
        .card.standard {
            border-color: #666;
            box-shadow: 0 0 10px rgba(100, 100, 100, 0.4);
        }

        .card.standard:hover {
            border-color: #ff5500;
            box-shadow: 0 0 15px rgba(255, 85, 0, 0.5), 0 0 25px rgba(204, 34, 0, 0.2);
            transform: translateY(-5px);
        }

        /* Warning Card - Red accent */
        .card.warning {
            border-color: #666;
            box-shadow: 0 0 10px rgba(100, 100, 100, 0.4);
        }

        .card.warning:hover {
            border-color: #cc2200;
            box-shadow: 0 0 15px rgba(204, 34, 0, 0.5);
            transform: translateY(-5px);
        }

        /* Championship belt effect */
        .championship-belt {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 3rem;
            filter: drop-shadow(0 0 10px #ffff00);
            animation: belt-bounce 1s ease-in-out infinite;
        }

        @keyframes belt-bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .card h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .card.champion h2 {
            color: #ffaa00;
            text-shadow: 0 0 8px rgba(255, 170, 0, 0.6);
        }

        .card.standard h2 {
            color: #ffaa00;
            text-shadow: 0 0 6px rgba(255, 170, 0, 0.4);
        }

        .card.warning h2 {
            color: #ff5500;
            text-shadow: 0 0 6px rgba(255, 85, 0, 0.4);
        }

        /* ==================== STAT LINES ==================== */
        .stat-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 12px 0;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 85, 0, 0.2);
            font-size: 1.1rem;
            font-weight: bold;
        }

        .stat-line:last-child {
            border-bottom: none;
        }

        .stat-label {
            color: #aaa;
            text-shadow: none;
        }

        .stat-value {
            color: #ffaa00;
            text-shadow: 0 0 4px rgba(255, 170, 0, 0.3);
            font-size: 1.3rem;
        }

        .card.champion .stat-label {
            color: #ddd;
        }

        .card.champion .stat-value {
            color: #ffaa00;
        }

        /* ==================== PROGRESS BARS ==================== */
        .progress-bar {
            width: 100%;
            height: 25px;
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid #555;
            border-radius: 5px;
            overflow: hidden;
            margin: 10px 0;
            box-shadow: inset 0 0 8px rgba(0, 0, 0, 0.5);
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #ff5500, #ffaa00);
            width: 0%;
            transition: width 0.5s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000;
            font-weight: bold;
            font-size: 0.9rem;
            text-shadow: 0 0 1px #fff;
        }

        .progress-fill.high {
            background: linear-gradient(90deg, #ffaa00, #ff7700);
        }

        .progress-fill.medium {
            background: linear-gradient(90deg, #ff7700, #ff5500);
        }

        .progress-fill.low {
            background: linear-gradient(90deg, #ff5500, #cc2200);
        }

        /* ==================== CHART CONTAINER ==================== */
        .chart-container {
            width: 100%;
            height: 300px;
            position: relative;
            margin: 20px 0;
        }

        canvas {
            display: block;
        }

        /* ==================== FOOTER ==================== */
        .footer {
            text-align: center;
            margin-top: 50px;
            padding: 20px;
            border-top: 1px solid #555;
            font-size: 0.9rem;
            color: #aaa;
            text-shadow: none;
        }

        /* ==================== LOADING ANIMATION ==================== */
        .loading {
            display: inline-block;
            animation: loading-spin 1s linear infinite;
        }

        @keyframes loading-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* ==================== ERROR STATE ==================== */
        .error-message {
            background: rgba(255, 0, 0, 0.2);
            border: 2px solid #ff0000;
            color: #ff6666;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-shadow: 0 0 5px #ff0000;
        }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.5rem;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .stat-line {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        /* ==================== STAT RANKING BADGES ==================== */
        .badge {
            display: inline-block;
            padding: 5px 12px;
            background: linear-gradient(45deg, #ff5500, #ffaa00);
            color: #000;
            font-weight: bold;
            border-radius: 20px;
            font-size: 0.85rem;
            text-shadow: none;
            margin-left: 10px;
            box-shadow: 0 0 8px rgba(255, 85, 0, 0.5);
        }

        .badge.gold {
            background: linear-gradient(45deg, #ffaa00, #ff8800);
            box-shadow: 0 0 8px rgba(255, 170, 0, 0.5);
        }

        .badge.silver {
            background: linear-gradient(45deg, #aaaaaa, #777777);
            color: #000;
            box-shadow: 0 0 6px rgba(170, 170, 170, 0.4);
        }

        .badge.bronze {
            background: linear-gradient(45deg, #cc7744, #994422);
            box-shadow: 0 0 6px rgba(204, 119, 68, 0.4);
        }

        /* ==================== TIMESTAMP ==================== */
        .timestamp {
            font-size: 0.85rem;
            color: #888;
            margin-top: 10px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <h1>⚡ WRESTLING CHAMPIONSHIP ANALYTICS ⚡</h1>
            <p class="tagline">🏆 REAL-TIME FIGHTER STATISTICS & TRAINING INSIGHTS 🏆</p>
        </div>

        <!-- NAVIGATION -->
        <div class="nav-buttons">
            <button class="nav-btn active" onclick="showTab('overview')">📊 OVERVIEW</button>
            <button class="nav-btn" onclick="showTab('fighters')">🥊 FIGHTERS</button>
            <button class="nav-btn" onclick="showTab('training')">🤖 TRAINING</button>
            <button class="nav-btn" onclick="showTab('stats')">📈 STATS</button>
            <button class="nav-btn" onclick="toggleAutoRefresh()" id="auto-refresh-btn">🔄 AUTO OFF</button>
            <button class="nav-btn" onclick="goBackToArena()" style="background: linear-gradient(45deg, #cc2200, #660000); border-color: #cc2200; color: #fff;">🎪 BACK TO ARENA</button>
        </div>

        <!-- CONTENT AREA -->
        <div id="content-area"></div>

        <!-- FOOTER -->
        <div class="footer">
            <p>Last Updated: <span id="last-update">Loading...</span> | Version 3.0 - Championship Series</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        const API_URL = 'http://localhost:8001/api';
        let currentTab = 'overview';
        let fightersData = [];
        let episodesData = [];
        let autoRefreshEnabled = false;
        let autoRefreshInterval = null;
        let charts = {};  // Store chart references for cleanup

        // ==================== TAB NAVIGATION ====================
        function showTab(tab) {
            currentTab = tab;
            document.querySelectorAll('.nav-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.textContent.includes(tab === 'overview' ? 'OVERVIEW' : tab === 'fighters' ? 'FIGHTERS' : tab === 'training' ? 'TRAINING' : 'STATS')) {
                    btn.classList.add('active');
                }
            });
            loadContent();
        }

        // ==================== BACK TO ARENA ====================
        function goBackToArena() {
            window.location.href = '/index.php';
        }

        // ==================== AUTO-REFRESH TOGGLE ====================
        function toggleAutoRefresh() {
            autoRefreshEnabled = !autoRefreshEnabled;
            const btn = document.getElementById('auto-refresh-btn');

            if (autoRefreshEnabled) {
                btn.classList.add('active');
                btn.textContent = '🔄 AUTO ON';
                // Refresh every 10 seconds
                autoRefreshInterval = setInterval(() => {
                    if (document.visibilityState === 'visible') {
                        loadContent();
                    }
                }, 10000);
            } else {
                btn.classList.remove('active');
                btn.textContent = '🔄 AUTO OFF';
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                }
            }
        }

        // ==================== MAIN CONTENT LOADER ====================
        async function loadContent() {
            const contentArea = document.getElementById('content-area');
            contentArea.innerHTML = '<div style="text-align: center; padding: 40px;"><div class="loading">⚡</div> LOADING CHAMPIONSHIP DATA...</div>';

            try {
                await fetchData();

                switch(currentTab) {
                    case 'overview':
                        contentArea.innerHTML = renderOverview();
                        break;
                    case 'fighters':
                        contentArea.innerHTML = renderFighters();
                        break;
                    case 'training':
                        contentArea.innerHTML = renderTraining();
                        break;
                    case 'stats':
                        contentArea.innerHTML = renderStats();
                        break;
                }

                updateTimestamp();
                initCharts();
            } catch (error) {
                contentArea.innerHTML = `<div class="error-message">❌ ERROR: ${error.message}</div>`;
                console.error('Error loading content:', error);
            }
        }

        // ==================== FETCH DATA ====================
        async function fetchData() {
            try {
                // Fetch fighters
                const fightersRes = await fetch(`${API_URL}/fighters`);
                fightersData = await fightersRes.json();

                // Fetch stats for each fighter
                for (let fighter of fightersData) {
                    const statsRes = await fetch(`${API_URL}/fighters/${fighter.id}/stats`);
                    fighter.stats = await statsRes.json();

                    // Fetch episodes
                    const episodesRes = await fetch(`${API_URL}/fighters/${fighter.id}/episodes`);
                    fighter.episodes = await episodesRes.json();

                    // Fetch checkpoints
                    const checkpointsRes = await fetch(`${API_URL}/fighters/${fighter.id}/checkpoints`);
                    fighter.checkpoints = await checkpointsRes.json();
                }
            } catch (error) {
                throw new Error(`Failed to fetch data: ${error.message}`);
            }
        }

        // ==================== OVERVIEW TAB ====================
        function renderOverview() {
            const totalFighters = fightersData.length;
            const totalEpisodes = fightersData.reduce((sum, f) => sum + (f.stats?.total_episodes || 0), 0);
            const avgWinRate = fightersData.length > 0
                ? (fightersData.reduce((sum, f) => sum + (f.stats?.win_rate || 0), 0) / fightersData.length * 100).toFixed(2)
                : 0;

            const champion = fightersData.reduce((prev, current) =>
                ((prev.stats?.win_rate || 0) > (current.stats?.win_rate || 0)) ? prev : current
            , {});

            return `
                <div class="dashboard-grid">
                    <!-- SYSTEM STATS -->
                    <div class="card standard">
                        <h2>🎪 SYSTEM STATUS</h2>
                        <div class="stat-line">
                            <span class="stat-label">Active Fighters</span>
                            <span class="stat-value">${totalFighters}</span>
                        </div>
                        <div class="stat-line">
                            <span class="stat-label">Total Bouts</span>
                            <span class="stat-value">${totalEpisodes}</span>
                        </div>
                        <div class="stat-line">
                            <span class="stat-label">Circuit Avg Win Rate</span>
                            <span class="stat-value">${avgWinRate}%</span>
                        </div>
                    </div>

                    <!-- CHAMPIONSHIP BELT -->
                    ${renderChampionCard(champion)}
                </div>

                <!-- ARENA OVERVIEW -->
                <div class="card standard" style="margin-bottom: 25px;">
                    <h2>📊 CHAMPIONSHIP RANKINGS</h2>
                    <div style="margin-top: 20px;">
                        ${renderRankings()}
                    </div>
                </div>

                <!-- TRAINING OVERVIEW -->
                <div class="dashboard-grid">
                    <div class="card standard">
                        <h2>📈 REWARD PROGRESSION</h2>
                        <div class="chart-container">
                            <canvas id="reward-chart"></canvas>
                        </div>
                    </div>
                    <div class="card standard">
                        <h2>🥊 WIN RATE BREAKDOWN</h2>
                        <div class="chart-container">
                            <canvas id="winrate-chart"></canvas>
                        </div>
                    </div>
                </div>
            `;
        }

        // ==================== CHAMPION CARD ====================
        function renderChampionCard(champion) {
            if (!champion.stats) {
                return '';
            }

            return `
                <div class="card champion">
                    <div class="championship-belt">🏆</div>
                    <h2>UNDISPUTED CHAMPION</h2>
                    <div class="stat-line">
                        <span class="stat-label">Fighter Name</span>
                        <span class="stat-value">${champion.glb_filename.replace('.glb', '').toUpperCase()}</span>
                    </div>
                    <div class="stat-line">
                        <span class="stat-label">Championship Record</span>
                        <span class="stat-value">${champion.stats.total_episodes} BOUTS</span>
                    </div>
                    <div class="stat-line">
                        <span class="stat-label">Victory Rate</span>
                        <span class="stat-value">${(champion.stats.win_rate * 100).toFixed(1)}%</span>
                    </div>
                    <div class="stat-line">
                        <span class="stat-label">Peak Reward</span>
                        <span class="stat-value">${champion.stats.avg_reward.toFixed(2)}</span>
                    </div>
                </div>
            `;
        }

        // ==================== RANKINGS ====================
        function renderRankings() {
            const sorted = [...fightersData].sort((a, b) =>
                (b.stats?.win_rate || 0) - (a.stats?.win_rate || 0)
            );

            return sorted.slice(0, 5).map((fighter, idx) => {
                const badges = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'];
                return `
                    <div style="padding: 10px; margin: 10px 0; border-bottom: 1px solid #00ffff; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 1.2rem; font-weight: bold;">${badges[idx]} ${fighter.glb_filename.replace('.glb', '')}</span>
                        <span style="color: #ffff00; font-weight: bold;">${(fighter.stats?.win_rate * 100 || 0).toFixed(1)}%</span>
                    </div>
                `;
            }).join('');
        }

        // ==================== FIGHTERS TAB ====================
        function renderFighters() {
            if (fightersData.length === 0) {
                return '<div class="error-message">No fighters registered in the arena yet!</div>';
            }

            return `
                <div class="dashboard-grid">
                    ${fightersData.map(fighter => renderFighterCard(fighter)).join('')}
                </div>
            `;
        }

        function renderFighterCard(fighter) {
            const stats = fighter.stats || {};
            const episodeCount = stats.total_episodes || 0;
            const winRate = stats.win_rate * 100 || 0;
            const avgReward = stats.avg_reward || 0;

            return `
                <div class="card standard">
                    <h2>🥊 ${fighter.glb_filename.replace('.glb', '').toUpperCase()}</h2>

                    <div class="stat-line">
                        <span class="stat-label">Bouts Completed</span>
                        <span class="stat-value">${episodeCount}</span>
                    </div>

                    <div class="stat-line">
                        <span class="stat-label">Victory Record</span>
                        <span class="stat-value">${winRate.toFixed(1)}%</span>
                    </div>

                    <div class="stat-line">
                        <span class="stat-label">Average Reward</span>
                        <span class="stat-value">${avgReward.toFixed(2)}</span>
                    </div>

                    <div class="stat-line">
                        <span class="stat-label">Avg Round Length</span>
                        <span class="stat-value">${stats.avg_episode_length || 0} frames</span>
                    </div>

                    <div style="margin-top: 15px;">
                        <span class="stat-label" style="display: block; margin-bottom: 8px;">Win Rate:</span>
                        <div class="progress-bar">
                            <div class="progress-fill ${getProgressClass(winRate)}" style="width: ${winRate}%">
                                ${winRate > 30 ? Math.round(winRate) + '%' : ''}
                            </div>
                        </div>
                    </div>

                    ${renderCheckpointBadges(fighter.checkpoints || [])}

                    <div class="timestamp">Last Updated: ${new Date().toLocaleTimeString()}</div>
                </div>
            `;
        }

        function renderCheckpointBadges(checkpoints) {
            if (!checkpoints || checkpoints.length === 0) {
                return '<div style="color: #888; margin-top: 10px;">No model checkpoints yet</div>';
            }

            const best = checkpoints[0];
            return `
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #00ffff;">
                    <div style="font-size: 0.9rem; color: #88ccff;">Best Model: v${best.model_version}</div>
                    <div style="font-size: 0.85rem; color: #00ff88;">Win Rate: ${(best.win_rate * 100).toFixed(1)}%</div>
                </div>
            `;
        }

        // ==================== TRAINING TAB ====================
        function renderTraining() {
            return `
                <div class="dashboard-grid">
                    <div class="card standard">
                        <h2>🤖 TRAINING STATUS</h2>
                        <div class="stat-line">
                            <span class="stat-label">Active Training</span>
                            <span class="stat-value">CHECKING...</span>
                        </div>
                        <div class="stat-line">
                            <span class="stat-label">Total Epochs</span>
                            <span class="stat-value">${getTotalEpochs()}</span>
                        </div>
                        <div class="stat-line">
                            <span class="stat-label">Models Trained</span>
                            <span class="stat-value">${getModelsCount()}</span>
                        </div>
                    </div>

                    <div class="card standard">
                        <h2>📊 TRAINING METRICS</h2>
                        ${renderTrainingMetrics()}
                    </div>
                </div>

                <div class="card standard" style="margin-top: 25px;">
                    <h2>📈 LEARNING CURVES (ALL FIGHTERS)</h2>
                    <div class="chart-container">
                        <canvas id="learning-curve"></canvas>
                    </div>
                </div>
            `;
        }

        function renderTrainingMetrics() {
            let html = '';
            fightersData.forEach(fighter => {
                if (fighter.episodes && fighter.episodes.length > 0) {
                    const latest = fighter.episodes[fighter.episodes.length - 1];
                    html += `
                        <div style="margin: 10px 0; padding: 10px 0; border-bottom: 1px solid #00ffff;">
                            <div style="color: #00ffff; font-weight: bold;">${fighter.glb_filename}</div>
                            <div style="font-size: 0.9rem; color: #88ccff;">Reward: ${latest.total_reward.toFixed(2)} | Rank: ${latest.rank || 'N/A'}</div>
                        </div>
                    `;
                }
            });
            return html || '<div style="color: #888;">No training data available</div>';
        }

        // ==================== STATS TAB ====================
        function renderStats() {
            return `
                <div class="card standard">
                    <h2>⚡ DETAILED FIGHTER STATS</h2>
                    ${renderDetailedStats()}
                </div>
            `;
        }

        function renderDetailedStats() {
            if (fightersData.length === 0) {
                return '<div style="color: #888;">No data available</div>';
            }

            return `
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                    <thead>
                        <tr style="border-bottom: 2px solid #00ffff;">
                            <th style="padding: 10px; text-align: left; color: #ffff00;">Fighter</th>
                            <th style="padding: 10px; text-align: center; color: #ffff00;">Bouts</th>
                            <th style="padding: 10px; text-align: center; color: #ffff00;">Wins</th>
                            <th style="padding: 10px; text-align: center; color: #ffff00;">Win %</th>
                            <th style="padding: 10px; text-align: center; color: #ffff00;">Avg Reward</th>
                            <th style="padding: 10px; text-align: center; color: #ffff00;">Models</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${renderStatsRows()}
                    </tbody>
                </table>
            `;
        }

        function renderStatsRows() {
            return fightersData.map(fighter => {
                const stats = fighter.stats || {};
                const wins = Math.round((stats.total_episodes || 0) * (stats.win_rate || 0));
                return `
                    <tr style="border-bottom: 1px solid #00ffff;">
                        <td style="padding: 12px; color: #00ffff;">${fighter.glb_filename}</td>
                        <td style="padding: 12px; text-align: center; color: #00ff88;">${stats.total_episodes || 0}</td>
                        <td style="padding: 12px; text-align: center; color: #ffff00;">${wins}</td>
                        <td style="padding: 12px; text-align: center; color: #00ffff;">${((stats.win_rate || 0) * 100).toFixed(1)}%</td>
                        <td style="padding: 12px; text-align: center; color: #00ff88;">${(stats.avg_reward || 0).toFixed(2)}</td>
                        <td style="padding: 12px; text-align: center; color: #ff00ff;">${(fighter.checkpoints || []).length}</td>
                    </tr>
                `;
            }).join('');
        }

        // ==================== HELPER FUNCTIONS ====================
        function getProgressClass(percentage) {
            if (percentage >= 70) return 'high';
            if (percentage >= 40) return 'medium';
            return 'low';
        }

        function getTotalEpochs() {
            return fightersData.reduce((sum, f) => sum + (f.episodes?.length || 0), 0);
        }

        function getModelsCount() {
            return fightersData.reduce((sum, f) => sum + (f.checkpoints?.length || 0), 0);
        }

        function updateTimestamp() {
            document.getElementById('last-update').textContent = new Date().toLocaleString();
        }

        // ==================== CHARTS INITIALIZATION ====================
        function initCharts() {
            // Cleanup old charts
            Object.values(charts).forEach(chart => {
                if (chart && typeof chart.destroy === 'function') {
                    chart.destroy();
                }
            });
            charts = {};

            if (currentTab === 'overview') {
                initRewardChart();
                initWinrateChart();
            } else if (currentTab === 'training') {
                initLearningCurve();
            }
        }

        function initRewardChart() {
            const ctx = document.getElementById('reward-chart');
            if (!ctx) return;

            const labels = fightersData.map(f => f.glb_filename.replace('.glb', ''));
            const data = fightersData.map(f => f.stats?.avg_reward || 0);

            charts['reward'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Average Reward',
                        data: data,
                        backgroundColor: '#ff5500',
                        borderColor: '#ffaa00',
                        borderWidth: 2,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            ticks: { color: '#aaa' },
                            grid: { color: 'rgba(100, 100, 100, 0.2)' }
                        },
                        x: {
                            ticks: { color: '#aaa' },
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        function initWinrateChart() {
            const ctx = document.getElementById('winrate-chart');
            if (!ctx) return;

            const labels = fightersData.map(f => f.glb_filename.replace('.glb', ''));
            const data = fightersData.map(f => (f.stats?.win_rate || 0) * 100);

            charts['winrate'] = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: ['#ff5500', '#ffaa00', '#ff8800', '#cc2200', '#ff6600'],
                        borderColor: '#1a1a1a',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: '#aaa' }
                        }
                    }
                }
            });
        }

        function initLearningCurve() {
            const ctx = document.getElementById('learning-curve');
            if (!ctx) return;

            const datasets = fightersData.map((fighter, idx) => {
                const colors = ['#ff5500', '#ffaa00', '#ff8800', '#cc2200', '#ff6600'];
                const episodes = fighter.episodes || [];
                const data = episodes.map(e => e.total_reward);
                const labels = episodes.map((_, i) => i + 1);

                return {
                    label: fighter.glb_filename.replace('.glb', ''),
                    data: data,
                    borderColor: colors[idx % colors.length],
                    backgroundColor: colors[idx % colors.length] + '15',
                    tension: 0.3,
                    fill: true,
                    borderWidth: 2
                };
            });

            charts['learning'] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Array.from({length: Math.max(...fightersData.map(f => f.episodes?.length || 0))}, (_, i) => i + 1),
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: '#aaa' }
                        }
                    },
                    scales: {
                        y: {
                            ticks: { color: '#aaa' },
                            grid: { color: 'rgba(100, 100, 100, 0.2)' }
                        },
                        x: {
                            ticks: { color: '#aaa' },
                            grid: { color: 'rgba(100, 100, 100, 0.2)' }
                        }
                    }
                }
            });
        }

        // ==================== INITIALIZATION ====================
        document.addEventListener('DOMContentLoaded', () => {
            loadContent();
            // Enable auto-refresh by default on page load
            toggleAutoRefresh();
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
            Object.values(charts).forEach(chart => {
                if (chart && typeof chart.destroy === 'function') {
                    chart.destroy();
                }
            });
        });
    </script>
</body>
</html>
