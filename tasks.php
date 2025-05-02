<?php
require 'user_session.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   $task_name = $_POST['task_name'];
   $description = $_POST['description'];
   $due_date = $_POST['due_date'];
  
   if (isset($_POST['task_id']) && !empty($_POST['task_id'])) {
       $task_id = $_POST['task_id'];
       $sql = "UPDATE tasks SET task_name=?, description=?, due_date=?, status='pending' WHERE id=? AND user_id=?";
       $stmt = $conn->prepare($sql);
       $stmt->bind_param("sssii", $task_name, $description, $due_date, $task_id, $user_id);
   } else {
       $sql = "INSERT INTO tasks (user_id, task_name, description, due_date, status) VALUES (?, ?, ?, ?, 'pending')";
       $stmt = $conn->prepare($sql);
       $stmt->bind_param("isss", $user_id, $task_name, $description, $due_date);
   }

   if ($stmt->execute()) {
       header("Location: dashboard.php");
       exit();
   } else {
       die("Error: " . $stmt->error);
   }
}

if (isset($_GET['update_status'])) {
    $task_id = $_GET['task_id'];
    $status = $_GET['status'];
    
    $sql = "UPDATE tasks SET status=? WHERE id=? AND user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $status, $task_id, $user_id);
    $stmt->execute();
    exit();
}

if (isset($_GET['delete'])) {
   $task_id = $_GET['delete'];
   $sql = "DELETE FROM tasks WHERE id=? AND user_id=?";
   $stmt = $conn->prepare($sql);
   $stmt->bind_param("ii", $task_id, $user_id);
   $stmt->execute();
   header("Location: dashboard.php");
   exit();
}

if (isset($_GET['complete'])) {
   $task_id = $_GET['complete'];
   $sql = "UPDATE tasks SET status='completed' WHERE id=? AND user_id=?";
   $stmt = $conn->prepare($sql);
   $stmt->bind_param("ii", $task_id, $user_id);
   $stmt->execute();
   header("Location: dashboard.php");
   exit();
}

$sql = "SELECT * FROM tasks WHERE user_id=? ORDER BY due_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>
    <link rel="stylesheet" type="text/css" href="styles.css?v=1">
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
            margin-bottom: 20px;
            text-align: center;
        }
        
        form {
            display: grid;
            gap: 15px;
        }
        
        input[type="text"],
        input[type="date"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        button[type="submit"] {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.2s;
            width: auto;
            display: inline-block;
            margin: 0 auto;
        }
        
        button[type="submit"]:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .task-planner {
            max-width: 800px;
            margin: 0 auto;
            display: grid;
            gap: 15px;
            padding: 0 20px 40px 20px;
        }
        
        .task-card {
            background: white;
            padding: var(--space-md);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow-sm);
            display: flex;
            flex-direction: column;
            gap: var(--space-md);
            transition: var(--transition);
            border-left: 4px solid var(--primary);
        }

        .task-card:hover {
            box-shadow: var(--box-shadow);
            transform: translateY(-2px);
        }

        .completed-task {
            opacity: 0.8;
            background-color: var(--light);
            border-left-color: var(--success);
        }

        .completed-task h4 {
            text-decoration: line-through;
            color: var(--gray);
        }

        .task-content {
            flex: 1;
        }

        .task-content h4 {
            margin-bottom: var(--space-sm);
            color: var(--dark);
            font-size: var(--font-size-lg);
        }

        .task-content p {
            margin-bottom: var(--space-xs);
            color: var(--gray);
            font-size: var(--font-size-sm);
        }

        .task-actions {
            display: flex;
            gap: var(--space-sm);
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .task-checkbox {
            transform: scale(1.3);
            margin-right: var(--space-xs);
            cursor: pointer;
        }


        .task-btn {
            padding: var(--space-sm) var(--space-md);
            border: none;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            font-size: var(--font-size-sm);
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: var(--space-xs);
            color: white;
            text-decoration: none;
            text-align: center;
        }

        .btn-edit {
            background-color: var(--primary);
        }

        .btn-edit:hover {
            background-color: var(--primary-dark);
        }

        .btn-delete {
            background-color: var(--danger);
        }

        .btn-delete:hover {
            background-color: var(--danger-dark);
        }


        @media (max-width: 768px) {
            body {
                padding-top: 80px;
            }
            
            .task-actions {
                justify-content: space-between;
                width: 100%;
            }

            .task-btn {
                flex: 1;
                justify-content: center;
                padding: var(--space-sm);
                font-size: var(--font-size-xs);
            }
        }

        @media (max-width: 480px) {
            .task-actions {
                flex-direction: column;
                gap: var(--space-sm);
            }

            .task-btn {
                width: 100%;
            }

            .task-checkbox {
                margin-right: 0;
                margin-bottom: var(--space-sm);
                align-self: flex-start;
            }
        }
    </style>
</head>
<body>
    
    <div class="content">
        <h2><i class="fas fa-tasks"></i> Task Manager</h2>
        <form method="POST">
            <input type="hidden" name="task_id" id="task_id">
            <input type="text" name="task_name" id="task_name" required placeholder="Task Name">
            <textarea name="description" id="description" placeholder="Task Description"></textarea>
            <input type="date" name="due_date" id="due_date" required>
            <button type="submit"><i class="fas fa-save"></i> Save Task</button>
        </form>
    </div>
    
    <div class="task-planner">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="task-card <?php echo $row['status'] == 'completed' ? 'completed-task' : ''; ?>">
                <div class="task-content">
                    <h4><?php echo htmlspecialchars($row['task_name']); ?></h4>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                    <p><strong>Due Date:</strong> <?php echo date('Y-m-d', strtotime($row['due_date'])); ?></p>
                </div>
                <div class="task-actions">
                    <input type="checkbox" class="task-checkbox" 
                           data-task-id="<?php echo $row['id']; ?>"
                           <?php echo $row['status'] == 'completed' ? 'checked' : ''; ?>>
                    <a href="#" onclick="editTask(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['task_name']); ?>', '<?php echo htmlspecialchars($row['description']); ?>', '<?php echo $row['due_date']; ?>')" 
                       class="task-btn btn-edit">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?')" 
                       class="task-btn btn-delete">
                        <i class="fas fa-trash-alt"></i> Delete
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
        // handle checkbox status changes
        document.querySelectorAll('.task-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const taskId = this.dataset.taskId;
                const status = this.checked ? 'completed' : 'pending';
                const taskCard = this.closest('.task-card');
                
                fetch(`tasks.php?update_status=1&task_id=${taskId}&status=${status}`)
                    .then(response => {
                        if (response.ok) {
                            taskCard.classList.toggle('completed-task', this.checked);
                        }
                    });
            });
        });

        // edit task function
        function editTask(id, name, description, due_date) {
            document.getElementById('task_id').value = id;
            document.getElementById('task_name').value = name;
            document.getElementById('description').value = description;
            document.getElementById('due_date').value = due_date.split(' ')[0]; // Remove time if present
            document.querySelector('.content').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>