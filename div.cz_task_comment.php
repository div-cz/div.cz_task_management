<?php



require_once 'div.cz/config.php';


include "div.cz/ip_addresses.php";




// Check if user's IP address is allowed
$user_ip = $_SERVER['REMOTE_ADDR'];
if (!in_array($user_ip, $allowed_ip_addresses)) {
    die("Tvoje IP adresa ($user_ip) není oprávněna přidávat komentáře. Prosím, kontaktujte Martina nebo Ionno.");
}


// Check if the connection is successful
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Retrieve task ID from URL parameter
$task_id = isset($_GET['id']) ? $_GET['id'] : die('Task ID is required.');

// Retrieve task details including existing comments
$stmt_task = $conn->prepare("SELECT Title, Category, Assigned, Comments FROM AATasks WHERE ID = ?");
$stmt_task->bind_param("i", $task_id);
$stmt_task->execute();
$result_task = $stmt_task->get_result();
$row_task = $result_task->fetch_assoc();

if (!$row_task) {
    echo "No task found with ID: $task_id";
    $conn->close();
    exit;
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve existing comment
    $existing_comment = $row_task['Comments'];

    // Append new comment
    $new_comment = $_POST['Comments'];
    if (!empty($existing_comment)) {
        $new_comment = $existing_comment . "\n 🃏 \n" . $new_comment;
    }

    // Prepare and execute SQL query to update task with new comment and IP address
    $stmt_update_comment = $conn->prepare("UPDATE AATasks SET Comments = ?, Updated = NOW() WHERE ID = ?");
    $stmt_update_comment->bind_param("si", $new_comment, $task_id);

    // Execute the update query
    if ($stmt_update_comment->execute()) {
        // Redirect to task view page
        header("Location: task_view.php?id=$task_id");
        exit();
    } else {
        echo "Error adding comment: " . $conn->error;
    }

    // Close the prepared statement
    $stmt_update_comment->close();
}

// Close the prepared statement
$stmt_task->close();
// Close the connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přidat komentář k úkolu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-3">Přidat komentář k úkolu</h1>
        <div class="mb-3">
            <label for="title" class="form-label">Název úkolu:</label>
            <input type="text" id="title" class="form-control" value="<?php echo htmlspecialchars($row_task['Title']); ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="category" class="form-label">Kategorie:</label>
            <input type="text" id="category" class="form-control" value="<?php echo htmlspecialchars($row_task['Category']); ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="assigned_to" class="form-label">Přiřazeno komu:</label>
            <input type="text" id="assigned_to" class="form-control" value="<?php echo htmlspecialchars($row_task['Assigned']); ?>" readonly>
        </div>
        <form action="" method="post">
            <div class="mb-3">
                <label for="Comments" class="form-label">Komentář:</label>
                <textarea name="Comments" id="Comments" class="form-control" rows="4" required></textarea>
            </div>
            <input type="submit" class="btn btn-primary" value="Přidat komentář">
        </form>
        <p><a href="task_view.php?id=<?php echo $task_id; ?>" class="btn btn-secondary mt-3">Zpět na detail úkolu</a></p>
    </div>
</body>
</html>
