<?php
require "user_session.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work/Break Timer</title>
    <link rel="stylesheet" type="text/css" href="styles.css?v=1">
    <style>     
    
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: var(--light);
            padding: 0;
            margin: 0;
            padding-top: 80px;

        }
        
        .content {
            max-width: 800px;
            margin: 0 auto 30px auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        
        .timer-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
            width: 100%;
            max-width: 350px;
        }
        
        .timer-display {
            font-size: 3.5rem;
            font-weight: bold;
            margin: 20px 0;
            color: var(--dark);
            font-family: 'Courier New', monospace;
        }
        
        .mode-display {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .work { color: var(--primary); }
        .break { color: var(--success); }
        
        .controls {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 25px 0;
        }
        
        button {
            padding: 12px 20px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: bold;
        }
        
        #startBtn { 
            background: var(--success); 
            color: white; 
        }
        
        #pauseBtn { 
            background: var(--warning); 
            color: white; 
        }
        
        #resetBtn { 
            background: var(--danger); 
            color: white; 
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        button:disabled {
            background: #cccccc !important;
            color: #666666 !important;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }
        
        .settings {
            margin-top: 25px;
            display: flex;
            justify-content: space-around;
            padding: 15px 0;
            border-top: 1px solid #eee;
        }
        
        .setting-group {
            text-align: center;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: var(--dark);
            font-weight: 500;
        }
        
        input {
            width: 60px;
            padding: 8px 10px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: bold;
        }
        
        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
        }
        
        @media (max-width: 400px) {
            .timer-display { font-size: 2.8rem; }
            .timer-container { padding: 20px; }
        }
    </style>
</head>
<body>

    <div class="timer-container">
        <div id="modeDisplay" class="mode-display work">Work Time</div>
        <div class="timer-display" id="display">25:00</div>
        
        <div class="controls">
            <button id="startBtn"><i class="fas fa-play"></i> Start</button>
            <button id="pauseBtn" disabled><i class="fas fa-pause"></i> Pause</button>
            <button id="resetBtn" disabled><i class="fas fa-redo"></i> Reset</button>
        </div>
        
        <div class="settings">
            <div class="setting-group">
                <label for="workTime">Work (min)</label>
                <input type="number" id="workTime" min="1" value="25">
            </div>
            <div class="setting-group">
                <label for="breakTime">Break (min)</label>
                <input type="number" id="breakTime" min="1" value="5">
            </div>
        </div>

    </div>

    <script>
        const startBtn = document.getElementById('startBtn');
        const pauseBtn = document.getElementById('pauseBtn');
        const resetBtn = document.getElementById('resetBtn');
        const display = document.getElementById('display');
        const modeDisplay = document.getElementById('modeDisplay');
        const workTimeInput = document.getElementById('workTime');
        const breakTimeInput = document.getElementById('breakTime');
        
        let timer;
        let timeLeft;
        let isRunning = false;
        let isPaused = false;
        let isWorkTime = true;
        
        function updateDisplay(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            display.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }
        
        function startTimer() {
            if (!isRunning && !isPaused) {
                // restart
                const minutes = isWorkTime ? parseInt(workTimeInput.value) : parseInt(breakTimeInput.value);
                timeLeft = minutes * 60;
                workTimeInput.disabled = true;
                breakTimeInput.disabled = true;
            } else if (isPaused) {
                // resuming from pause
                isPaused = false;
            }
            
            isRunning = true;
            startBtn.disabled = true;
            pauseBtn.disabled = false;
            resetBtn.disabled = false;
            
            timer = setInterval(() => {
                timeLeft--;
                updateDisplay(timeLeft);
                
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    isRunning = false;
                    
                    // switching from work to break
                    isWorkTime = !isWorkTime;
                    if (isWorkTime) {
                        modeDisplay.textContent = "Work Time";
                        modeDisplay.className = "mode-display work";
                    } else {
                        modeDisplay.textContent = "Break Time";
                        modeDisplay.className = "mode-display break";
                    }

                    startTimer();
                }
            }, 1000);
        }
        
        function pauseTimer() {
            if (isRunning) {
                clearInterval(timer);
                isRunning = false;
                isPaused = true;
                pauseBtn.innerHTML = '<i class="fas fa-play"></i> Resume';
            } else if (isPaused) {
                pauseBtn.innerHTML = '<i class="fas fa-pause"></i> Pause';
                startTimer();
            }
        }
        
        function resetTimer() {
            clearInterval(timer);
            isRunning = false;
            isPaused = false;
            isWorkTime = true;
            modeDisplay.textContent = "Work Time";
            modeDisplay.className = "mode-display work";
            timeLeft = parseInt(workTimeInput.value) * 60;
            updateDisplay(timeLeft);
            startBtn.disabled = false;
            pauseBtn.disabled = true;
            resetBtn.disabled = true;
            pauseBtn.innerHTML = '<i class="fas fa-pause"></i> Pause';
            workTimeInput.disabled = false;
            breakTimeInput.disabled = false;
        }
        
        resetTimer();
        
        startBtn.addEventListener('click', startTimer);
        pauseBtn.addEventListener('click', pauseTimer);
        resetBtn.addEventListener('click', resetTimer);
        
        workTimeInput.addEventListener('change', function() {
            if (this.value < 1) this.value = 1;
            if (!isRunning) resetTimer();
        });
        
        breakTimeInput.addEventListener('change', function() {
            if (this.value < 1) this.value = 1;
        });
    </script>
</body>
</html>