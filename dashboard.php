<?php
require "user_session.php";

// Get today's date
$today = date("Y-m-d");

// Fetch today's tasks
$query = "SELECT * FROM tasks WHERE user_id = ? AND due_date = ? AND status = 'pending' ORDER BY due_date ASC";
$stmt = $db->prepare($query);
$stmt->bind_param("is", $_SESSION['user_id'], $today);
$stmt->execute();
$today_tasks_result = $stmt->get_result();
$today_tasks = $today_tasks_result->fetch_all(MYSQLI_ASSOC);

// Fetch upcoming tasks
$query = "SELECT * FROM tasks WHERE user_id = ? AND due_date > ? AND status = 'pending' ORDER BY due_date ASC";
$stmt = $db->prepare($query);
$stmt->bind_param("is", $_SESSION['user_id'], $today);
$stmt->execute();
$upcoming_tasks_result = $stmt->get_result();
$upcoming_tasks = $upcoming_tasks_result->fetch_all(MYSQLI_ASSOC);

// Fetch completed tasks
$query = "SELECT * FROM tasks WHERE user_id = ? AND status = 'completed' ORDER BY due_date DESC";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$completed_tasks_result = $stmt->get_result();
$completed_tasks = $completed_tasks_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light);
            padding: 0;
            margin: 0;
            padding-top: 100px;
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
        
        .task-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .task-section:last-child {
            border-bottom: none;
        }
        
        h3 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        h3 i {
            color: var(--primary);
        }
        
        .task-list {
            padding-left: 0;
            list-style-type: none;
        }
        
        .task-list li {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 8px;
            background-color: #f8f9fa;
            border-radius: 6px;
            transition: all 0.2s;
        }
        
        .task-list li:hover {
            background-color: #f0f4ff;
        }
        
        .task-checkbox {
            margin-right: 15px;
            transform: scale(1.2);
            cursor: pointer;
        }
        
        .task-name {
            flex: 1;
            color: var(--dark);
        }
        
        .completed-task {
            text-decoration: line-through;
            color: #888;
        }
        
        .task-list small {
            color: #666;
            font-size: 0.85rem;
            margin-left: 10px;
        }
        
        .empty-message {
            color: #666;
            font-style: italic;
            padding: 15px;
            text-align: center;
            background-color: #f8f9fa;
            border-radius: 6px;
        }
        
        /* Quick Access Section */
        .quick-access {
            margin: 40px 0 20px;
        }
        
        .icon-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .icon-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: white;
            padding: 20px;
            border-radius: 8px;
            color: var(--primary);
            text-decoration: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: all 0.2s;
            border: 1px solid #e8ebf0;
            aspect-ratio: 1/1;
        }
        
        .icon-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.1);
            background-color: #f8f9ff;
        }
        
        .icon-box i {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .icon-box span {
            font-size: 0.85rem;
            color: var(--dark);
            text-align: center;
        }
        
        /* Add Task Button */
        .add-task-container {
            margin-top: 30px;
            text-align: center;
        }
        
        .add-task-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary);
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .add-task-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .add-task-btn i {
            margin-right: 8px;
        }
        
        @media (max-width: 768px) {
            body {
                padding-top: 80px;
            }
            
            .content {
                padding: 20px;

            }
            
            .icon-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .add-task-btn {
                padding: 10px 20px;
            }
        }
    </style>
</head>
<body>
    
    <div class="content">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>


        <div class="task-section">
            <h3><i class="fas fa-calendar-day"></i> Tasks for Today</h3>
            <?php if (!empty($today_tasks)): ?>
                <ul class="task-list" id="today-tasks">
                    <?php foreach ($today_tasks as $task): ?>
                        <li data-task-id="<?php echo $task['id']; ?>">
                            <input type="checkbox" class="task-checkbox" 
                                   data-task-id="<?php echo $task['id']; ?>"
                                   <?php echo $task['status'] == 'completed' ? 'checked' : ''; ?>>
                            <span class="task-name <?php echo $task['status'] == 'completed' ? 'completed-task' : ''; ?>">
                                <?php echo htmlspecialchars($task['task_name']); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="empty-message">No tasks for today!</p>
            <?php endif; ?>
        </div>

        <!-- Upcoming Tasks -->
        <div class="task-section">
            <h3><i class="fas fa-calendar-week"></i> Upcoming Tasks</h3>
            <?php if (!empty($upcoming_tasks)): ?>
                <ul class="task-list" id="upcoming-tasks">
                    <?php foreach ($upcoming_tasks as $task): ?>
                        <li data-task-id="<?php echo $task['id']; ?>">
                            <input type="checkbox" class="task-checkbox" 
                                   data-task-id="<?php echo $task['id']; ?>"
                                   <?php echo $task['status'] == 'completed' ? 'checked' : ''; ?>>
                            <span class="task-name <?php echo $task['status'] == 'completed' ? 'completed-task' : ''; ?>">
                                <?php echo htmlspecialchars($task['task_name']); ?>
                                <small>(Due: <?php echo date('M j', strtotime($task['due_date'])); ?>)</small>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="empty-message">No upcoming tasks!</p>
            <?php endif; ?>
        </div>


        <div class="task-section">
            <h3><i class="fas fa-check-circle"></i> Completed Tasks</h3>
            <?php if (!empty($completed_tasks)): ?>
                <ul class="task-list" id="completed-tasks">
                    <?php foreach ($completed_tasks as $task): ?>
                        <li data-task-id="<?php echo $task['id']; ?>">
                            <input type="checkbox" class="task-checkbox" 
                                   data-task-id="<?php echo $task['id']; ?>" checked>
                            <span class="task-name completed-task">
                                <?php echo htmlspecialchars($task['task_name']); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="empty-message">No completed tasks yet!</p>
            <?php endif; ?>
        </div>

            <div class="add-task-container">
                <a href="tasks.php" class="add-task-btn">
                    <i class="fas fa-plus"></i> Add New Task
                </a>
            </div>

        <div class="quick-access">
            <h3><i class="fas fa-bolt"></i> Quick Tools</h3>
            <div class="icon-grid">
                <a href="tasks.php" class="icon-box">
                    <i class="fas fa-tasks"></i>
                    <span>Task Manager</span>
                </a>
                <a href="pomodoro.php" class="icon-box">
                    <i class="fas fa-hourglass"></i>
                    <span>Study Timer</span>
                </a>
                <a href="view_subjects.php" class="icon-box">
                    <i class="fas fa-book-open"></i>
                    <span>Flashcards</span>
                </a>
                <a href="study_tips.php" class="icon-box">
                    <i class="fas fa-lightbulb"></i>
                    <span>Study Tips</span>
                </a>

            </div>
        </div>

        <script>
    document.addEventListener('DOMContentLoaded', function() {
        // handle task status updates
        async function updateTaskStatus(taskId, isCompleted) {
            const taskItem = document.querySelector(`li[data-task-id="${taskId}"]`);
            if (!taskItem) return;
            
            // loading state
            taskItem.classList.add('updating');
            
            try {
                const response = await fetch(`update_task_status.php?task_id=${taskId}&status=${isCompleted ? 'completed' : 'pending'}`);
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                const result = await response.text();
                if (result.includes('Failed')) {
                    throw new Error(result);
                }
                
                // Update UI immediately // issues with updating initially 
                const taskName = taskItem.querySelector('.task-name');
                const checkbox = taskItem.querySelector('.task-checkbox');
                
                taskName.classList.toggle('completed-task', isCompleted);
                

                setTimeout(() => {
                    moveTaskToSection(taskItem, isCompleted);
                    updateEmptyMessages();
                    taskItem.classList.remove('updating');
                }, 300);
                
            } catch (error) {
                console.error('Error:', error);
                // this will revert the checkbox if there was an error
                const checkbox = taskItem.querySelector('.task-checkbox');
                if (checkbox) {
                    checkbox.checked = !isCompleted;
                }
                taskItem.classList.remove('updating');
            }
        }
        
        // to move task between sections
        function moveTaskToSection(taskItem, isCompleted) {
            const taskClone = taskItem.cloneNode(true);
            taskItem.remove();
            
            if (isCompleted) {
                // add to completed tasks at the top
                const completedList = document.getElementById('completed-tasks');
                if (completedList) {
                    completedList.prepend(taskClone);
                }
            } else {
                // determine if it's today's task or upcoming
                const dueText = taskClone.querySelector('small');
                const todayList = document.getElementById('today-tasks');
                const upcomingList = document.getElementById('upcoming-tasks');
                
                if (dueText && dueText.textContent.includes('Due:')) {
                    if (upcomingList) {
                        upcomingList.appendChild(taskClone);
                    }
                } else {
                    if (todayList) {
                        todayList.appendChild(taskClone);
                    }
                }
            }
       
            const newCheckbox = taskClone.querySelector('.task-checkbox');
            if (newCheckbox) {
                newCheckbox.addEventListener('change', function() {
                    updateTaskStatus(this.dataset.taskId, this.checked);
                });
            }
        }
        
        // Function to update empty messages
        function updateEmptyMessages() {
            const sections = [
                { id: 'today-tasks', message: 'No tasks for today!' },
                { id: 'upcoming-tasks', message: 'No upcoming tasks!' },
                { id: 'completed-tasks', message: 'No completed tasks yet!' }
            ];
            
            sections.forEach(section => {
                const list = document.getElementById(section.id);
                if (!list) return;
                
                const parent = list.parentNode;
                let emptyMsg = parent.querySelector('.empty-message');
                
                if (list.children.length === 0) {
                    if (!emptyMsg) {
                        emptyMsg = document.createElement('p');
                        emptyMsg.className = 'empty-message';
                        emptyMsg.textContent = section.message;
                        parent.insertBefore(emptyMsg, list.nextSibling);
                    }
                } else if (emptyMsg) {
                    emptyMsg.remove();
                }
            });
        }
        
        // attaches event listeners to all checkboxes
        document.querySelectorAll('.task-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateTaskStatus(this.dataset.taskId, this.checked);
            });
        });
    });
    </script>
</body>
</html>