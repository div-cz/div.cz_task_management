<?php


require_once 'div.cz/config.php';


// Získání ID úkolu z URL
$task_id = isset($_GET['id']) ? intval($_GET['id']) : 0;





if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assigned_user'])) {
    $assigned_user = $_POST['assigned_user'];

    $stmt = $conn->prepare("UPDATE AATasks SET assigned = ? WHERE id = ?");
    $stmt->bind_param("si", $assigned_user, $task_id);

    if ($stmt->execute()) {
        echo "<div class='notification is-success'><p>Úkol byl úspěšně přiřazen.</p><p><a href='index.php' class='button is-link'>Zpět na správce úkolů</a></p></div>";
    } else {
        echo "<div class='notification is-danger'><p>Přiřazení úkolu se nezdařilo: " . $conn->error . "</p></div>";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přiřadit úkol</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.3/css/bulma.min.css">
</head>
<body>
    <div class="container">
        <h1 class="title">Přiřadit úkol</h1>
        <form action="div.cz_task_assign.php?id=<?php echo $task_id; ?>" method="post">
            <div class="field">
                <label class="label">Přiřadit uživatele:</label>
                <div class="control">
                    <div class="select">
                        <select name="assigned_user">
                            <option value='Ionno'>Ionno</option>
                            <option value='Martin'>Martin</option> 
                            <option value='Jirka'>Jirka</option>
                            <option value='Aleš'>Aleš</option>
                            <option value='Barshee'>Barshee</option>
                            <option value='Christian'>Christian</option>
                            <option value='Eliška'>Eliška</option>   
                            <option value='Barča'>Barča</option>  
                            <option value='Míša'>Míša</option>                           
                        </select>
                    </div>
                </div>
            </div>
            <div class="field">
                <div class="control">
                    <button type="submit" class="button is-link">Přiřadit úkol</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
