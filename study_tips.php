<?php
require "user_session.php";
$page_title = "Study Tips";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="styles.css?v=1">
    <title><?php echo $page_title; ?></title>
    <style>      
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
        
        h2 {
            color: var(--primary-dark);
            margin-bottom: 25px;
            text-align: center;
            font-size: 1.8rem;
        }

body {
            margin: 0;
            padding-top: 100px;
            background-color: var(--light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto 40px auto;
            background: white;
            padding: 35px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: var(--primary-dark);
            text-align: center;
            margin-bottom: 35px;
            font-size: 2.3rem;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light);
        }
        
        h2 {
            color: var(--primary);
            margin: 30px 0 20px;
            font-size: 1.7rem;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .tip-section {
            margin-bottom: 35px;
        }
        
        ul {
            padding-left: 25px;
        }
        
        li {
            margin-bottom: 12px;
            color: var(--dark);
        }
        
        .flashcard-method {
            background-color: rgba(67, 97, 238, 0.08);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid var(--primary);
        }
        
        .flashcard-method h3 {
            color: var(--primary-dark);
            margin-bottom: 15px;
            font-size: 1.4rem;
        }

        .flashcard-method ol {
            padding-left: 25px;
        }
        
        .flashcard-method li {
            margin-bottom: 10px;
        }
        
        strong {
            color: var(--primary-dark);
        }
        
        @media (max-width: 768px) {
            body {
                padding-top: 90px;
            }
            
            .container {
                padding: 25px;
                margin: 0 auto 30px auto;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

    
    <div class="container">
        <h1><i class="fas fa-lightbulb"></i> Effective Study Tips</h1>
        
        <div class="tip-section">
            <h2><i class="fas fa-clock"></i> Time Management</h2>
            <ul>
                <li>Use the Pomodoro technique (25 minutes study, 5 minutes break)</li>
                <li>Create a study schedule and stick to it</li>
                <li>Prioritize difficult subjects when your energy is highest</li>
                <li>Set specific goals for each study session</li>
            </ul>
        </div>
        
        <div class="tip-section">
            <h2><i class="fas fa-brain"></i> Learning Techniques</h2>
            <ul>
                <li>Use active recall - test yourself instead of just re-reading</li>
                <li>Try spaced repetition for better long-term retention</li>
                <li>Explain concepts in your own words or teach someone else</li>
                <li>Create mind maps or diagrams for visual learning</li>
                <li>
                    <strong>Use flashcards for effective memorization</strong>
                    <div class="flashcard-method">
                        <h3><i class="fas fa-cards-blank"></i> How to use flashcards effectively:</h3>
                        <ol>
                            <li>Write a question or term on one side and the answer on the other</li>
                            <li>Review cards regularly using spaced repetition</li>
                            <li>Shuffle the deck to avoid memorizing order</li>
                            <li>Keep track of which cards you got wrong and right</li>
                            <li>Use digital flashcard apps</li>
                            <li>Include images or diagrams when helpful</li>
                        </ol>
                    </div>
                </li>
            </ul>
        </div>
        
        <div class="tip-section">
            <h2><i class="fas fa-desktop"></i> Study Environment</h2>
            <ul>
                <li>Find a quiet, distraction-free place to study</li>
                <li>Keep your study space organized</li>
                <li>Ensure good lighting to reduce eye strain</li>
                <li>Have all necessary materials ready before starting</li>
            </ul>
        </div>
        
        <div class="tip-section">
            <h2><i class="fas fa-heartbeat"></i> Health & Wellness</h2>
            <ul>
                <li>Get enough sleep - it's crucial for memory consolidation</li>
                <li>Stay hydrated and eat brain-boosting foods</li>
                <li>Take regular breaks to avoid burnout</li>
                <li>Include physical activity in your routine</li>
            </ul>
        </div>
        
        <div class="tip-section">
            <h2><i class="fas fa-edit"></i> Exam Preparation</h2>
            <ul>
                <li>Review past exams or practice questions</li>
                <li>Study in the same format as the test (e.g., practice essays for essay exams)</li>
                <li>Create summary sheets for quick review</li>
                <li>Get a good night's sleep before the exam</li>
            </ul>
        </div>
    </div>
</body>
</html>