<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Wrestling Arena - Championship Royale</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial Black', Arial, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #fff;
            overflow: hidden;
        }

        #canvas-container {
            width: 100vw;
            height: 100vh;
            position: relative;
        }

        #ui-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            padding: 20px;
            font-size: 18px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
            z-index: 100;
        }

        .fighter-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }

        .fighter-item {
            background: rgba(0,0,0,0.7);
            border: 2px solid #444;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .fighter-item:hover {
            border-color: #ff6b00;
            transform: scale(1.05);
        }

        .fighter-item.controlled {
            border-color: #00ff00;
            background: rgba(0,255,0,0.1);
            box-shadow: 0 0 10px #00ff00;
        }

        .fighter-item.eliminated {
            opacity: 0.5;
            border-color: #666;
            text-decoration: line-through;
        }

        .fighter-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .fighter-health {
            width: 100%;
            height: 15px;
            background: #222;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 5px;
        }

        .health-bar {
            height: 100%;
            background: linear-gradient(90deg, #00ff00, #ffff00);
            width: 100%;
            transition: width 0.2s;
        }

        .health-bar.damaged {
            background: linear-gradient(90deg, #ff6600, #ff0000);
        }

        #status-panel {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: rgba(0,0,0,0.8);
            border: 2px solid #ff6b00;
            padding: 15px;
            border-radius: 5px;
            min-width: 300px;
            box-shadow: 0 0 20px rgba(255, 107, 0, 0.5);
        }

        .instruction {
            font-size: 14px;
            color: #ccc;
            margin-top: 8px;
        }

        .instruction code {
            background: rgba(255, 107, 0, 0.2);
            padding: 2px 5px;
            border-radius: 3px;
            color: #ff6b00;
        }

        #victory-screen {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.95);
            z-index: 200;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        #victory-screen.show {
            display: flex;
        }

        .victory-content {
            animation: scaleIn 0.5s ease-out;
        }

        .victory-title {
            font-size: 80px;
            color: #ff6b00;
            text-shadow: 0 0 20px #ff6b00;
            margin-bottom: 20px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .victory-name {
            font-size: 48px;
            color: #00ff00;
            margin-bottom: 30px;
            text-shadow: 0 0 15px #00ff00;
        }

        .victory-subtitle {
            font-size: 24px;
            color: #ccc;
            margin-bottom: 40px;
        }

        .reload-btn {
            background: linear-gradient(135deg, #ff6b00, #ff8c00);
            border: none;
            color: white;
            padding: 15px 40px;
            font-size: 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            text-transform: uppercase;
        }

        .reload-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(255, 107, 0, 0.8);
        }

        @keyframes scaleIn {
            from {
                transform: scale(0.5);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .crowd-sound {
            font-size: 12px;
            color: #ff6b00;
            margin-top: 10px;
            animation: pulse 0.5s;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .controls-info {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: rgba(0,0,0,0.8);
            border: 2px solid #00ff00;
            padding: 15px;
            border-radius: 5px;
            font-size: 13px;
            line-height: 1.6;
        }

        .controls-info strong {
            color: #00ff00;
            display: block;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .ring-counter {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(0,0,0,0.9);
            border: 3px solid #ff6b00;
            padding: 20px;
            border-radius: 5px;
            font-size: 24px;
            font-weight: bold;
            color: #ff6b00;
            text-shadow: 0 0 10px #ff6b00;
            text-align: center;
        }

        .impact-popup {
            position: fixed;
            font-size: 48px;
            font-weight: bold;
            font-family: 'Arial Black', Arial, sans-serif;
            pointer-events: none;
            z-index: 150;
            animation: impactPop 0.8s ease-out forwards;
            text-shadow: 3px 3px 0 rgba(0,0,0,0.8), -2px -2px 0 rgba(255,255,255,0.2);
        }

        @keyframes impactPop {
            0% {
                transform: translate(-50%, -50%) scale(0.2) rotate(0deg);
                opacity: 1;
            }
            50% {
                transform: translate(-50%, -150%) scale(1.2) rotate(10deg);
            }
            100% {
                transform: translate(-50%, -250%) scale(0.8) rotate(-5deg);
                opacity: 0;
            }
        }

        /* Fighter Assignment Modal */
        #fighter-assignment-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 300;
            align-items: center;
            justify-content: center;
        }

        #fighter-assignment-modal.show {
            display: flex;
        }

        .assignment-container {
            background: rgba(20, 20, 20, 0.95);
            border: 3px solid #ff5500;
            padding: 40px;
            border-radius: 10px;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 0 50px rgba(255, 85, 0, 0.5);
        }

        .assignment-title {
            font-size: 2.5em;
            color: #ffaa00;
            text-align: center;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .assignment-subtitle {
            color: #aaa;
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.1em;
        }

        .fighters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .fighter-card {
            background: rgba(40, 40, 40, 0.8);
            border: 2px solid #444;
            padding: 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .fighter-card:hover {
            border-color: #ff5500;
            box-shadow: 0 0 20px rgba(255, 85, 0, 0.3);
        }

        .fighter-card.selected {
            border-color: #ffaa00;
            background: rgba(255, 85, 0, 0.15);
            box-shadow: 0 0 20px rgba(255, 170, 0, 0.5);
        }

        .fighter-card-name {
            font-weight: bold;
            color: #ffaa00;
            font-size: 1.2em;
            margin-bottom: 10px;
        }

        .fighter-card-model {
            color: #888;
            font-size: 0.9em;
            margin-bottom: 15px;
        }

        .profile-dropdown {
            width: 100%;
            padding: 8px;
            background: rgba(50, 50, 50, 0.9);
            border: 1px solid #555;
            color: #ddd;
            border-radius: 3px;
            font-size: 0.9em;
        }

        .profile-dropdown:focus {
            outline: none;
            border-color: #ff5500;
            box-shadow: 0 0 10px rgba(255, 85, 0, 0.3);
        }

        .profile-info {
            color: #888;
            font-size: 0.85em;
            margin-top: 8px;
        }

        .assignment-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .assignment-actions button {
            padding: 15px 40px;
            font-size: 1em;
            font-weight: bold;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-transform: uppercase;
            transition: all 0.3s ease;
            min-width: 150px;
        }

        .btn-start-battle {
            background: linear-gradient(45deg, #ff5500, #ffaa00);
            color: #000;
        }

        .btn-start-battle:hover:not(:disabled) {
            transform: scale(1.05);
            box-shadow: 0 0 30px rgba(255, 85, 0, 0.6);
        }

        .btn-start-battle:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-back-menu {
            background: rgba(100, 100, 100, 0.5);
            color: #ddd;
            border: 1px solid #666;
        }

        .btn-back-menu:hover {
            background: rgba(100, 100, 100, 0.7);
            border-color: #888;
        }
    </style>
</head>
<body>
    <div id="canvas-container"></div>

    <div id="ui-overlay">
        <div class="fighter-list" id="fighter-list"></div>

        <div id="status-panel">
            <div id="status-text">⚡ LOADING CHAMPIONS...</div>
            <div class="instruction">
                <code>CLICK</code> a fighter to control
            </div>
            <div class="instruction">
                <code>SPACE</code> to knock out opponent
            </div>
            <div class="instruction">
                <code>ARROW KEYS</code> to move around the ring
            </div>
            <div class="crowd-sound" id="crowd">🎪 The crowd is going WILD!</div>
        </div>
    </div>

    <div class="ring-counter">
        <div>FIGHTERS IN RING:</div>
        <div id="fighter-count">0</div>
    </div>

    <div style="position: fixed; bottom: 200px; left: 20px; background: rgba(0,0,0,0.85); border: 2px solid #00aeff; padding: 15px; border-radius: 5px; z-index: 110; min-width: 150px;">
        <div style="color: #00aeff; font-weight: bold; margin-bottom: 10px; font-size: 12px;">🤖 AI CONTROL</div>
        <button id="ai-toggle-btn" onclick="toggleTrainedAI()" style="background: #00aeff; color: #000; border: none; padding: 8px 12px; border-radius: 3px; cursor: pointer; font-weight: bold; font-size: 11px; margin-bottom: 5px; width: 100%; transition: all 0.3s;">
            Load Trained Model
        </button>
        <button onclick="window.open('analytics.php', '_blank')" style="background: #ff00ff; color: #fff; border: 2px solid #ffff00; padding: 8px 12px; border-radius: 3px; cursor: pointer; font-weight: bold; font-size: 11px; margin-bottom: 5px; width: 100%; transition: all 0.3s; box-shadow: 0 0 10px #ff00ff;">
            📊 ANALYTICS
        </button>
        <div id="backend-status" style="font-size: 11px; color: #aaa; margin-top: 5px; word-break: break-word;">Checking backend...</div>
    </div>

    <div id="victory-screen">
        <div class="victory-content">
            <div class="victory-title">🏆 CHAMPION! 🏆</div>
            <div class="victory-name" id="champion-name"></div>
            <div class="victory-subtitle">ULTIMATE WRESTLING CHAMPION</div>
            <button class="reload-btn" onclick="location.reload()">REMATCH</button>
        </div>
    </div>

    <!-- Fighter Assignment Modal -->
    <div id="fighter-assignment-modal" class="show">
        <div class="assignment-container">
            <div class="assignment-title">⚔️ Assign Fighter Profiles</div>
            <div class="assignment-subtitle">Select fighters and assign personality profiles before entering the arena</div>
            <div class="fighters-grid" id="fightersGrid"></div>
            <div class="assignment-actions">
                <button class="btn-start-battle" id="startBattleBtn" onclick="startAssignedBattle()" disabled>
                    🚀 START BATTLE
                </button>
                <button class="btn-back-menu" onclick="window.location.href='main-menu.php'">
                    ← BACK TO MENU
                </button>
            </div>
        </div>
    </div>

    <script type="importmap">
        {
            "imports": {
                "three": "https://unpkg.com/three@0.128.0/build/three.module.js",
                "three/addons/": "https://unpkg.com/three@0.128.0/examples/jsm/"
            }
        }
    </script>

    <!-- Initialize GAME_STATE before all other scripts -->
    <script>
        // ==================== GLOBAL GAME STATE ====================
        // Initialize early so it's available to ALL scripts
        // Must use window.GAME_STATE for cross-script access
        window.GAME_STATE = {
            fighters: [],
            controlled: null,
            running: false,
            victoryAnnounced: false,
            episode_id: null,
            frameCounter: 0,
            lastFrameData: { health: 100, enemyCount: 0 }
        };
    </script>

    <script type="module">
        import * as THREE from 'three';
        import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

        // Make available globally for game code
        window.THREE = THREE;
        window.GLTFLoader = GLTFLoader;

        // ==================== GLOBAL FUNCTIONS (Accessible to HTML event handlers) ====================

        window.handleProfileSelection = function(fighterIndex, profileName) {
            const infoDiv = document.getElementById(`profile-info-${fighterIndex}`);
            if (!infoDiv) return;

            if (profileName === '') {
                infoDiv.textContent = '🎮 Player will control this fighter';
                infoDiv.style.color = '#66ff00';
            } else if (profileName === 'default') {
                infoDiv.textContent = '🤖 Using default scripted AI';
                infoDiv.style.color = '#ffaa00';
            } else {
                infoDiv.textContent = `📊 Custom personality: ${profileName}`;
                infoDiv.style.color = '#ff5500';
            }
        };

        window.startAssignedBattle = function() {
            // Get modal and ensure GAME_STATE exists
            const modal = document.getElementById('fighter-assignment-modal');
            if (modal) {
                modal.classList.remove('show');
            }

            // Ensure GAME_STATE is defined before proceeding
            if (typeof GAME_STATE === 'undefined') {
                console.error('GAME_STATE not initialized yet');
                return;
            }

            // Get selected fighter profile
            const fighterIndex = window.selectedFighterIndex || 0;
            const selectElement = document.getElementById(`profile-select-${fighterIndex}`);
            const selectedProfile = selectElement ? selectElement.value : 'default';

            // Store profile assignment for later use
            window.selectedFighterProfile = selectedProfile;
            window.selectedFighterIndex = fighterIndex;

            // Apply fighter assignments and set up battle
            let playerControlledFighter = null;

            // Loop through all fighters and apply their profile assignments
            GAME_STATE.fighters.forEach((fighter, index) => {
                const dropdown = document.getElementById(`profile-select-${index}`);
                const profile = dropdown ? dropdown.value : 'default';

                // Store profile for each fighter
                fighter.assignedProfile = profile;

                // Check if this fighter should be player-controlled
                if (profile === '' && !playerControlledFighter) {
                    playerControlledFighter = fighter;
                }
            });

            // Set up controlled fighter
            if (playerControlledFighter) {
                // Player controls this fighter
                GAME_STATE.fighters.forEach(f => f.isControlled = false);
                playerControlledFighter.isControlled = true;
                GAME_STATE.controlled = playerControlledFighter;

                document.getElementById('crowd').textContent =
                    '🔥 ' + playerControlledFighter.name + ' IS PLAYER CONTROLLED!';

                // Start backend episode for player-controlled fighter
                if (window.backend && !GAME_STATE.episode_id) {
                    const RING_SIZE = 160; // Match the game config
                    const opponentIds = GAME_STATE.fighters
                        .filter(f => f !== playerControlledFighter && !f.eliminated)
                        .map((_, i) => i + 2);
                    window.backend.startEpisode(1, opponentIds, RING_SIZE)
                        .catch(err => console.log('Backend not available:', err));
                }
            } else {
                // All fighters are AI-controlled
                // Set selected fighter as observed (for camera to follow)
                const observedFighter = GAME_STATE.fighters[fighterIndex];
                if (observedFighter) {
                    GAME_STATE.controlled = observedFighter;
                    observedFighter.isControlled = false; // AI controlled, but camera follows

                    document.getElementById('crowd').textContent =
                        '🤖 WATCHING AI BATTLE: ' + observedFighter.name;
                }
            }

            // Enable AI controller for all AI-controlled fighters
            const hasPlayerControl = GAME_STATE.fighters.some(f => !f.isControlled);
            const needsAI = !playerControlledFighter; // All AI-controlled

            if (needsAI && window.aiController) {
                window.aiController.enable();
                console.log('🤖 AI controller enabled for all fighters');
            }

            // Also check for custom profiles
            const hasCustomProfiles = GAME_STATE.fighters.some(f =>
                f.assignedProfile && f.assignedProfile !== '' && f.assignedProfile !== 'default'
            );
            if (hasCustomProfiles) {
                console.log('⭐ Custom trained profiles detected');
            }

            // Start the game loop
            GAME_STATE.running = true;
            document.getElementById('status-text').textContent = '🎬 ROUND 1: BEGIN!';
            window.updateFighterList();
            window.updateFighterCount();
        };
    </script>
    <script src="backend-integration.js"></script>
    <script src="AI_INTEGRATION.js"></script>
    <script>
        // Reference GAME_STATE which was initialized earlier
        const GAME_STATE = window.GAME_STATE;

        // Wait for module script to load THREE and GLTFLoader
        function waitForDependencies(callback, maxAttempts = 50) {
            if (typeof window.THREE !== 'undefined' && typeof window.GLTFLoader !== 'undefined') {
                callback();
            } else if (maxAttempts > 0) {
                setTimeout(() => waitForDependencies(callback, maxAttempts - 1), 100);
            } else {
                console.error('Failed to load THREE.js dependencies');
            }
        }

        function startGame() {
            // ==================== GAME CONFIG ====================
            const RING_SIZE = 160; // Square ring size (width and depth)
        const RING_MAT_HEIGHT = 2;
        const FIGHTER_GROUND_Y = RING_MAT_HEIGHT;
        const KNOCKOUT_DISTANCE = RING_SIZE / 1.2; // Diagonal distance for square
        // GAME_STATE is now defined globally above, just reset it
        GAME_STATE.fighters = [];
        GAME_STATE.controlled = null;
        GAME_STATE.running = false;
        GAME_STATE.victoryAnnounced = false;
        GAME_STATE.episode_id = null;
        GAME_STATE.frameCounter = 0;
        GAME_STATE.lastFrameData = { health: 100, enemyCount: 0 };

        // ==================== SCENE SETUP ====================
        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0x1a1a2e);
        scene.fog = new THREE.Fog(0x1a1a2e, 300, 600);

        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 1, 10000);
        camera.position.set(0, 120, 150);
        camera.lookAt(0, 0, 0);

        const renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFShadowShadowMap;
        document.getElementById('canvas-container').appendChild(renderer.domElement);

        // ==================== LIGHTING ====================
        // Main ring spotlight (brighter)
        const ringLight = new THREE.SpotLight(0xffcc00, 4, 500, Math.PI / 2.5, 0.8, 2);
        ringLight.position.set(0, 250, 0);
        ringLight.castShadow = true;
        ringLight.shadow.mapSize.width = 2048;
        ringLight.shadow.mapSize.height = 2048;
        scene.add(ringLight);

        // Rim lights for drama (enhanced)
        const rimLight1 = new THREE.PointLight(0xff6b00, 3.5, 400);
        rimLight1.position.set(200, 120, 150);
        scene.add(rimLight1);

        const rimLight2 = new THREE.PointLight(0x00ccff, 3.5, 400);
        rimLight2.position.set(-200, 120, -150);
        scene.add(rimLight2);

        // Additional fill lights for better model visibility
        const fillLight1 = new THREE.PointLight(0xffffff, 2, 350);
        fillLight1.position.set(150, 80, -150);
        scene.add(fillLight1);

        const fillLight2 = new THREE.PointLight(0xffffff, 2, 350);
        fillLight2.position.set(-150, 80, 150);
        scene.add(fillLight2);

        // Directional light for overall illumination
        const dirLight = new THREE.DirectionalLight(0xffffff, 1.5);
        dirLight.position.set(100, 150, 100);
        dirLight.castShadow = true;
        dirLight.shadow.mapSize.width = 2048;
        dirLight.shadow.mapSize.height = 2048;
        scene.add(dirLight);

        // Ambient light (increased for better visibility)
        const ambientLight = new THREE.AmbientLight(0xffffff, 1.8);
        scene.add(ambientLight);

        // ==================== ARENA CONSTRUCTION ====================

        // Wrestling ring mat - square shape
        const matGeometry = new THREE.BoxGeometry(RING_SIZE, RING_MAT_HEIGHT, RING_SIZE);
        const matMaterial = new THREE.MeshLambertMaterial({
            color: 0xff6b00,
            emissive: 0xff4400,
            shininess: 100
        });
        const ringMat = new THREE.Mesh(matGeometry, matMaterial);
        ringMat.position.y = RING_MAT_HEIGHT / 2; // Position so top is at RING_MAT_HEIGHT
        ringMat.castShadow = true;
        ringMat.receiveShadow = true;
        scene.add(ringMat);

        // Ring frame/border for visual effect - red lines on edges
        const frameMat = new THREE.MeshStandardMaterial({
            color: 0xff0000,
            metalness: 0.8,
            roughness: 0.2,
            emissive: 0xff0000
        });

        const halfSize = RING_SIZE / 2;
        // Create frame edges
        const edgeGeo = new THREE.BoxGeometry(RING_SIZE, 1, 4);
        const edgeNorth = new THREE.Mesh(edgeGeo, frameMat);
        edgeNorth.position.set(0, RING_MAT_HEIGHT + 0.5, -halfSize);
        scene.add(edgeNorth);

        const edgeSouth = new THREE.Mesh(edgeGeo, frameMat);
        edgeSouth.position.set(0, RING_MAT_HEIGHT + 0.5, halfSize);
        scene.add(edgeSouth);

        const edgeEastGeo = new THREE.BoxGeometry(4, 1, RING_SIZE);
        const edgeEast = new THREE.Mesh(edgeEastGeo, frameMat);
        edgeEast.position.set(halfSize, RING_MAT_HEIGHT + 0.5, 0);
        scene.add(edgeEast);

        const edgeWest = new THREE.Mesh(edgeEastGeo, frameMat);
        edgeWest.position.set(-halfSize, RING_MAT_HEIGHT + 0.5, 0);
        scene.add(edgeWest);

        // Ring ropes (4 sides) - improved rope structure
        function createRope(startPos, endPos) {
            const ropeGeo = new THREE.CylinderGeometry(2.5, 2.5, 1, 16);
            const ropeMat = new THREE.MeshStandardMaterial({
                color: 0xdddddd,
                roughness: 0.4,
                metalness: 0.3
            });
            const ropeSegments = 25;
            const direction = endPos.clone().sub(startPos);
            const length = direction.length();
            const originalDirection = direction.clone();
            direction.normalize();

            for (let i = 0; i < ropeSegments; i++) {
                const rope = new THREE.Mesh(ropeGeo, ropeMat);
                const t = i / ropeSegments;
                const pos = startPos.clone().addScaledVector(originalDirection.normalize(), length * t);
                rope.position.copy(pos);
                rope.rotation.z = Math.atan2(originalDirection.y, originalDirection.length() / ropeSegments);
                rope.castShadow = true;
                scene.add(rope);
            }
        }

        const ropeHeight = RING_MAT_HEIGHT + 35;
        const ropeOffset = halfSize - 8;
        // Four ropes around the square ring (north, east, south, west)
        createRope(new THREE.Vector3(-ropeOffset, ropeHeight, -ropeOffset), new THREE.Vector3(ropeOffset, ropeHeight, -ropeOffset));
        createRope(new THREE.Vector3(ropeOffset, ropeHeight, -ropeOffset), new THREE.Vector3(ropeOffset, ropeHeight, ropeOffset));
        createRope(new THREE.Vector3(ropeOffset, ropeHeight, ropeOffset), new THREE.Vector3(-ropeOffset, ropeHeight, ropeOffset));
        createRope(new THREE.Vector3(-ropeOffset, ropeHeight, ropeOffset), new THREE.Vector3(-ropeOffset, ropeHeight, -ropeOffset));

        // Ring posts (corners) - much improved appearance
        const postGeo = new THREE.CylinderGeometry(5, 6, ropeHeight + RING_MAT_HEIGHT, 32);
        const postMat = new THREE.MeshStandardMaterial({
            color: 0x1a1a1a,
            metalness: 0.9,
            roughness: 0.1
        });
        const corners = [
            [-ropeOffset, 0, -ropeOffset],
            [ropeOffset, 0, -ropeOffset],
            [ropeOffset, 0, ropeOffset],
            [-ropeOffset, 0, ropeOffset]
        ];

        corners.forEach(pos => {
            const post = new THREE.Mesh(postGeo, postMat);
            post.position.set(pos[0], (ropeHeight + RING_MAT_HEIGHT) / 2, pos[2]);
            post.castShadow = true;
            scene.add(post);

            // Add glowing top caps
            const capGeo = new THREE.SphereGeometry(6, 16, 16);
            const capMat = new THREE.MeshStandardMaterial({
                color: 0xff6b00,
                emissive: 0xff6b00,
                metalness: 0.9
            });
            const cap = new THREE.Mesh(capGeo, capMat);
            cap.position.set(pos[0], ropeHeight + RING_MAT_HEIGHT + 3, pos[2]);
            scene.add(cap);
        });

        // Stadium seating - no grey boxes, just crowd distribution

        // Enhanced crowd with individual spectators - positioned around square ring
        function createCrowd() {
            const crowdColors = [
                0xff0000, 0x0000ff, 0xffff00, 0x00ff00, 0xff00ff,
                0xff6600, 0x00ffff, 0xff99cc, 0x99ff00, 0xff0099, 0xff3366, 0x00ff99
            ];

            // Create crowd on all 4 sides of the square ring
            const createSideRows = (startX, endX, startZ, endZ, sideName) => {
                // 5 rows deep from each side
                for (let row = 0; row < 5; row++) {
                    const depthOffset = 20 + row * 25;
                    const rowY = 18 + row * 12 + Math.random() * 3;

                    // Spread people along the side
                    const numPeople = 15 + row * 5;
                    for (let i = 0; i < numPeople; i++) {
                        const t = i / numPeople;
                        const x = startX + (endX - startX) * t;
                        const z = startZ + (endZ - startZ) * t;

                        // Apply depth offset based on side
                        let finalX = x;
                        let finalZ = z;

                        if (sideName === 'north') finalZ -= depthOffset;
                        if (sideName === 'south') finalZ += depthOffset;
                        if (sideName === 'west') finalX -= depthOffset;
                        if (sideName === 'east') finalX += depthOffset;

                        // Create spectator
                        const bodyGeo = new THREE.CylinderGeometry(2, 2.5, 12, 8);
                        const bodyMat = new THREE.MeshLambertMaterial({
                            color: crowdColors[Math.floor(Math.random() * crowdColors.length)]
                        });
                        const body = new THREE.Mesh(bodyGeo, bodyMat);

                        const headGeo = new THREE.SphereGeometry(2, 8, 8);
                        const headMat = new THREE.MeshLambertMaterial({ color: 0xffcc99 });
                        const head = new THREE.Mesh(headGeo, headMat);
                        head.position.y = 8;

                        const spectator = new THREE.Group();
                        spectator.add(body);
                        spectator.add(head);
                        spectator.position.set(finalX, rowY + Math.random() * 2, finalZ);
                        scene.add(spectator);
                    }
                }
            };

            // Create crowd on all 4 sides
            const h = halfSize - 10;
            createSideRows(-h, h, -h, -h, 'north');    // North side
            createSideRows(h, h, -h, h, 'east');        // East side
            createSideRows(h, -h, h, h, 'south');       // South side
            createSideRows(-h, -h, h, -h, 'west');      // West side
        }
        createCrowd();

        // ==================== FIGHTER CLASS ====================
        class Fighter {
            constructor(name, model) {
                this.name = name;
                this.model = model;
                this.health = 100;
                this.maxHealth = 100;
                this.velocity = new THREE.Vector3(0, 0, 0);
                this.angularVelocity = 0;
                this.isControlled = false;
                this.eliminated = false;
                this.knockCooldown = 0;
                this.knockoutAnimating = false;
                this.knockoutTimer = 0;
                this.knockoutDuration = 1.5;
                this.knockoutStartPos = new THREE.Vector3();
                this.knockoutDirection = new THREE.Vector3();
                this.modelYOffset = 0; // Offset for centered models

                // Random fighting attributes for AI
                this.knockAggression = Math.random() * 0.7 + 0.3; // 0.3 - 1.0 (how likely to attack)
                this.knockFrequency = Math.random() * 3 + 1;     // 1 - 4 seconds between attacks
                this.aiAttackCooldown = 0;                        // Time until next AI attack

                this.ai = {
                    moveTimer: 0,
                    moveDirection: new THREE.Vector3(),
                    targetSpeed: 0
                };
                // Random starting position within the square ring
                const halfSize = RING_SIZE / 2;
                this.position = new THREE.Vector3(
                    (Math.random() - 0.5) * RING_SIZE * 0.8,
                    FIGHTER_GROUND_Y,
                    (Math.random() - 0.5) * RING_SIZE * 0.8
                );
                this.scale = Math.random() * 0.3 + 0.7; // Random size variation

                this.setupModel();
            }

            setupModel() {
                // Calculate bounding box from ONLY visible meshes (ignore armature/bones)
                const box = new THREE.Box3();
                let hasMeshes = false;

                this.model.traverse((child) => {
                    if (child.isMesh) {
                        hasMeshes = true;
                        const meshBox = new THREE.Box3().setFromObject(child);
                        box.union(meshBox);
                    }
                });

                // Fallback if no meshes found
                if (!hasMeshes) {
                    box.setFromObject(this.model);
                }

                const size = box.getSize(new THREE.Vector3());
                const maxDim = Math.max(size.x, size.y, size.z);
                const targetSize = 30; // Target height
                const scaleFactor = targetSize / maxDim;

                // Apply scaling
                this.model.scale.multiplyScalar(scaleFactor * this.scale);

                // Reset position to calculate bounding box properly
                this.model.position.set(0, 0, 0);
                this.model.rotation.set(0, 0, 0);

                // Force update the matrix world
                this.model.updateMatrixWorld(true);

                // Get full bounding box including all children
                const fullBox = new THREE.Box3().setFromObject(this.model);
                const minY = fullBox.min.y;
                const maxY = fullBox.max.y;
                const height = maxY - minY;

                console.log(`${this.name} - minY: ${minY.toFixed(2)}, maxY: ${maxY.toFixed(2)}, height: ${height.toFixed(2)}, scale: ${scaleFactor.toFixed(4)}`);

                // IMPORTANT: If model geometry is centered or has weird origin,
                // we need to offset it so the BOTTOM touches the ground
                // Simple formula: move the model down by minY amount to put bottom at 0, then up by FIGHTER_GROUND_Y
                const yOffset = FIGHTER_GROUND_Y - minY;

                // Store the offset - this accounts for the difference between the model's geometry
                // and where we want to position it. For centered models (minY < 0), this will be positive.
                // For normal models (minY >= 0), this will be small/zero.
                this.modelYOffset = yOffset - FIGHTER_GROUND_Y;

                // Now apply the final position with proper Y offset
                this.model.position.copy(this.position);
                this.model.position.y = yOffset;

                // Update matrix again with final position
                this.model.updateMatrixWorld(true);

                this.model.castShadow = true;
                this.model.receiveShadow = true;

                // Ensure all children cast/receive shadows
                this.model.traverse((child) => {
                    if (child.isMesh) {
                        child.castShadow = true;
                        child.receiveShadow = true;
                    }
                });
            }

            update(deltaTime, controls) {
                // Handle knockout animation
                if (this.knockoutAnimating) {
                    this.updateKnockoutAnimation(deltaTime);
                    return;
                }

                if (this.eliminated) return;

                // Movement
                if (this.isControlled) {
                    this.handlePlayerMovement(controls);
                } else {
                    // Use AI movement (can be async via trained models or sync via scripted)
                    // For now, handle synchronously - AI will manage async internally if needed
                    const result = this.handleAIMovement();
                    // If it returns a promise, we'll await it in a background task
                    if (result instanceof Promise) {
                        // Don't await here - just let it resolve in background
                        // The AI controller handles partial updates
                    }
                    // AI auto-attacks on cooldown
                    this.updateAIAttack(deltaTime);
                }

                // Apply velocity
                this.position.add(this.velocity.clone().multiplyScalar(deltaTime));
                this.velocity.multiplyScalar(0.95); // Friction

                // Keep in ring
                this.checkRingBoundaries();

                // Update model position with Y offset for centered models
                this.model.position.copy(this.position);
                this.model.position.y += this.modelYOffset;

                // Simple rotation towards movement direction
                if (this.velocity.length() > 0.5) {
                    const targetAngle = Math.atan2(this.velocity.x, this.velocity.z);
                    this.angularVelocity = targetAngle - this.model.rotation.y;
                    this.model.rotation.y += this.angularVelocity * 0.1;
                }

                this.knockCooldown = Math.max(0, this.knockCooldown - deltaTime);
            }

            updateKnockoutAnimation(deltaTime) {
                this.knockoutTimer += deltaTime;
                const progress = this.knockoutTimer / this.knockoutDuration;

                if (progress >= 1) {
                    // Animation complete
                    this.model.visible = false;
                    return;
                }

                // Ease out - start fast, slow down
                const easeProgress = 1 - Math.pow(1 - progress, 3);

                // Move along knockout direction and upward
                const horizontalDist = 200 * easeProgress;
                const verticalDist = 100 * Math.sin(progress * Math.PI); // Arc upward then down

                this.position.copy(this.knockoutStartPos);
                this.position.addScaledVector(this.knockoutDirection, horizontalDist);
                this.position.y = FIGHTER_GROUND_Y + verticalDist;

                // Spin the model as it flies out
                this.model.rotation.x += deltaTime * 10;
                this.model.rotation.z += deltaTime * 15;

                // Update model position with Y offset
                this.model.position.copy(this.position);
                this.model.position.y += this.modelYOffset;
            }

            handlePlayerMovement(controls) {
                const moveSpeed = 60;
                const direction = new THREE.Vector3();

                if (controls.up) direction.z -= 1;
                if (controls.down) direction.z += 1;
                if (controls.left) direction.x -= 1;
                if (controls.right) direction.x += 1;

                if (direction.length() > 0) {
                    direction.normalize();
                    this.velocity.copy(direction.multiplyScalar(moveSpeed));
                }
            }

            handleAIMovement() {
                // Try to use trained model AI if available (async, will update in background)
                if (window.aiController && window.aiController.useTrainedModel) {
                    // Trigger async AI in background, but don't block on it
                    window.aiController.getAction(
                        this,
                        GAME_STATE.fighters,
                        RING_SIZE
                    ).then(action => {
                        if (action !== null) {
                            // Use trained model action when available
                            window.aiController.executeAction(this, action, GAME_STATE.fighters);
                        }
                    }).catch(err => {
                        // Silent fail, fall back to scripted
                        console.log('AI inference error, using scripted AI');
                    });
                }

                // Always run scripted AI as fallback (or as default)
                this.ai.moveTimer -= 0.016;

                if (this.ai.moveTimer <= 0) {
                    this.ai.moveDirection = new THREE.Vector3(
                        (Math.random() - 0.5) * 2,
                        0,
                        (Math.random() - 0.5) * 2
                    ).normalize();
                    this.ai.targetSpeed = Math.random() * 40 + 20;
                    this.ai.moveTimer = Math.random() * 3 + 1;

                    // Target other fighters occasionally
                    if (Math.random() < 0.3 && GAME_STATE.fighters.length > 1) {
                        const target = GAME_STATE.fighters[Math.floor(Math.random() * GAME_STATE.fighters.length)];
                        if (target !== this && !target.eliminated) {
                            const toTarget = target.position.clone().sub(this.position);
                            if (toTarget.length() > 0) {
                                this.ai.moveDirection = toTarget.normalize();
                            }
                        }
                    }
                }

                this.velocity.copy(this.ai.moveDirection.clone().multiplyScalar(this.ai.targetSpeed));
            }

            updateAIAttack(deltaTime) {
                this.aiAttackCooldown -= deltaTime;

                // Check if it's time to consider an attack
                if (this.aiAttackCooldown <= 0) {
                    // Reset cooldown for next attack opportunity
                    this.aiAttackCooldown = this.knockFrequency + (Math.random() - 0.5) * 2;

                    // Decide whether to attack based on aggression
                    if (Math.random() < this.knockAggression) {
                        this.performAIAttack();
                    }
                }
            }

            performAIAttack() {
                let targetEnemy = null;
                let minDistance = 100; // AI attack range

                // Find nearest opponent
                GAME_STATE.fighters.forEach(fighter => {
                    if (fighter !== this && !fighter.eliminated) {
                        const distance = this.position.distanceTo(fighter.position);
                        if (distance < minDistance) {
                            minDistance = distance;
                            targetEnemy = fighter;
                        }
                    }
                });

                // If an enemy is in range, attack them
                if (targetEnemy) {
                    const direction = targetEnemy.position.clone().sub(this.position).normalize();
                    targetEnemy.knockBack(direction);

                    // Show impact popup
                    const worldPos = targetEnemy.model.position.clone();
                    const screenPos = new THREE.Vector3();
                    screenPos.copy(worldPos);
                    screenPos.project(camera);

                    const screenX = (screenPos.x * 0.5 + 0.5) * window.innerWidth;
                    const screenY = (-screenPos.y * 0.5 + 0.5) * window.innerHeight;
                    createImpactPopup(screenX, screenY);

                    // Crowd reaction
                    const aiMessages = [
                        '💥 ' + this.name + ' STRIKES ' + targetEnemy.name + '!',
                        '⚡ ' + this.name + ' UNLEASHES A MASSIVE BLOW!',
                        '🔥 ' + this.name + ' ATTACKS!',
                        '💪 ' + this.name + ' THROWS DOWN!',
                        '🎯 ' + this.name + ' CONNECTS!'
                    ];
                    const message = aiMessages[Math.floor(Math.random() * aiMessages.length)];
                    document.getElementById('crowd').textContent = message;

                    window.updateFighterList();
                    window.updateFighterCount();
                }
            }

            checkRingBoundaries() {
                const halfSize = RING_SIZE / 2;
                const bounceDistance = 12;
                const eliminateDistance = KNOCKOUT_DISTANCE;

                // Check if completely out of the ring
                if (Math.abs(this.position.x) > eliminateDistance || Math.abs(this.position.z) > eliminateDistance) {
                    this.eliminate();
                    document.getElementById('crowd').textContent = '😱 ' + this.name.toUpperCase() + ' IS OUT OF THE RING!';
                    return;
                }

                // Check and push back if approaching boundaries
                let needsPush = false;
                let pushX = 0;
                let pushZ = 0;

                // Check X boundaries
                if (this.position.x > halfSize - bounceDistance) {
                    needsPush = true;
                    pushX = (halfSize - bounceDistance - this.position.x) * 5;
                    if (this.position.x > halfSize - 5) {
                        this.position.x = halfSize - 8;
                    }
                }
                if (this.position.x < -halfSize + bounceDistance) {
                    needsPush = true;
                    pushX = (-halfSize + bounceDistance - this.position.x) * 5;
                    if (this.position.x < -halfSize + 5) {
                        this.position.x = -halfSize + 8;
                    }
                }

                // Check Z boundaries
                if (this.position.z > halfSize - bounceDistance) {
                    needsPush = true;
                    pushZ = (halfSize - bounceDistance - this.position.z) * 5;
                    if (this.position.z > halfSize - 5) {
                        this.position.z = halfSize - 8;
                    }
                }
                if (this.position.z < -halfSize + bounceDistance) {
                    needsPush = true;
                    pushZ = (-halfSize + bounceDistance - this.position.z) * 5;
                    if (this.position.z < -halfSize + 5) {
                        this.position.z = -halfSize + 8;
                    }
                }

                // Apply push back
                if (needsPush) {
                    this.velocity.x += pushX;
                    this.velocity.z += pushZ;
                }
            }

            eliminate() {
                this.eliminated = true;
                this.knockoutAnimating = true;
                this.knockoutTimer = 0;

                // Set knockout animation start position
                this.knockoutStartPos.copy(this.position);

                // Determine direction to fly out (away from ring center)
                const direction = this.position.clone().normalize();
                this.knockoutDirection.copy(direction);

                window.updateFighterList();
            }

            knockBack(direction) {
                if (this.knockCooldown > 0) return;

                const force = 150;
                this.velocity.copy(direction.normalize().multiplyScalar(force));
                this.health -= 20;
                this.knockCooldown = 0.5;

                if (this.health <= 0) {
                    this.health = 0;
                    this.eliminate();
                }
            }
        }

        // ==================== LOADER & INITIALIZATION ====================
        const loader = new window.GLTFLoader();

        // Dynamically load all GLB files from the Insert-GLBS directory
        let glbFiles = [];

        // Fetch directory listing via PHP endpoint
        fetch('list-glb-files.php')
            .then(response => response.json())
            .then(files => {
                glbFiles = files;
                startLoading();
            })
            .catch(() => {
                // Fallback if PHP endpoint not available (loads from Insert-GLBS folder)
                console.log('Using fallback GLB file list from Insert-GLBS folder');
                glbFiles = [
                    'Insert-GLBS/bacon.glb',
                    'Insert-GLBS/gum-guy.glb',
                    'Insert-GLBS/gum-tape-guy.glb',
                    'Insert-GLBS/scaryblue.glb',
                    'Insert-GLBS/spongebob.glb'
                ];
                startLoading();
            });

        function startLoading() {
            if (glbFiles.length === 0) {
                console.error('No GLB files found!');
                document.getElementById('status-text').textContent = '❌ NO FIGHTERS FOUND!';
                return;
            }

            let loadedModels = 0;
            const fighters = [];

            glbFiles.forEach((file, index) => {
                loader.load(file, (gltf) => {
                    const model = gltf.scene;
                    const fighterName = file.replace('.glb', '').replace(/\+/g, ' ').toUpperCase();
                    const fighter = new Fighter(fighterName, model);

                    scene.add(model);
                    fighters.push(fighter);
                    GAME_STATE.fighters.push(fighter);

                    loadedModels++;
                    document.getElementById('status-text').textContent =
                        `⚡ LOADING CHAMPIONS... ${loadedModels}/${glbFiles.length}`;

                    if (loadedModels === glbFiles.length) {
                        initializeGame();
                    }
                }, undefined, (error) => {
                    console.error('Error loading', file, error);
                    loadedModels++;
                    if (loadedModels === glbFiles.length) {
                        initializeGame();
                    }
                });
            });
        }

        // ==================== GAME INITIALIZATION ====================
        function initializeGame() {
            // Show the fighter assignment modal so player can choose personalities
            showFighterAssignmentModal();
        }

        function showFighterAssignmentModal() {
            const modal = document.getElementById('fighter-assignment-modal');
            const grid = document.getElementById('fightersGrid');
            const API_BASE = 'http://localhost:8001/api';
            const API_TIMEOUT = 5000;

            // Show the modal
            if (modal) {
                modal.classList.add('show');
            }

            grid.innerHTML = '';

            // Create a card for each loaded fighter
            GAME_STATE.fighters.forEach((fighter, index) => {
                const card = document.createElement('div');
                card.className = 'fighter-card';
                card.id = `card-${index}`;

                const fighterData = {
                    fighterIndex: index,
                    fighterName: fighter.name,
                    profileSelected: null
                };

                // Add data attribute to track selection
                card.setAttribute('data-fighter-index', index);

                card.innerHTML = `
                    <div class="fighter-card-name">${fighter.name}</div>
                    <div class="fighter-card-model">Fighter ID: ${index + 1}</div>
                    <select class="profile-dropdown" id="profile-select-${index}" onchange="handleProfileSelection(${index}, this.value)">
                        <option value="">🚫 No AI (Player Controlled)</option>
                        <option value="default" selected>🤖 Default AI</option>
                        <optgroup label="Custom Personalities" id="personalities-${index}"></optgroup>
                    </select>
                    <div class="profile-info" id="profile-info-${index}" style="color: #ffaa00;">🤖 Using default scripted AI</div>
                `;

                card.addEventListener('click', () => {
                    // Toggle selection
                    const isSelected = card.classList.contains('selected');
                    document.querySelectorAll('.fighter-card').forEach(c => c.classList.remove('selected'));
                    if (!isSelected) {
                        card.classList.add('selected');
                        window.selectedFighterIndex = index;
                    }
                });

                grid.appendChild(card);

                // Load profiles for this fighter asynchronously
                fetch(`${API_BASE}/fighter-profiles/${index + 1}`, { timeout: API_TIMEOUT })
                    .then(res => {
                        // Only try to parse JSON if response is OK
                        if (!res.ok) {
                            return []; // Return empty array for 404 or other errors
                        }
                        return res.json();
                    })
                    .catch(err => {
                        // Silently ignore errors (no profiles for this fighter)
                        return [];
                    })
                    .then(profiles => {
                        if (profiles && profiles.length > 0) {
                            const optgroup = document.getElementById(`personalities-${index}`);
                            profiles.forEach(profile => {
                                const option = document.createElement('option');
                                option.value = profile.profile_name;
                                option.textContent = `⭐ ${profile.profile_name}`;
                                optgroup.appendChild(option);
                            });
                        }
                    });
            });

            // Select first fighter by default
            if (GAME_STATE.fighters.length > 0) {
                const firstCard = document.getElementById('card-0');
                if (firstCard) {
                    firstCard.classList.add('selected');
                    window.selectedFighterIndex = 0;
                }
            }

            // Update button disabled state
            updateStartBattleButton();
        }

        function handleProfileSelection(fighterIndex, profileName) {
            const infoDiv = document.getElementById(`profile-info-${fighterIndex}`);
            if (profileName === '') {
                infoDiv.textContent = '🎮 Player will control this fighter';
                infoDiv.style.color = '#66ff00';
            } else if (profileName === 'default') {
                infoDiv.textContent = '🤖 Using default scripted AI';
                infoDiv.style.color = '#ffaa00';
            } else {
                infoDiv.textContent = `📊 Custom personality: ${profileName}`;
                infoDiv.style.color = '#ff5500';
            }
        }

        function updateStartBattleButton() {
            const btn = document.getElementById('startBattleBtn');
            btn.disabled = typeof window.selectedFighterIndex === 'undefined';
        }

        window.updateFighterList = function() {
            const listContainer = document.getElementById('fighter-list');
            listContainer.innerHTML = '';

            GAME_STATE.fighters.forEach((fighter, index) => {
                const item = document.createElement('div');
                item.className = 'fighter-item';

                if (fighter.eliminated) {
                    item.classList.add('eliminated');
                } else if (fighter === GAME_STATE.controlled) {
                    item.classList.add('controlled');
                }

                item.innerHTML = `
                    <div class="fighter-name">${fighter.name}</div>
                    <div class="fighter-health">
                        <div class="health-bar ${fighter.health < 30 ? 'damaged' : ''}"
                             style="width: ${fighter.health}%"></div>
                    </div>
                `;

                if (!fighter.eliminated) {
                    item.addEventListener('click', () => {
                        // Clear previous controlled fighter's control flag
                        GAME_STATE.fighters.forEach(f => f.isControlled = false);

                        // Set new controlled fighter
                        GAME_STATE.controlled = fighter;
                        fighter.isControlled = true;

                        // Start backend episode when player takes control
                        if (window.backend && !GAME_STATE.episode_id) {
                            const RING_SIZE = 160; // Match game config
                            const opponentIds = GAME_STATE.fighters
                                .filter(f => f !== fighter && !f.eliminated)
                                .map((_, i) => i + 2);
                            window.backend.startEpisode(1, opponentIds, RING_SIZE);
                        }

                        window.updateFighterList();
                        document.getElementById('crowd').textContent =
                            '🔥 ' + fighter.name + ' HAS ENTERED THE ARENA!';
                    });
                }

                listContainer.appendChild(item);
            });
        };

        window.updateFighterCount = function() {
            const remaining = GAME_STATE.fighters.filter(f => !f.eliminated).length;
            document.getElementById('fighter-count').textContent = remaining;

            if (remaining === 1 && !GAME_STATE.victoryAnnounced) {
                GAME_STATE.victoryAnnounced = true;
                GAME_STATE.running = false;
                window.announceVictory();
            }
        };

        window.announceVictory = function() {
            const champion = GAME_STATE.fighters.find(f => !f.eliminated);
            if (champion) {
                document.getElementById('champion-name').textContent = champion.name;
                document.getElementById('victory-screen').classList.add('show');
                document.getElementById('crowd').textContent = '👑 WE HAVE A CHAMPION! 👑';

                // Complete episode in backend
                if (window.backend) {
                    const playerControlled = GAME_STATE.controlled;
                    const playerWon = (champion === playerControlled);

                    window.backend.completeEpisode({
                        winner_id: 1, // Controlled fighter ID
                        final_health: champion.health,
                        player_won: playerWon
                    });

                    if (playerWon) {
                        console.log('🏆 Victory! Episode saved.');
                    } else {
                        console.log('💔 Defeated. Episode saved for analysis.');
                    }
                }
            }
        };

        // ==================== INPUT HANDLING ====================
        const controls = {
            up: false,
            down: false,
            left: false,
            right: false,
            attacking: false
        };

        // Track last action for training data
        window.lastAction = 8; // 8 = idle

        document.addEventListener('keydown', (e) => {
            const key = e.key.toLowerCase();
            const code = e.code.toLowerCase();

            // Arrow keys
            if (code === 'arrowup') { controls.up = true; window.lastAction = 0; } // North
            if (code === 'arrowdown') { controls.down = true; window.lastAction = 4; } // South
            if (code === 'arrowleft') { controls.left = true; window.lastAction = 6; } // West
            if (code === 'arrowright') { controls.right = true; window.lastAction = 2; } // East

            // WASD keys
            if (key === 'w') { controls.up = true; window.lastAction = 0; } // North
            if (key === 's') { controls.down = true; window.lastAction = 4; } // South
            if (key === 'a') { controls.left = true; window.lastAction = 6; } // West
            if (key === 'd') { controls.right = true; window.lastAction = 2; } // East

            // Space for attack
            if (e.code === 'Space') {
                e.preventDefault();
                window.lastAction = 9; // Attack
                performAttack();
            }
        });

        document.addEventListener('keyup', (e) => {
            const key = e.key.toLowerCase();
            const code = e.code.toLowerCase();

            // Arrow keys
            if (code === 'arrowup') controls.up = false;
            if (code === 'arrowdown') controls.down = false;
            if (code === 'arrowleft') controls.left = false;
            if (code === 'arrowright') controls.right = false;

            // WASD keys
            if (key === 'w') controls.up = false;
            if (key === 's') controls.down = false;
            if (key === 'a') controls.left = false;
            if (key === 'd') controls.right = false;
        });

        // Create impact popup animation
        function createImpactPopup(screenX, screenY) {
            const impacts = ['POW!', 'SOCK!', 'BAM!', 'WHAM!', 'SMACK!', 'BOOM!', 'CRASH!', 'KAPOW!'];
            const colors = ['#ff0000', '#ff6600', '#ffff00', '#00ff00', '#00ffff', '#ff00ff'];

            const randomImpact = impacts[Math.floor(Math.random() * impacts.length)];
            const randomColor = colors[Math.floor(Math.random() * colors.length)];

            const popup = document.createElement('div');
            popup.className = 'impact-popup';
            popup.textContent = randomImpact;
            popup.style.left = screenX + 'px';
            popup.style.top = screenY + 'px';
            popup.style.color = randomColor;

            document.body.appendChild(popup);

            // Remove the element after animation completes
            setTimeout(() => {
                popup.remove();
            }, 800);
        }

        function performAttack() {
            if (!GAME_STATE.controlled || GAME_STATE.controlled.eliminated) return;

            const attacker = GAME_STATE.controlled;
            let hitTarget = null;
            let minDistance = 60; // Increased attack range

            // Find nearest opponent within attack range
            GAME_STATE.fighters.forEach(fighter => {
                if (fighter !== attacker && !fighter.eliminated) {
                    const distance = attacker.position.distanceTo(fighter.position);
                    if (distance < minDistance) {
                        minDistance = distance;
                        hitTarget = fighter;
                    }
                }
            });

            if (hitTarget) {
                // Create a visual effect at impact point
                const direction = hitTarget.position.clone().sub(attacker.position).normalize();
                hitTarget.knockBack(direction);

                // Get the screen position of the hit target for the popup
                const worldPos = hitTarget.model.position.clone();
                const screenPos = new THREE.Vector3();
                screenPos.copy(worldPos);
                screenPos.project(camera);

                const screenX = (screenPos.x * 0.5 + 0.5) * window.innerWidth;
                const screenY = (-screenPos.y * 0.5 + 0.5) * window.innerHeight;

                // Create the impact popup
                createImpactPopup(screenX, screenY);

                // More dramatic crowd reaction
                const crowdMessages = [
                    '💥 MASSIVE HIT! ' + hitTarget.name + ' FLIES BACK!',
                    '⚡ POW! ' + hitTarget.name + ' TAKES THE DAMAGE!',
                    '🔥 CRITICAL HIT! ' + hitTarget.name + ' STUMBLES!',
                    '💪 DEVASTATING BLOW! ' + hitTarget.name + ' REELS!'
                ];
                const message = crowdMessages[Math.floor(Math.random() * crowdMessages.length)];
                document.getElementById('crowd').textContent = message;

                window.updateFighterList();
                window.updateFighterCount();
            } else {
                // Miss message
                document.getElementById('crowd').textContent = '❌ ' + attacker.name + ' MISSED!';
            }
        }

        // ==================== ANIMATION LOOP ====================
        const clock = new THREE.Clock();
        function animate() {
            requestAnimationFrame(animate);

            if (GAME_STATE.running) {
                const deltaTime = clock.getDelta();
                GAME_STATE.frameCounter++;

                // Update fighters
                GAME_STATE.fighters.forEach(fighter => {
                    fighter.update(deltaTime, controls);
                });

                // Record frame data for training (every FRAME_SAMPLE_RATE frames)
                if (GAME_STATE.controlled && window.backend) {
                    const controlled = GAME_STATE.controlled;
                    const obs = window.backend.buildObservationVector(
                        controlled,
                        GAME_STATE.fighters,
                        RING_SIZE
                    );

                    const aliveEnemies = GAME_STATE.fighters.filter(f => f !== controlled && !f.eliminated);
                    const reward = window.backend.calculateFrameReward(controlled, GAME_STATE.lastFrameData);

                    window.backend.recordFrame({
                        fighter_id: 1,
                        position: [controlled.position.x, controlled.position.z],
                        health: controlled.health,
                        velocity: [controlled.velocity.x, controlled.velocity.z],
                        observation_vector: obs,
                        action_taken: window.lastAction || 8,
                        reward_delta: reward,
                        cumulative_reward: (GAME_STATE.lastFrameData.cumulativeReward || 0) + reward
                    });

                    GAME_STATE.lastFrameData = {
                        health: controlled.health,
                        enemyCount: aliveEnemies.length,
                        cumulativeReward: (GAME_STATE.lastFrameData.cumulativeReward || 0) + reward
                    };
                }

                // Check collisions between fighters
                for (let i = 0; i < GAME_STATE.fighters.length; i++) {
                    for (let j = i + 1; j < GAME_STATE.fighters.length; j++) {
                        const f1 = GAME_STATE.fighters[i];
                        const f2 = GAME_STATE.fighters[j];

                        if (!f1.eliminated && !f2.eliminated) {
                            const dist = f1.position.distanceTo(f2.position);
                            if (dist < 20) {
                                // Push apart slightly
                                const direction = f2.position.clone().sub(f1.position).normalize();
                                f1.velocity.addScaledVector(direction, -30);
                                f2.velocity.addScaledVector(direction, 30);
                            }
                        }
                    }
                }

                // Update camera to follow controlled fighter
                if (GAME_STATE.controlled && !GAME_STATE.controlled.eliminated) {
                    const targetPos = GAME_STATE.controlled.position.clone();
                    camera.position.lerp(targetPos.clone().add(new THREE.Vector3(0, 120, 150)), 0.05);
                    camera.lookAt(targetPos.x, 20, targetPos.z);
                } else {
                    // Idle camera rotation
                    const time = Date.now() * 0.0001;
                    camera.position.x = Math.cos(time) * 180;
                    camera.position.z = Math.sin(time) * 180;
                    camera.lookAt(0, 20, 0);
                }
            }

            renderer.render(scene, camera);
        }

        animate();

        // ==================== RESPONSIVE ====================
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
        }

        // Start the game after all dependencies are loaded
        waitForDependencies(startGame);

        // ==================== AI CONTROL ====================
        function toggleTrainedAI() {
            if (!window.aiController) {
                console.error('AI controller not initialized');
                return;
            }

            const btn = document.getElementById('ai-toggle-btn');

            if (window.aiController.useTrainedModel) {
                // Disable trained AI
                window.aiController.disable();
                btn.textContent = 'Load Trained Model';
                btn.style.background = '#00aeff';
                btn.style.color = '#000';
                console.log('🎯 Switched to Scripted AI');
            } else {
                // Enable trained AI
                if (!window.backend || !window.backend.trainedModel) {
                    alert('❌ No trained model available. Train a model first:\n\ncd backend\npython example_training.py');
                    return;
                }
                window.aiController.enable();
                btn.textContent = '✅ Trained Model Active';
                btn.style.background = '#00ff00';
                btn.style.color = '#000';
                console.log('🤖 Switched to Trained Model AI');
            }
        }

        // Update button status when backend loads model
        async function updateAIButtonStatus() {
            const btn = document.getElementById('ai-toggle-btn');
            if (!window.backend) return;

            if (window.backend.trainedModel) {
                btn.style.opacity = '1';
                btn.textContent = 'Load Trained Model';
                btn.style.background = '#00aeff';
            } else {
                btn.style.opacity = '0.5';
                btn.textContent = 'No Model Available';
            }
        }

        // Check for model availability periodically
        if (typeof setInterval !== 'undefined') {
            setInterval(updateAIButtonStatus, 2000);
        }
    </script>
</body>
</html>
