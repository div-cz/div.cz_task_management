<?php

require_once 'div.cz/config.php';



// Získání uživatele z GET parametru nebo ukončení skriptu, pokud není zadán
$assigned_to = $_GET['div'] ?? '';
if (!$assigned_to) {
    echo "<p>Uživatel není specifikován.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($assigned_to); ?> - Úkoly</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .completed {
        color: #6c757d; /* Šedý odstín pro dokončené úkoly */
        font-style: italic;
        background-color: #f0f0f0; /* Světlé pozadí pro dokončené úkoly */
    }
    .ongoing {
        color: #fd7e14; /* Oranžová barva pro probíhající úkoly */
        font-weight: bold; /* Tučně pro lepší viditelnost */
        background-color: #fff3cd; /* Světlé oranžové pozadí */
    }
    .pending {
        color: #0d6efd; /* Modrá barva pro úkoly čekající na zpracování */
        background-color: #e0e7ff; /* Světlé modré pozadí */
    }
</style>

</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-body">
                <?php
                $sql = "SELECT ID, Title, Status, Category, DueDate FROM AATasks WHERE Assigned = ? ORDER BY Created, ID DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $assigned_to);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    echo "<p>Pro " . htmlspecialchars($assigned_to) . " nebyly nalezeny žádné úkoly.</p>";
                } else {
                    echo "<h2>" . htmlspecialchars($assigned_to) . ": Správce úkolů</h2>";
                    echo "<ul class='list-unstyled'>";
while ($task = $result->fetch_assoc()) {
    // Přiřazení třídy podle stavu úkolu
    $class = '';
    if ($task['Status'] === 'Hotovo') {
        $class = 'completed';
    } elseif ($task['Status'] === 'Probíhá') {
        $class = 'ongoing';
    } elseif ($task['Status'] === 'Ke zpracování') {
        $class = 'pending';
    }

    echo "<li class='{$class}'><a href='div.cz_task_view.php?id=" . $task['ID'] . "' class='text-decoration-none'>" . htmlspecialchars($task['Title']) . "</a> | " . htmlspecialchars($task['Status']) . " | " . htmlspecialchars($task['DueDate']) . "</li>";
}
                    echo "</ul>";
                }
                $stmt->close();
                $conn->close();
                ?>
                <a href="/div/management/" class="btn btn-secondary mt-3">Zpět na správu úkolů</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
