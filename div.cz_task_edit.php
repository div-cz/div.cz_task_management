<?php

require_once 'div.cz/config.php';

// Include file with allowed IP addresses
include "ip_addresses.php";

// Check if the connection is successful
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Check if user is allowed based on IP address
$user_ip = $_SERVER['REMOTE_ADDR'];
if (!in_array($user_ip, $allowed_ip_addresses)) {
    die("<div style='font-size:120%;width:300px;border:1px solid black;'>Přístup zakázán, IP adresa ".$_SERVER['REMOTE_ADDR']." není povolena. Napiš Martinovi tu IP.</div>");
}


if (isset($_POST['user']) && !in_array($_POST['user'], $users)) {
    die("<div style='font-size:120%;width:300px;border:1px solid black;'>Přístup zakázán, uživatelské jméno není v seznamu povolených.</div>");
}


// Retrieve task ID from request
$task_id = $_POST['task_id'] ?? ($_GET['id'] ?? die('Číslo úkolu je nezbytné'));


// DELETE
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        // Delete action handling
        $stmt = $conn->prepare("DELETE FROM AATasks WHERE ID = ?");
        $stmt->bind_param("i", $task_id);
        if ($stmt->execute()) {
            header("Location: index.php"); // Redirect if deletion is successful
            exit();
        } else {
            die("Error deleting task: " . $conn->error);
        }
    } else {}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if user is allowed to edit task

    // Your code to handle form submission...

        // Retrieve task ID and other variables from form
        $task_id = $_POST['task_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $comments = $_POST['Comments'];
        $assigned = $_POST['Assigned'];
        $priority = $_POST['priority'];
        $status = $_POST['status'];
        $category = $_POST['category'];



$parentid = !empty($_POST['ParentID']) ? $_POST['ParentID'] : NULL;

$due_date = !empty($_POST['DueDate']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['DueDate']) ? $_POST['DueDate'] : '2040-04-01';




    // Prepare and execute SQL query to update task details
// Příprava SQL dotazu
$stmt = $conn->prepare("UPDATE AATasks SET Title = ?, Description = ?, Comments = ?, Assigned = ?, Priority = ?, Status = ?, Category = ?, ParentID = ?, DueDate = ?, Updated = NOW()  WHERE ID = ?");
$stmt->bind_param("sssssssssi", $title, $description, $comments, $assigned, $priority, $status, $category, $parentid, $due_date, $task_id);

// Vykonání dotazu
if ($stmt->execute()) {
    header("Location: div.cz_task_view.php?id=$task_id");  // Přesměrování pokud je aktualizace úspěšná
    exit();
} else {
    echo "Error updating task: " . $conn->error;  // Zobrazení chyby, pokud aktualizace selže
}

    // Close the prepared statement
    $stmt->close();
}

// Retrieve task ID from URL parameter
$task_id = isset($_GET['id']) ? $_GET['id'] : die('Task ID is required.');

// Prepare and execute SQL query to fetch task details
$stmt = $conn->prepare("SELECT * FROM AATasks WHERE ID = ?");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo "No task found with ID: $task_id";
    $conn->close();
    exit;
}

// Close the connection

?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editace úkolu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-3">Editace úkolu</h1>
        <form action="" method="post">
            <input type="hidden" name="task_id" value="<?php echo $row['ID']; ?>">
            <div class="mb-3">
                <label for="title" class="form-label">Název úkolu:</label>
                <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($row['Title']); ?>" required>
            </div>


            <div class="mb-3">
                <label for="Assigned" class="form-label">Přiřadit komu:</label>
                <input type="text" name="Assigned" id="Assigned" class="form-control" value="<?php echo htmlspecialchars($row['Assigned']); ?>">
            </div>
            

            <div class="mb-3">
                <label for="description" class="form-label">Popis úkolu:</label>
                <textarea name="description" id="description" class="form-control" rows="4" cols="50"><?php echo htmlspecialchars($row['Description']); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="Comments" class="form-label">Komentáře:</label>
                <textarea name="Comments" id="Comments" class="form-control" rows="3"><?php echo htmlspecialchars($row['Comments']); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="priority" class="form-label">Priorita:</label>
                <select name="priority" id="priority" class="form-select">
                    <option value="Vysoká" <?php if ($row['Priority'] == 'Vysoká') echo 'selected'; ?>>Vysoká</option>
                    <option value="Střední" <?php if ($row['Priority'] == 'Střední') echo 'selected'; ?>>Střední</option>
                    <option value="Nízká" <?php if ($row['Priority'] == 'Nízká') echo 'selected'; ?>>Nízká</option>
                </select>
            </div>



            <div class="mb-3">
                <label for="category" class="form-label">Kategorie:</label>
                <select name="category" id="category" class="form-select">
                    <option value="Frontend" <?php if ($row['Category'] == 'Frontend') echo 'selected'; ?>>Frontend</option>
                    <option value="Backend" <?php if ($row['Category'] == 'Backend') echo 'selected'; ?>>Backend</option>
                    <option value="Testování" <?php if ($row['Category'] == 'Testování') echo 'selected'; ?>>Testování</option>
                    <option value="Databáze" <?php if ($row['Category'] == 'Databáze') echo 'selected'; ?>>Databáze</option>
                    <option value="Server" <?php if ($row['Category'] == 'Server') echo 'selected'; ?>>Server</option>
                    <option value="iOS" <?php if ($row['Category'] == 'iOS') echo 'selected'; ?>>iOS</option>
                    <option value="Android" <?php if ($row['Category'] == 'iOS') echo 'selected'; ?>>Android</option>
                </select>
            </div>
               
<div class="mb-3">
    <label for="status" class="form-label">Status:</label>
    <select name="status" id="status" class="form-select">
        <option value="Ke zpracování">Ke zpracování</option>
        <option value="Probíhá">Probíhá</option>
        <option value="Hotovo">Hotovo</option>
    </select>
</div>



            
            <div class="mb-3">
                <label for="ParentID" class="form-label">Rodičovský úkol:</label>
                <select name="ParentID" id="ParentID" class="form-select">
                    <option value="">Žádný</option>
                    <?php
                    // Předpokládáme, že $tasks obsahuje všechny dostupné úkoly
// Příklad dotazu pro načtení úkolů
$parentTasks = $conn->query("SELECT ID, Title FROM AATasks WHERE Status = 'Ke zpracování' OR Status = 'Probíhá' ORDER BY Title");

// Kontrola, jestli dotaz vrátil nějaké řádky
if ($parentTasks->num_rows > 0) {
    while($task = $parentTasks->fetch_assoc()) {
        echo '<option value="' . $task['ID'] . '"' . ($task['ID'] == $row['ParentID'] ? ' selected' : '') . '>' . htmlspecialchars($task['Title']) . '</option>';
    }
} else {
    echo '<option value="">Žádné tasky</option>';
}
                    ?>
                </select>
            </div>


            <div class="mb-3">
                <label for="DueDate" class="form-label">Termín dokončení:</label>
                <input type="date" name="DueDate" id="DueDate" class="form-control" value="<?php echo $row['DueDate'] ?>">
            </div>

            

                


            <input type="submit" class="btn btn-primary" value="Uložit změny">
            
    <button type="submit" name="action" value="delete" class="btn btn-danger">Smazat úkol</button>

        </form>
        <p><a href="div.cz_task_view.php?id=<?php echo $row['ID']; ?>" class="btn btn-secondary mt-3">Zobrazit úkol</a>
         <a href="index.php" class="btn btn-secondary mt-3">Zpět na hlavní stránku</a></p>
    </div>
</body>
</html>
<?php $conn->close(); ?>
