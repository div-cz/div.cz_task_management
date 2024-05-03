<?php

require_once 'div.cz/config.php';


/*
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Logika zpracování
    echo $_POST['Title'];
    //header("Location: index.php?success=1");
    exit();
} else {
    echo "Neplatná metoda nebo chybějící data.";
}
*/



// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['Title'], $_POST['Description'], $_POST['Priority'], $_POST['Category'], $_POST['DueDate'])) {



    $due_date = new DateTime($_POST['DueDate']);
    $today = new DateTime(); // Aktuální datum bez času

    if ($due_date < $today) {
        $date_error = "Datum dokončení nemůže být v minulosti.";
    } else {
    
    
    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO AATasks (Title, Description, Status, Priority, Category, Assigned, Creator, DueDate, Updated, Created, IPaddress, ParentID) VALUES (?, ?, 'Ke zpracování', ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?)");
    $stmt->bind_param("sssssssss", $title, $description, $priority, $category, $assigned, $creator, $due_date, $_SERVER['REMOTE_ADDR'], $parent_id);

    // Set parameters and execute
    $title = $_POST['Title'];
    $description = !empty($_POST['Description']) ? $_POST['Description'] : '-';
    $priority = !empty($_POST['Priority']) ? $_POST['Priority'] : '';
    $category = !empty($_POST['Category']) ? $_POST['Category'] : '';
    $due_date = !empty($_POST['DueDate']) ? $_POST['DueDate'] : '2050-05-05';
$parent_id = isset($_POST['ParentID']) && !empty($_POST['ParentID']) ? $_POST['ParentID'] : NULL;
    $assigned = $_POST['Assigned'];
    $creator = $_POST['Creator'];



    // Insert into database
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        // Redirect to the main page
        header("Location: index.php?success=1");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Task</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1>Přidat nový úkol</h1>
        <?php if (isset($date_error)): ?>
            <div class="alert alert-danger mt-2"><?= $date_error ?></div>
        <?php endif; ?>

<form action="div.cz_task_add.php" method="post" class="needs-validation" novalidate>
    <div class="form-group">
        <label>Title</label>
        <input type="text" class="form-control" name="Title" value="<?= isset($_POST['Title']) ? htmlspecialchars($_POST['Title']) : '' ?>" required>
    </div>

    <div class="form-group">
        <label>Popis</label>
        <textarea class="form-control" name="Description" required><?= isset($_POST['Description']) ? htmlspecialchars($_POST['Description']) : '' ?></textarea>
    </div>


            <div class="form-group">
                <label>Status</label>
                <select class="form-control" name="Status" required>
                    <option value="Ke zpracování">Ke zpracování</option>
                    <option value="Probíhá">Probíhá</option>
                    <option value="Hotovo">Hotovo</option>
                    <option value="Nice to have">Nice to have</option>
                </select>
            </div>


    <div class="form-group">
        <label>Priorita</label>
        <select class="form-control" name="Priority" required>
            <option value="Nízká" <?= (isset($_POST['Priority']) && $_POST['Priority'] == 'Nízká') ? 'selected' : '' ?>>Nízká</option>
            <option value="Střední" <?= (isset($_POST['Priority']) && $_POST['Priority'] == 'Střední') ? 'selected' : '' ?>>Střední</option>
            <option value="Vysoká" <?= (isset($_POST['Priority']) && $_POST['Priority'] == 'Vysoká') ? 'selected' : '' ?>>Vysoká</option>
        </select>
    </div>

    <div class="form-group">
        <label>Kategorie</label>
        <select class="form-control" name="Category" required>
            <option value="Frontend" <?= (isset($_POST['Category']) && $_POST['Category'] == 'Frontend') ? 'selected' : '' ?>">Frontend</option>
            <option value="Backend" <?= (isset($_POST['Category']) && $_POST['Category'] == 'Backend') ? 'selected' : '' ?>">Backend</option>
            <option value="Testování" <?= (isset($_POST['Category']) && $_POST['Category'] == 'Testování') ? 'selected' : '' ?>">Testování</option>
            <option value="Databáze" <?= (isset($_POST['Category']) && $_POST['Category'] == 'Databáze') ? 'selected' : '' ?>">Databáze</option>
            <option value="Server" <?= (isset($_POST['Category']) && $_POST['Category'] == 'Server') ? 'selected' : '' ?>">Server</option>
            <option value="iOS" <?= (isset($_POST['Category']) && $_POST['Category'] == 'iOS') ? 'selected' : '' ?>">iOS</option>
            <option value="Android" <?= (isset($_POST['Category']) && $_POST['Category'] == 'Android') ? 'selected' : '' ?>">Android</option>

                </select>
            </div>


<div class="form-group">
    <label for="ParentID">Rodičovský úkol</label>
    <select class="form-control" name="ParentID" id="ParentID">
        <option value="">Žádný</option>
        <?php
        // Připojení k databázi by mělo být aktivní
        $sql = "SELECT ID, Title FROM AATasks WHERE (Status = 'Ke zpracování' OR Status = 'Probíhá') AND ParentID IS NULL ORDER BY Title";

        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<option value="' . $row['ID'] . '">' . htmlspecialchars($row['Title']) . '</option>';
            }
        }
        ?>
    </select>
</div>


            <div class="form-group">
                <label>Přiděleno</label>
                <input type="text" class="form-control" name="Assigned">
            </div>
            
            <div class="form-group">
                <label>Vytvořil (zadal)</label>
                <input type="text" class="form-control" name="Creator" required>
            </div>


            <div class="form-group">
                <label for="DueDate">Termín dokončení <span style="color:red">*</span></label>
                <input type="date" class="form-control" name="DueDate">
            </div>
            
            
            <button type="submit" class="btn btn-primary">Uložit</button>

            <a href="index.php" class="btn btn-secondary">Zpět na hlavní stránku</a>
        </form>
    </div>
</body>
</html>
