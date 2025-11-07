<?php
/**
 * Fighter Personality Trainer
 * Allows users to create custom personality profiles and train fighters
 * with specified personality parameters (Aggression, Positioning, etc.)
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fighter Personality Trainer</title>
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
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px 0;
            background: linear-gradient(45deg, #ff5500, #ffaa00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        header p {
            color: #ddd;
            font-size: 1.1em;
            margin-bottom: 20px;
        }

        .back-button {
            display: inline-block;
            background: linear-gradient(45deg, #cc2200, #660000);
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-weight: bold;
            font-size: 0.9em;
            margin-bottom: 30px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .back-button:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(204, 34, 0, 0.5);
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .section {
            background: rgba(20, 20, 20, 0.8);
            border: 2px solid #333;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 0 30px rgba(255, 85, 0, 0.1);
        }

        .section h2 {
            color: #ffaa00;
            font-size: 1.5em;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #ddd;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 0.95em;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select {
            width: 100%;
            padding: 12px;
            background: rgba(40, 40, 40, 0.9);
            border: 1px solid #444;
            color: #ddd;
            border-radius: 3px;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group select:focus {
            outline: none;
            border-color: #ff5500;
            box-shadow: 0 0 10px rgba(255, 85, 0, 0.3);
        }

        .slider-group {
            margin-bottom: 25px;
            padding: 15px;
            background: rgba(255, 85, 0, 0.05);
            border-left: 3px solid #ff5500;
            border-radius: 3px;
        }

        .slider-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            color: #ddd;
            font-weight: bold;
        }

        .slider-value {
            color: #ffaa00;
            font-size: 1.1em;
        }

        input[type="range"] {
            width: 100%;
            height: 6px;
            background: linear-gradient(to right, #ff5500, #ffaa00);
            border-radius: 3px;
            outline: none;
            -webkit-appearance: none;
        }

        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #ffaa00;
            cursor: pointer;
            box-shadow: 0 0 10px rgba(255, 170, 0, 0.5);
        }

        input[type="range"]::-moz-range-thumb {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #ffaa00;
            cursor: pointer;
            border: none;
            box-shadow: 0 0 10px rgba(255, 170, 0, 0.5);
        }

        .fighter-selection {
            margin-bottom: 20px;
        }

        .fighter-option {
            display: flex;
            align-items: center;
            padding: 15px;
            background: rgba(40, 40, 40, 0.8);
            border: 1px solid #444;
            border-radius: 3px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .fighter-option input[type="radio"] {
            margin-right: 15px;
            cursor: pointer;
            width: 18px;
            height: 18px;
        }

        .fighter-option:hover {
            border-color: #ff5500;
            box-shadow: 0 0 15px rgba(255, 85, 0, 0.2);
        }

        .fighter-name {
            font-weight: bold;
            color: #ffaa00;
            margin-bottom: 5px;
        }

        .fighter-info {
            color: #888;
            font-size: 0.9em;
        }

        .preview-section {
            background: rgba(255, 85, 0, 0.08);
            padding: 20px;
            border-radius: 3px;
            margin-top: 20px;
            border: 1px solid #444;
        }

        .preview-title {
            color: #ffaa00;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
            font-size: 0.9em;
        }

        .personality-badges {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .badge {
            background: rgba(255, 85, 0, 0.15);
            border: 1px solid #ff5500;
            padding: 10px;
            border-radius: 3px;
            text-align: center;
        }

        .badge-label {
            color: #888;
            font-size: 0.85em;
            display: block;
            margin-bottom: 5px;
        }

        .badge-value {
            color: #ffaa00;
            font-weight: bold;
            font-size: 1.2em;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 3px;
            font-weight: bold;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-train {
            background: linear-gradient(45deg, #ff5500, #ffaa00);
            color: #000;
        }

        .btn-train:hover:not(:disabled) {
            transform: scale(1.05);
            box-shadow: 0 0 30px rgba(255, 85, 0, 0.6);
        }

        .btn-train:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-reset {
            background: rgba(100, 100, 100, 0.5);
            color: #ddd;
            border: 1px solid #666;
        }

        .btn-reset:hover {
            background: rgba(100, 100, 100, 0.7);
            border-color: #888;
        }

        .training-status {
            margin-top: 30px;
            padding: 20px;
            background: rgba(20, 20, 20, 0.8);
            border: 2px solid #333;
            border-radius: 5px;
            display: none;
        }

        .training-status.active {
            display: block;
            border-color: #ff5500;
        }

        .status-title {
            color: #ffaa00;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
            font-size: 0.9em;
        }

        .progress-bar {
            width: 100%;
            height: 25px;
            background: rgba(40, 40, 40, 0.9);
            border: 1px solid #444;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .progress-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #ff5500, #ffaa00);
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000;
            font-weight: bold;
            font-size: 0.85em;
        }

        .status-message {
            color: #ddd;
            margin-top: 10px;
            text-align: center;
        }

        .success-message {
            color: #66ff00;
        }

        .error-message {
            color: #ff6600;
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 85, 0, 0.3);
            border-radius: 50%;
            border-top-color: #ff5500;
            animation: spin 0.6s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .info-box {
            background: rgba(255, 85, 0, 0.1);
            border-left: 3px solid #ff5500;
            padding: 15px;
            border-radius: 3px;
            margin-top: 20px;
            color: #aaa;
            line-height: 1.6;
        }

        .info-box strong {
            color: #ffaa00;
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }

            header h1 {
                font-size: 2em;
            }

            .personality-badges {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="/" class="back-button">⬅ BACK TO MAIN MENU</a>

        <header>
            <h1>🥊 Fighter Personality Trainer</h1>
            <p>Create custom AI personality profiles for your fighters</p>
        </header>

        <div class="main-content">
            <!-- Left: Fighter Selection -->
            <div class="section">
                <h2>🎭 Select Fighter</h2>
                <div class="fighter-selection" id="fighterSelection">
                    <p style="color: #888; text-align: center; padding: 20px;">
                        <span class="loading-spinner"></span>Loading fighters...
                    </p>
                </div>
                <div class="info-box">
                    <strong>💡 Tip:</strong> Select a fighter from the list. You'll be able to create multiple personality profiles for each fighter.
                </div>
            </div>

            <!-- Right: Personality Parameters -->
            <div class="section">
                <h2>⚙️ Personality Parameters</h2>

                <div class="form-group">
                    <label for="profileName">Profile Name</label>
                    <input type="text" id="profileName" placeholder="e.g., Aggressive Striker, Defensive Turtle">
                </div>

                <div class="slider-group">
                    <div class="slider-label">
                        <span>🔥 Aggression</span>
                        <span class="slider-value" id="aggressionValue">50</span>
                    </div>
                    <input type="range" id="aggression" min="0" max="100" value="50">
                    <small style="color: #888;">How likely to attack vs defend</small>
                </div>

                <div class="slider-group">
                    <div class="slider-label">
                        <span>📍 Positioning</span>
                        <span class="slider-value" id="positioningValue">50</span>
                    </div>
                    <input type="range" id="positioning" min="0" max="100" value="50">
                    <small style="color: #888;">Strategic ring positioning vs random movement</small>
                </div>

                <div class="slider-group">
                    <div class="slider-label">
                        <span>🎯 Targeting</span>
                        <span class="slider-value" id="targetingValue">50</span>
                    </div>
                    <input type="range" id="targeting" min="0" max="100" value="50">
                    <small style="color: #888;">Smart target selection vs attacking anything</small>
                </div>

                <div class="slider-group">
                    <div class="slider-label">
                        <span>⚡ Risk Tolerance</span>
                        <span class="slider-value" id="riskToleranceValue">50</span>
                    </div>
                    <input type="range" id="riskTolerance" min="0" max="100" value="50">
                    <small style="color: #888;">Willing to take damage for big attacks</small>
                </div>

                <div class="slider-group">
                    <div class="slider-label">
                        <span>💪 Endurance</span>
                        <span class="slider-value" id="enduranceValue">50</span>
                    </div>
                    <input type="range" id="endurance" min="0" max="100" value="50">
                    <small style="color: #888;">Energy management and stamina strategy</small>
                </div>

                <!-- Preview -->
                <div class="preview-section">
                    <div class="preview-title">📊 Personality Preview</div>
                    <div class="personality-badges">
                        <div class="badge">
                            <span class="badge-label">Aggression</span>
                            <span class="badge-value" id="previewAggression">50</span>
                        </div>
                        <div class="badge">
                            <span class="badge-label">Positioning</span>
                            <span class="badge-value" id="previewPositioning">50</span>
                        </div>
                        <div class="badge">
                            <span class="badge-label">Targeting</span>
                            <span class="badge-value" id="previewTargeting">50</span>
                        </div>
                        <div class="badge">
                            <span class="badge-label">Risk Tolerance</span>
                            <span class="badge-value" id="previewRiskTolerance">50</span>
                        </div>
                        <div class="badge">
                            <span class="badge-label">Endurance</span>
                            <span class="badge-value" id="previewEndurance">50</span>
                        </div>
                        <div class="badge">
                            <span class="badge-label">Profile</span>
                            <span class="badge-value" id="previewProfile" style="color: #ff5500;">UNNAMED</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Training Configuration -->
        <div class="section">
            <h2>🎓 Training Configuration</h2>
            <div class="form-group">
                <label for="epochs">Training Epochs</label>
                <input type="number" id="epochs" min="10" max="1000" value="100" step="10">
                <small style="color: #888;">Higher epochs = longer training but potentially better results</small>
            </div>

            <div class="action-buttons">
                <button class="btn btn-train" id="trainButton" disabled>
                    🚀 START TRAINING
                </button>
                <button class="btn btn-reset" id="resetButton">
                    ↻ RESET PARAMETERS
                </button>
            </div>
        </div>

        <!-- Training Status -->
        <div class="training-status" id="trainingStatus">
            <div class="status-title">⏳ Training Progress</div>
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill" style="width: 0%;">0%</div>
            </div>
            <div class="status-message" id="statusMessage"></div>
        </div>
    </div>

    <script>
        const API_BASE = 'http://localhost:8001/api';

        let selectedFighterId = null;
        let trainingInProgress = false;

        // DOM Elements
        const fighterSelection = document.getElementById('fighterSelection');
        const profileName = document.getElementById('profileName');
        const aggression = document.getElementById('aggression');
        const positioning = document.getElementById('positioning');
        const targeting = document.getElementById('targeting');
        const riskTolerance = document.getElementById('riskTolerance');
        const endurance = document.getElementById('endurance');
        const epochs = document.getElementById('epochs');
        const trainButton = document.getElementById('trainButton');
        const resetButton = document.getElementById('resetButton');
        const trainingStatus = document.getElementById('trainingStatus');
        const statusMessage = document.getElementById('statusMessage');
        const progressFill = document.getElementById('progressFill');

        // Initialize
        loadFighters();
        setupSliderListeners();
        setupButtonListeners();

        async function loadFighters() {
            try {
                // Load GLB files dynamically from the Insert-GLBS folder
                const glbResponse = await fetch('list-glb-files.php');
                const glbFiles = await glbResponse.json();

                if (glbFiles.length === 0) {
                    fighterSelection.innerHTML = '<p style="color: #ff6600;">No GLB files found in Insert-GLBS folder!</p>';
                    return;
                }

                // Convert GLB file paths to fighter options
                fighterSelection.innerHTML = glbFiles.map((filepath, idx) => {
                    const filename = filepath.split('/').pop();
                    const fighterName = filename.replace('.glb', '');
                    return `
                        <label class="fighter-option">
                            <input type="radio" name="fighter" value="${idx + 1}"
                                   onchange="selectFighter(${idx + 1}, '${filename}')">
                            <div>
                                <div class="fighter-name">🥊 ${fighterName}</div>
                                <div class="fighter-info">GLB: ${filename}</div>
                            </div>
                        </label>
                    `;
                }).join('');
            } catch (error) {
                console.error('Failed to load fighters:', error);
                fighterSelection.innerHTML = '<p style="color: #ff6600;">Failed to load GLB files from Insert-GLBS folder</p>';
            }
        }

        function selectFighter(id, name) {
            selectedFighterId = id;
            updateTrainButton();
        }

        function setupSliderListeners() {
            const sliders = [
                { element: aggression, display: 'aggressionValue', preview: 'previewAggression' },
                { element: positioning, display: 'positioningValue', preview: 'previewPositioning' },
                { element: targeting, display: 'targetingValue', preview: 'previewTargeting' },
                { element: riskTolerance, display: 'riskToleranceValue', preview: 'previewRiskTolerance' },
                { element: endurance, display: 'enduranceValue', preview: 'previewEndurance' },
            ];

            sliders.forEach(({ element, display, preview }) => {
                element.addEventListener('input', () => {
                    const value = element.value;
                    document.getElementById(display).textContent = value;
                    document.getElementById(preview).textContent = value;
                });
            });

            profileName.addEventListener('input', () => {
                const name = profileName.value || 'UNNAMED';
                document.getElementById('previewProfile').textContent = name.toUpperCase().substring(0, 20);
            });
        }

        function setupButtonListeners() {
            trainButton.addEventListener('click', startTraining);
            resetButton.addEventListener('click', resetParameters);
        }

        function updateTrainButton() {
            const hasName = profileName.value.trim() !== '';
            const hasSelected = selectedFighterId !== null;
            trainButton.disabled = !hasName || !hasSelected || trainingInProgress;
        }

        profileName.addEventListener('input', updateTrainButton);

        function resetParameters() {
            profileName.value = '';
            aggression.value = 50;
            positioning.value = 50;
            targeting.value = 50;
            riskTolerance.value = 50;
            endurance.value = 50;
            epochs.value = 100;

            // Update displays
            document.getElementById('aggressionValue').textContent = '50';
            document.getElementById('positioningValue').textContent = '50';
            document.getElementById('targetingValue').textContent = '50';
            document.getElementById('riskToleranceValue').textContent = '50';
            document.getElementById('enduranceValue').textContent = '50';

            document.getElementById('previewAggression').textContent = '50';
            document.getElementById('previewPositioning').textContent = '50';
            document.getElementById('previewTargeting').textContent = '50';
            document.getElementById('previewRiskTolerance').textContent = '50';
            document.getElementById('previewEndurance').textContent = '50';
            document.getElementById('previewProfile').textContent = 'UNNAMED';

            updateTrainButton();
        }

        async function startTraining() {
            if (trainingInProgress || !selectedFighterId || !profileName.value.trim()) {
                return;
            }

            trainingInProgress = true;
            trainButton.disabled = true;
            trainingStatus.classList.add('active');
            statusMessage.textContent = 'Creating training job...';
            progressFill.style.width = '0%';
            progressFill.textContent = '0%';

            try {
                // Step 1: Create training job
                const jobResponse = await fetch(`${API_BASE}/training-jobs`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        fighter_id: selectedFighterId,
                        profile_name: profileName.value,
                        epochs: parseInt(epochs.value),
                        aggression: parseFloat(aggression.value),
                        positioning: parseFloat(positioning.value),
                        targeting: parseFloat(targeting.value),
                        risk_tolerance: parseFloat(riskTolerance.value),
                        endurance: parseFloat(endurance.value),
                    })
                });

                if (!jobResponse.ok) {
                    throw new Error(`Failed to create training job: ${jobResponse.status}`);
                }

                const job = await jobResponse.json();
                const jobId = job.id;

                statusMessage.innerHTML = `<span class="loading-spinner"></span>Training job created (ID: ${jobId})<br>Starting training...`;

                // Step 2: Start the training in the background (non-blocking)
                fetch(`${API_BASE}/training-jobs/${jobId}/execute`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                }).catch(err => console.log('Training started in background'));

                // Step 3: Poll training progress
                let completed = false;
                let attempts = 0;
                const maxAttempts = 300; // 5 minutes with 1s intervals (more responsive)
                let lastProgress = 0;

                while (!completed && attempts < maxAttempts) {
                    await new Promise(resolve => setTimeout(resolve, 1000)); // Poll every 1 second

                    try {
                        const statusResponse = await fetch(`${API_BASE}/training-jobs/${jobId}`);
                        if (!statusResponse.ok) {
                            throw new Error('Failed to fetch job status');
                        }

                        const updatedJob = await statusResponse.json();
                        const progress = updatedJob.progress || lastProgress;
                        lastProgress = progress;

                        progressFill.style.width = progress + '%';
                        progressFill.textContent = Math.round(progress) + '%';

                        if (updatedJob.status === 'completed') {
                            completed = true;
                            statusMessage.innerHTML = `
                                <span class="success-message">✅ Training Complete!</span><br>
                                Final Reward: ${updatedJob.final_reward?.toFixed(2) || 'N/A'}<br>
                                Win Rate: ${(updatedJob.final_win_rate * 100)?.toFixed(1) || 'N/A'}%
                            `;
                            progressFill.style.width = '100%';
                            progressFill.textContent = '100%';
                        } else if (updatedJob.status === 'failed') {
                            completed = true;
                            statusMessage.innerHTML = `
                                <span class="error-message">❌ Training Failed</span><br>
                                ${updatedJob.error_message || 'Unknown error'}
                            `;
                        } else if (progress > 0 || updatedJob.status === 'in_progress') {
                            const currentEpoch = Math.round(progress / 100 * parseInt(epochs.value));
                            statusMessage.innerHTML = `<span class="loading-spinner"></span>Training in progress (Epoch ${currentEpoch}/${parseInt(epochs.value)})...`;
                        }
                    } catch (error) {
                        console.error('Error checking job status:', error);
                    }

                    attempts++;
                }

                if (!completed) {
                    statusMessage.innerHTML = '<span class="error-message">⏱️ Training timeout - check backend status</span>';
                }

            } catch (error) {
                console.error('Training error:', error);
                statusMessage.innerHTML = `<span class="error-message">❌ Error: ${error.message}</span>`;
            } finally {
                trainingInProgress = false;
                updateTrainButton();
            }
        }
    </script>
</body>
</html>
