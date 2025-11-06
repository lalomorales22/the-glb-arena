/**
 * Backend Integration Module
 * Handles communication between the game frontend and the RL training backend
 *
 * Features:
 * - Episode creation and tracking
 * - Frame-level data collection and submission
 * - Trained model loading and inference
 * - Training status display
 *
 * Configuration:
 */

class BackendIntegration {
    constructor(backendURL = 'http://localhost:8001') {
        this.backendURL = backendURL;
        this.episodeId = null;
        this.isConnected = false;
        this.frameCounter = 0;
        this.FRAME_SAMPLE_RATE = 6; // Send every 6 frames (~10 FPS at 60 FPS game)
        this.lastFrameData = {
            health: 100,
            enemyCount: 0
        };
        this.lastAction = 8; // idle
        this.trainedModel = null;
        this.modelWeights = null;

        // Check backend connectivity on initialization
        this.checkBackendHealth();
    }

    // ==================== BACKEND HEALTH CHECK ====================

    async checkBackendHealth() {
        try {
            const response = await fetch(`${this.backendURL}/health`, {
                timeout: 2000
            });
            if (response.ok) {
                this.isConnected = true;
                console.log('✅ Backend connected at', this.backendURL);
                this.showStatus('🟢 Backend Connected', 'success');
                await this.loadTrainedModels();
            }
        } catch (err) {
            this.isConnected = false;
            console.warn('⚠️ Backend unavailable at', this.backendURL);
            this.showStatus('🔴 Backend Offline (playing local mode)', 'warning');
        }
    }

    // ==================== EPISODE MANAGEMENT ====================

    /**
     * Create a new episode when a game starts
     * @param {number} controlledFighterId - ID of the player-controlled fighter
     * @param {number[]} opponentIds - IDs of AI opponents
     * @param {number} mapSize - Size of the ring
     */
    async startEpisode(controlledFighterId = 1, opponentIds = [2, 3, 4], mapSize = 160) {
        if (!this.isConnected) {
            console.log('Backend offline - game will run in local mode');
            return;
        }

        try {
            const response = await fetch(`${this.backendURL}/api/episodes`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    fighter_id: controlledFighterId,
                    opponents: opponentIds,
                    map_size: mapSize
                })
            });

            if (response.ok) {
                const data = await response.json();
                this.episodeId = data.id;
                this.frameCounter = 0;
                console.log('✅ Episode started:', this.episodeId);
                this.showStatus(`📊 Episode #${this.episodeId} started`, 'info');
                return data;
            } else {
                console.error('Failed to create episode:', response.status);
                this.episodeId = null;
            }
        } catch (err) {
            console.error('Error starting episode:', err);
            this.episodeId = null;
        }
    }

    /**
     * Record a single frame of game data for training
     * @param {Object} frameData - Frame data including position, health, observation, action, reward
     */
    async recordFrame(frameData) {
        if (!this.isConnected || !this.episodeId) {
            return; // Skip if backend unavailable or no episode active
        }

        // Debounce: only send every N frames
        if (this.frameCounter++ % this.FRAME_SAMPLE_RATE !== 0) {
            return;
        }

        try {
            // Non-blocking fetch (fire and forget with error handling)
            fetch(`${this.backendURL}/api/fight-frames`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    episode_id: this.episodeId,
                    fighter_id: frameData.fighter_id || 1,
                    frame_number: this.frameCounter,
                    position: frameData.position,
                    health: frameData.health,
                    velocity: frameData.velocity,
                    observation_vector: frameData.observation_vector,
                    action_taken: frameData.action_taken,
                    reward_delta: frameData.reward_delta,
                    cumulative_reward: frameData.cumulative_reward || 0
                })
            }).catch(() => {
                // Silently fail if backend is down
                // Data is still useful for offline gameplay
            });
        } catch (err) {
            // Catch synchronous errors
            console.error('Error recording frame:', err);
        }
    }

    /**
     * Complete the episode when a game ends
     * @param {Object} episodeResult - Result data (winner_id, duration_frames, etc)
     */
    async completeEpisode(episodeResult) {
        if (!this.isConnected || !this.episodeId) {
            return;
        }

        try {
            const response = await fetch(`${this.backendURL}/api/episodes/${this.episodeId}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    status: 'completed',
                    winner_id: episodeResult.winner_id || 1,
                    duration_frames: this.frameCounter,
                    final_health: episodeResult.final_health || 100,
                    player_won: episodeResult.player_won || false
                })
            });

            if (response.ok) {
                const data = await response.json();
                console.log('✅ Episode saved:', data.id);
                this.showStatus(`📊 Episode #${this.episodeId} completed & saved`, 'success');
                this.episodeId = null;
                return data;
            }
        } catch (err) {
            console.error('Error completing episode:', err);
        }
    }

    // ==================== MODEL LOADING ====================

    /**
     * Load available trained models from backend
     */
    async loadTrainedModels() {
        if (!this.isConnected) return;

        try {
            const response = await fetch(`${this.backendURL}/api/fighters/1/best-model`);
            if (response.ok) {
                const modelData = await response.json();
                this.trainedModel = modelData;
                console.log('✅ Loaded trained model version:', modelData.version);
                this.showStatus(`🤖 Trained Model v${modelData.version} loaded (WR: ${(modelData.win_rate * 100).toFixed(1)}%)`, 'success');

                // Load model weights
                await this.loadModelWeights(modelData.version);
            } else {
                console.log('No trained model available yet');
            }
        } catch (err) {
            console.warn('Could not load trained model:', err);
        }
    }

    /**
     * Load neural network weights - we'll use backend for inference instead
     * @param {number} modelVersion - Version number of the model
     */
    async loadModelWeights(modelVersion) {
        if (!this.isConnected) return;

        try {
            // Instead of loading weights locally, we'll use the backend API for inference
            // This avoids having to deal with binary PyTorch formats in the browser
            console.log('✅ Model ready for inference via backend API');
            this.modelWeights = { ready: true }; // Mark as ready
        } catch (err) {
            console.warn('Could not prepare model:', err);
        }
    }

    // ==================== MODEL INFERENCE ====================

    /**
     * Run neural network inference on observations using backend API
     * This is more efficient than loading and running models in the browser
     *
     * @param {number[]} observation - 30-dimensional observation vector
     * @param {number} fighterId - ID of the fighter (for profile-specific models)
     * @param {string} profileName - Optional personality profile name
     * @returns {Promise<Object>} {action: 0-9, probs: [float]} or null if model unavailable
     */
    async runModelInference(observation, fighterId = 1, profileName = null) {
        if (!this.trainedModel || !this.modelWeights) {
            return null; // Use scripted AI fallback
        }

        try {
            // Use backend API for inference
            const body = {
                observation: observation
            };

            // Add profile name if specified (for personality-based AI)
            if (profileName) {
                body.profile_name = profileName;
            }

            const response = await fetch(`${this.backendURL}/api/fighters/${fighterId}/inference`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                timeout: 100, // Max 100ms for model inference
                body: JSON.stringify(body)
            });

            if (response.ok) {
                const result = await response.json();
                return {
                    action: result.action,
                    probs: result.action_probs,
                    model_info: result.model_info
                };
            } else {
                return null;
            }
        } catch (err) {
            // Inference failed - fall back to scripted AI
            return null;
        }
    }

    /**
     * Matrix-vector multiplication for neural network layer
     * @param {number[][]} matrix - Weight matrix [outputSize × inputSize]
     * @param {number[]} vector - Input vector [inputSize]
     * @returns {number[]} Output vector [outputSize]
     */
    matrixVectorMul(matrix, vector) {
        if (!matrix || !vector) return new Array(vector.length).fill(0);
        return matrix.map(row => {
            if (!Array.isArray(row)) return 0;
            return row.reduce((sum, val, i) => sum + (val * (vector[i] || 0)), 0);
        });
    }

    /**
     * Softmax activation function
     * @param {number[]} logits - Raw network output
     * @returns {number[]} Probability distribution [0, 1]
     */
    softmax(logits) {
        if (!Array.isArray(logits) || logits.length === 0) {
            return new Array(10).fill(0.1);
        }
        const maxLogit = Math.max(...logits);
        const exps = logits.map(x => Math.exp((x || 0) - maxLogit));
        const sum = exps.reduce((a, b) => a + b, 0);
        return exps.map(x => (sum > 0 ? x / sum : 1 / logits.length));
    }

    /**
     * Sample action from probability distribution
     * @param {number[]} probs - Action probabilities
     * @returns {number} Selected action (0-9)
     */
    sampleFromDistribution(probs) {
        let rand = Math.random();
        for (let i = 0; i < probs.length; i++) {
            rand -= (probs[i] || 0);
            if (rand <= 0) return i;
        }
        return probs.length - 1;
    }

    // ==================== OBSERVATION BUILDING ====================

    /**
     * Build 30-dimensional observation vector from game state
     * Format:
     * [0:2]   - Own position (normalized x, z)
     * [2]     - Own health [0-1]
     * [3:5]   - Own velocity (vx, vz)
     * [5:30]  - Enemy observations (up to 5 enemies × 5 values each):
     *           - distance, relative_x, relative_z, health, threat_level
     *
     * @param {Fighter} ownFighter - The fighter to build observation for
     * @param {Fighter[]} allFighters - All fighters in the arena
     * @param {number} ringSize - Size of the ring for normalization
     * @returns {number[]} 30-dimensional observation vector
     */
    buildObservationVector(ownFighter, allFighters, ringSize = 160) {
        const obs = new Array(30).fill(0);

        if (!ownFighter || !ownFighter.position) {
            return obs; // Return zeros if fighter not initialized
        }

        const ringNorm = ringSize / 2;

        // Own state [0:5]
        obs[0] = (ownFighter.position.x || 0) / ringNorm;
        obs[1] = (ownFighter.position.z || 0) / ringNorm;
        obs[2] = (ownFighter.health || 100) / 100; // Normalize to [0, 1]
        obs[3] = (ownFighter.velocity?.x || 0);
        obs[4] = (ownFighter.velocity?.z || 0);

        // Enemy observations [5:30] - up to 5 enemies
        if (Array.isArray(allFighters)) {
            const enemies = allFighters
                .filter(f => f !== ownFighter && !f.eliminated)
                .sort((a, b) => {
                    const dA = ownFighter.position.distanceTo(a.position);
                    const dB = ownFighter.position.distanceTo(b.position);
                    return dA - dB;
                })
                .slice(0, 5);

            enemies.forEach((enemy, i) => {
                const idx = 5 + i * 5;
                const dx = (enemy.position?.x || 0) - (ownFighter.position?.x || 0);
                const dz = (enemy.position?.z || 0) - (ownFighter.position?.z || 0);
                const dist = Math.sqrt(dx * dx + dz * dz);
                const threatLevel = ((100 - (enemy.health || 0)) + dist) / 2;

                obs[idx + 0] = dist / 100; // distance (normalized)
                obs[idx + 1] = dx / 100; // relative x
                obs[idx + 2] = dz / 100; // relative z
                obs[idx + 3] = ((enemy.health || 0) / 100); // enemy health
                obs[idx + 4] = threatLevel / 100; // threat level
            });
        }

        return obs;
    }

    /**
     * Calculate immediate reward for a frame
     * @param {Fighter} fighter - The fighter
     * @param {Object} prevState - Previous frame state
     * @returns {number} Immediate reward
     */
    calculateFrameReward(fighter, prevState = {}) {
        let reward = 0.5; // Small per-frame bonus for survival

        // Reward for eliminating enemies
        const currentEnemyCount = fighter.enemies?.length || 0;
        const prevEnemyCount = prevState.enemyCount || currentEnemyCount;
        if (currentEnemyCount < prevEnemyCount) {
            reward += 10; // Bonus for eliminating an enemy
        }

        // Penalty for taking damage
        const healthLoss = (prevState.health || 100) - (fighter.health || 100);
        if (healthLoss > 0) {
            reward -= healthLoss * 0.1;
        }

        // Penalty for being near ring edge
        if (fighter.position) {
            const distFromCenter = Math.sqrt(
                fighter.position.x ** 2 + fighter.position.z ** 2
            );
            if (distFromCenter > 112) { // Close to edge
                reward -= 1;
            }
        }

        return reward;
    }

    // ==================== UI FEEDBACK ====================

    /**
     * Show status message in UI
     * @param {string} message - Message to display
     * @param {string} type - Message type: 'info', 'success', 'warning', 'error'
     */
    showStatus(message, type = 'info') {
        // Log to console
        const icon = {
            'info': 'ℹ️',
            'success': '✅',
            'warning': '⚠️',
            'error': '❌'
        }[type] || '•';

        console.log(`${icon} ${message}`);

        // Could also update UI element if available
        try {
            const statusEl = document.getElementById('backend-status');
            if (statusEl) {
                statusEl.textContent = message;
                statusEl.style.color = {
                    'info': '#00aeff',
                    'success': '#00ff00',
                    'warning': '#ffaa00',
                    'error': '#ff0000'
                }[type] || '#fff';
            }
        } catch (e) {
            // UI element may not exist
        }
    }

    /**
     * Update training status display
     */
    async updateTrainingStatus() {
        if (!this.isConnected || !this.trainedModel) return;

        try {
            const response = await fetch(`${this.backendURL}/api/fighters/1/stats`);
            if (response.ok) {
                const stats = await response.json();
                this.showStatus(
                    `🤖 Model v${this.trainedModel.version} | WR: ${(stats.win_rate * 100).toFixed(1)}% | Avg Reward: ${stats.avg_reward.toFixed(1)}`,
                    'info'
                );
            }
        } catch (err) {
            // Silently fail
        }
    }
}

// Create global instance
window.backend = new BackendIntegration();

// Update training status every 10 seconds
setInterval(() => {
    if (window.backend) {
        window.backend.updateTrainingStatus();
    }
}, 10000);
