<?php

require_once 'div.cz/config.php';

function getStatusClass($status) {
    switch (strtolower($status)) {
        case 'hotovo':
            return 'badge-success';
        case 'probíhá':
            return 'badge-warning';
        case 'ke zpracování':
            return 'badge-info';
        default:
            return 'badge-secondary';  // Default class if status is not matched
    }
}

function getPriorityClass($priority) {
    switch (strtolower($priority)) {
        case 'vysoká':
            return 'badge-danger';
        case 'střední':
            return 'badge-primary';
        case 'nízká':
            return 'badge-success';
        default:
            return 'badge-secondary';  // Default class if priority is not matched
    }
}

// Check if the connection is successful
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Retrieve task ID from URL parameter
$task_id = isset($_GET['id']) ? $_GET['id'] : die('Task ID is required.');

// Prepare and execute SQL query to fetch task details
$stmt = $conn->prepare("SELECT * FROM AATasks WHERE id = ?");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo "No task found with ID: $task_id";
    $conn->close();
    exit;
}





// Připravte a proveďte SQL dotaz pro získání detailů úkolu spolu s názvem rodičovského úkolu
$stmt = $conn->prepare("
    SELECT t.*, parent.Title AS ParentTitle 
    FROM AATasks t
    LEFT JOIN AATasks parent ON t.ParentID = parent.ID 
    WHERE t.id = ?
");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Příprava dotazu pro získání podúkolů, pokud tento úkol je rodič
$children = [];
if ($row) {
    $childrenStmt = $conn->prepare("SELECT * FROM AATasks WHERE ParentID = ?");
    $childrenStmt->bind_param("i", $task_id);
    $childrenStmt->execute();
    $childrenResult = $childrenStmt->get_result();
    while ($child = $childrenResult->fetch_assoc()) {
        $children[] = $child;
    }
    $childrenStmt->close();
}

// Uzavření hlavního dotazu
$stmt->close();



// Close the connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail úkolu</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container { max-width: 800px; margin: 0 auto; padding-top: 50px; }
        pre {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
    font-size: 1rem;
    line-height: 1.5;
    color: #212529;
    background-color: transparent;
    border: none;
    white-space: pre-wrap;
}

    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-3">Detail úkolu</h1>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_as_done'])) {
    // Připravení SQL dotazu pro aktualizaci stavu úkolu
    $updateStmt = $conn->prepare("UPDATE AATasks SET Status = 'Hotovo' WHERE id = ?");
    $updateStmt->bind_param("i", $task_id);
    if ($updateStmt->execute()) {
        echo "<div class='alert alert-success'>Úkol byl označen jako hotový.</div>";
        // Po aktualizaci znovu načíst údaje o úkolu
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
    } else {
        echo "<div class='alert alert-danger'>Chyba při aktualizaci úkolu: " . $conn->error . "</div>";
    }
    $updateStmt->close();
}

?>




        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($row['Title'] ?? 'N/A'); ?></h5>

                <p class="card-text"><strong>Přiděleno:</strong> <?= htmlspecialchars($row['Assigned'] ?? 'Nepřiděleno'); ?></p>

                <p class="card-text"><strong>Popis:</strong> <pre><?= htmlspecialchars($row['Description'] ?? 'N/A'); ?></pre></p>
                <p class="card-text"><strong>Komentáře:</strong> <pre><?= htmlspecialchars($row['Comments'] ?? 'Žádné komentáře'); ?></pre></p>


                <p class="card-text"><strong>Priorita:</strong> <span class="badge <?= getPriorityClass($row['Priority'] ?? 'N/A') ?>"><?= htmlspecialchars($row['Priority'] ?? 'N/A'); ?></span></p>

                <p class="card-text"><strong>Kategorie:</strong> <?= htmlspecialchars($row['Category'] ?? 'N/A'); ?></p>
                <p class="card-text"><strong>Stav:</strong> <span class="badge <?= getStatusClass($row['Status'] ?? 'N/A') ?>"><?= htmlspecialchars($row['Status'] ?? 'N/A'); ?></span></p>


                <p class="card-text"><strong>Termín dokončení:</strong> <?= $row['DueDate'] ? date('d.m.Y', strtotime($row['DueDate'])) : 'N/A'; ?></p>
                <!--<p class="card-text"><strong>Rodičovský úkol:</strong> <?= htmlspecialchars($row['ParentID'] ?? 'Žádný'); ?></p>-->

                <p class="card-text"><strong>Vytvořeno:</strong> <?= $row['Created'] ? date('d.m.Y', strtotime($row['Created'])) : 'N/A'; ?></p>
                <p class="card-text"><strong>Aktualizováno:</strong> <?= $row['Updated'] ? date('d.m.Y', strtotime($row['Updated'])) : 'N/A'; ?></p>
<form method="post">
    <a href="index.php" class="btn btn-secondary mt-2">Zpět na hlavní stránku</a>
    
    <a href="task_edit.php?id=<?= $task_id; ?>" class="btn btn-success mt-2">Editovat úkol</a>

    <a href="task_comment.php?id=<?= $task_id; ?>" class="btn btn-primary mt-2">Přidat komentář</a>
    
    <?php if ($row) {
    // Formátujeme datum a čas pro Google Kalendář (YYYYMMDDTHHMMSSZ)
    $startDate = date('Ymd\THis\Z', strtotime($row['Created'])); // Začátek události jako datum vytvoření úkolu
    $endDate = $row['DueDate'] ? date('Ymd\THis\Z', strtotime($row['DueDate'])) : date('Ymd\THis\Z', strtotime($row['Created'] . ' +1 hour')); // Konec jako DueDate, pokud není nastaven, přidáme jednu hodinu

    // URL pro Google Kalendář
    $googleLink = "https://calendar.google.com/calendar/render?action=TEMPLATE";
    $googleLink .= "&text=" . urlencode($row['Title']);
    $googleLink .= "&dates=" . $startDate . "/" . $endDate;
    $googleLink .= "&details=" . urlencode("Přiděleno: " . $row['Assigned'] . "\nPopis: " . $row['Description']);
    $googleLink .= "&location=" . urlencode($row['Location'] ?? 'N/A'); // Přidání umístění, pokud je k dispozici
}

// Odkaz pro přidání do HTML kódu
echo '<a href="' . $googleLink . '" target="_blank" class="btn btn-info mt-2">Přidat do Google kalendáře</a>';
?>

    <!--<button type="submit" name="mark_as_done" class="btn btn-success mt-2">Hotovo</button>-->
</form>



<br><br>
            </div>
        </div>



        <div class="card mt-3">
            <div class="card-body">

            <!-- Odkaz na nadřazený úkol, pokud existuje -->
            <?php if (!empty($row['ParentID'])): ?>
                <p class="card-text"><strong>Nadřazený úkol:</strong> <a href="task_view.php?id=<?= $row['ParentID']; ?>"><?= htmlspecialchars($row['ParentTitle']); ?></a></p>
            <?php else: ?>
                <p class="card-text"><strong>Nadřazený úkol:</strong> Žádný</p>
            <?php endif; ?>

    <!-- Zobrazit všechny podúkoly, pokud existují -->
<?php if (!empty($children)): ?>
        <h5>Podúkoly:</h5>
        <ul>
        <?php foreach ($children as $child): ?>
            <li>
                <a href="div.cz_task_view.php?id=<?= $child['ID'] ?>">
                    <?= htmlspecialchars($child['Title']); ?>
                </a>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
            </div>
        </div>
    
    
    <!--<form action="send_email.php" method="post">
    <input type="hidden" name="task_id" value="<?= $task_id; ?>">
    <input type="email" name="email" required placeholder="Zadejte email">
    <button type="submit">TESTování emailu</button>
</form>-->
    
    
    </div>
</body>
</html>
