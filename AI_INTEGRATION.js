/**
 * Neural Network AI Integration Module
 * Replaces scripted AI with trained model predictions
 *
 * Usage:
 *  - Include this in index.php after backend-integration.js
 *  - Call useTrainedModelAI() to enable trained AI
 *  - Call useScriptedAI() to revert to scripted AI
 */

class AIController {
    constructor() {
        this.useTrainedModel = false;
        this.modelCache = new Map(); // Cache inference results
        this.lastInferenceTime = {};
        this.INFERENCE_COOLDOWN = 100; // ms between inferences for same fighter
    }

    /**
     * Enable trained model-based AI
     */
    enable() {
        this.useTrainedModel = true;
        console.log('🤖 Trained Model AI ENABLED');
    }

    /**
     * Disable trained model AI (fall back to scripted)
     */
    disable() {
        this.useTrainedModel = false;
        console.log('🎯 Scripted AI ENABLED');
    }

    /**
     * Get AI action for a fighter
     * @param {Fighter} fighter - The AI fighter
     * @param {Fighter[]} allFighters - All fighters in arena
     * @param {number} ringSize - Arena size for observation
     * @returns {Promise<number>} Action (0-9) or null if using scripted AI
     */
    async getAction(fighter, allFighters, ringSize = 160) {
        if (!this.useTrainedModel || !window.backend || !window.backend.trainedModel) {
            return null; // Use scripted AI
        }

        // Inference cooldown per fighter
        const now = Date.now();
        const lastTime = this.lastInferenceTime[fighter.id] || 0;
        if (now - lastTime < this.INFERENCE_COOLDOWN) {
            return null; // Skip inference, use previous action
        }

        try {
            // Build observation for this fighter
            const observation = window.backend.buildObservationVector(
                fighter,
                allFighters,
                ringSize
            );

            // Get fighter ID and profile name for personality-based AI
            const fighterId = fighter.id || 1;
            // Use fighter's assigned profile if available, otherwise fall back to global
            const profileName = fighter.assignedProfile || window.selectedFighterProfile || null;

            // Run inference on backend with profile support
            const result = await window.backend.runModelInference(observation, fighterId, profileName);

            if (result) {
                this.lastInferenceTime[fighter.id] = now;
                return result.action; // 0-9
            }
        } catch (err) {
            console.error('AI inference error:', err);
        }

        return null; // Fall back to scripted AI
    }

    /**
     * Execute action returned by model
     * @param {Fighter} fighter - The fighter
     * @param {number} action - Action code (0-9)
     * @param {Fighter[]} allFighters - Other fighters for targeting
     */
    executeAction(fighter, action, allFighters = []) {
        if (action === null || action === undefined) {
            return; // No action from model
        }

        // Actions 0-7: 8 directions
        // Action 8: Idle
        // Action 9: Attack

        const directions = [
            { x: 0, z: 1 },        // 0: North
            { x: 0.707, z: 0.707 }, // 1: NE
            { x: 1, z: 0 },         // 2: East
            { x: 0.707, z: -0.707 }, // 3: SE
            { x: 0, z: -1 },        // 4: South
            { x: -0.707, z: -0.707 }, // 5: SW
            { x: -1, z: 0 },        // 6: West
            { x: -0.707, z: 0.707 }  // 7: NW
        ];

        if (action < 8) {
            // Movement action
            const direction = directions[action];
            const moveSpeed = 50; // Slightly slower than player
            fighter.velocity.x = direction.x * moveSpeed;
            fighter.velocity.z = direction.z * moveSpeed;
        } else if (action === 8) {
            // Idle - reduce velocity
            fighter.velocity.multiplyScalar(0.9);
        } else if (action === 9) {
            // Attack action
            this.performTrainedAttack(fighter, allFighters);
        }
    }

    /**
     * Perform attack using trained model logic
     * @param {Fighter} attacker - The attacking fighter
     * @param {Fighter[]} allFighters - Targets
     */
    performTrainedAttack(attacker, allFighters = []) {
        let target = null;
        let minDistance = 100; // Attack range

        // Find nearest alive opponent
        allFighters.forEach(fighter => {
            if (fighter !== attacker && !fighter.eliminated) {
                const dist = attacker.position.distanceTo(fighter.position);
                if (dist < minDistance) {
                    minDistance = dist;
                    target = fighter;
                }
            }
        });

        if (target) {
            // Execute knockback
            const direction = target.position.clone().sub(attacker.position).normalize();
            target.knockBack(direction);
        }
    }
}

// Create global AI controller
window.aiController = new AIController();
