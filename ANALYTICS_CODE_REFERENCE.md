# Analytics Dashboard - Code Reference Guide

## File Locations

```
/Users/megabrain/Desktop/the-glb-arena/
├── analytics.php              (Main dashboard - 1,004 lines, 37 KB)
├── backend-integration.js      (API client - 467 lines, 16 KB)
├── AI_INTEGRATION.js           (AI integration - 152 lines, 5 KB)
├── fighter-trainer.php         (Training UI)
├── main-menu.php               (Hub page)
└── backend/
    ├── api/
    │   ├── routes.py           (API endpoints)
    │   └── schemas.py          (Data models)
    └── main.py                 (FastAPI app)
```

---

## Key Code Snippets

### 1. API Configuration (analytics.php, line 440)
```javascript
const API_URL = 'http://localhost:8001/api';
```

### 2. Auto-Refresh Toggle (analytics.php, lines 465-486)
```javascript
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
```

### 3. Data Fetching (analytics.php, lines 519-542)
```javascript
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
```

### 4. Reward Chart (analytics.php, lines 864-902)
```javascript
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
```

### 5. Win Rate Chart (Doughnut) (analytics.php, lines 904-932)
```javascript
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
```

### 6. Learning Curve (Line Chart) (analytics.php, lines 934-981)
```javascript
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
```

### 7. Chart Cleanup (analytics.php, lines 847-862)
```javascript
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
```

### 8. Overview Tab Rendering (analytics.php, lines 544-602)
```javascript
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
                <!-- more stats -->
            </div>
            
            <!-- CHAMPION CARD -->
            ${renderChampionCard(champion)}
        </div>
    `;
}
```

### 9. Fighter Card Rendering (analytics.php, lines 665-709)
```javascript
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
```

### 10. Timestamp Update (analytics.php, lines 842-844)
```javascript
function updateTimestamp() {
    document.getElementById('last-update').textContent = new Date().toLocaleString();
}
```

---

## CSS Classes & Styling

### Main Container
```css
.container {
    position: relative;
    z-index: 1;
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}
```

### Card Styling
```css
.card {
    background: linear-gradient(135deg, rgba(25, 25, 25, 0.9) 0%, rgba(35, 35, 35, 0.9) 100%);
    border: 2px solid;
    border-radius: 10px;
    padding: 25px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s;
}

.card.champion {
    border-color: #ffaa00;
    box-shadow: 0 0 15px rgba(255, 170, 0, 0.5), 0 0 30px rgba(204, 34, 0, 0.3);
    background: linear-gradient(135deg, rgba(50, 25, 0, 0.95) 0%, rgba(60, 30, 0, 0.95) 100%);
}
```

### Navigation Buttons
```css
.nav-btn.active {
    background: linear-gradient(45deg, #cc2200, #ff5500);
    border-color: #ffaa00;
    color: #fff;
    box-shadow: 0 0 15px rgba(255, 85, 0, 0.6), 0 0 25px rgba(204, 34, 0, 0.4);
    transform: scale(1.05);
}
```

### Progress Bars
```css
.progress-bar {
    width: 100%;
    height: 25px;
    background: rgba(0, 0, 0, 0.6);
    border: 1px solid #555;
    border-radius: 5px;
    overflow: hidden;
    margin: 10px 0;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #ff5500, #ffaa00);
    width: 0%;
    transition: width 0.5s ease;
}
```

---

## API Response Examples

### GET /api/fighters
```json
[
    {
        "id": 1,
        "glb_filename": "fighter1.glb",
        "metadata_json": {}
    }
]
```

### GET /api/fighters/{id}/stats
```json
{
    "fighter_id": 1,
    "glb_filename": "fighter1.glb",
    "total_episodes": 150,
    "win_rate": 0.75,
    "avg_reward": 42.5,
    "avg_episode_length": 180
}
```

### GET /api/fighters/{id}/episodes
```json
[
    {
        "id": 1,
        "fighter_id": 1,
        "episode_number": 1,
        "opponent_ids": [2, 3, 4],
        "total_reward": 45.3,
        "duration_frames": 200,
        "is_victory": true,
        "rank": 1
    }
]
```

### GET /api/fighters/{id}/checkpoints
```json
[
    {
        "id": 1,
        "fighter_id": 1,
        "model_version": 1,
        "win_rate": 0.75,
        "avg_reward": 42.5,
        "weights_path": "/data/models/fighter1_v1.pth"
    }
]
```

---

## Common Tasks

### Accessing Dashboard
```
http://localhost:8000/analytics.php
```

### Changing Refresh Interval
Edit line 478 in analytics.php:
```javascript
}, 10000);  // Change 10000 to desired milliseconds
```

### Adding New Chart
1. Add canvas element in HTML rendering function
2. Create init function (e.g., `initMyChart()`)
3. Call function from `initCharts()` when appropriate tab selected
4. Implement Chart.js configuration

### Changing Color Theme
All colors defined at top of CSS section (lines 7-409)
Primary colors:
- `#ff5500` - Orange
- `#ffaa00` - Gold
- `#00ffff` - Cyan
- `#00ff88` - Lime green

### Testing Dashboard Locally
```bash
# 1. Start backend
cd backend
python main.py

# 2. Open in browser
http://localhost:8000/analytics.php

# 3. Create some fighter data via API or game
# 4. Watch dashboard populate with real-time data
```

---

## Performance Tips

1. **Pagination:** API calls use `skip` and `limit` parameters
   ```javascript
   GET /api/fighters/{id}/episodes?skip=0&limit=50
   ```

2. **Visibility Detection:** Only refresh when tab is visible
   ```javascript
   if (document.visibilityState === 'visible') {
       loadContent();
   }
   ```

3. **Chart Cleanup:** Destroy charts when switching tabs
   ```javascript
   if (chart && typeof chart.destroy === 'function') {
       chart.destroy();
   }
   ```

4. **Error Handling:** Gracefully handle API failures
   ```javascript
   try {
       // API call
   } catch (error) {
       contentArea.innerHTML = `<div class="error-message">❌ ERROR: ${error.message}</div>`;
   }
   ```

