<?php

require_once 'div.cz/config.php';


function fetchTasks() {
    global $conn;
    $sql = "SELECT ID, Title, Priority, Status, Assigned, Creator, Category, ParentID FROM AATasks WHERE Status = 'Ke zpracování' OR Status = 'Probíhá' ORDER BY ParentID, ID";
    $result = $conn->query($sql);
    $tasks = [];


    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
    } else {
        echo "SQL error: " . $conn->error; 
    }
    return $tasks;
}

$tasks = fetchTasks(); 

//print_r($tasks);

$taskHierarchy = [];
foreach ($tasks as $task) {
    $taskHierarchy[$task['ParentID']][] = $task;
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Správce úkolů</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    .child-of-0 { padding-left: 20px; background-color: #f8f9fa; }
    .child-of-{id} { padding-left: 40px; background-color: #e9ecef; }

    .priority-high { color: #dc3545; } /* červená pro vysokou prioritu */
    .priority-medium { color: #ffc107; } /* žlutá pro střední prioritu */
    .priority-low { color: #28a745; } /* zelená pro nízkou prioritu */

    .child-row { padding-left: 30px; background-color: #f8f9fa; } /* světle šedé pozadí pro podúkoly */
    .parent-row { background-color: #e9ecef; font-weight: bold;} /* tmavě šedé pozadí pro rodiče */




@media (max-width: 768px) {
    .mobil {
        display:none;
            }
}
</style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Správce úkolů <small> <a href="task_add.php" class="btn btn-primary">Přidat nový úkol</a></small></h1>
        <div class="row">
            <div class="col-md-12">
                <h2> </h2>

<?php
$parents = [];
$children = [];
foreach ($tasks as $task) {
    if ($task['ParentID']) {
        $children[$task['ParentID']][] = $task;
    } else {
        $parents[] = $task;
    }
}
?>
        <form id="category-filter-form">
            <label for="category-filter">Filtrovat podle kategorie:</label>
            <select id="category-filter" class="form-control">
                <option value="">Všechny</option>
                <option value="Frontend">Frontend</option>
                <option value="Backend">Backend</option>
                <option value="Testování">Testování</option>
                <option value="Databáze">Databáze</option>
                <option value="Server">Server</option>
                <option value="iOS">iOS</option>
            </select>
        </form>


<table class="table"  id="tasks-table">
    <thead>
        <tr>
            <th>Název úkolu</th>
            <th>Priorita</th>
            <th class="mobil">Stav</th>
            <th>Přiděleno</th>
            <th>Kategorie</th>
            <th class="mobil">Zadal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($parents as $parent):
            $priorityClass = '';
            switch ($parent['Priority']) { 
                case 'Vysoká':
                    $priorityClass = 'priority-high';
                    break;
                case 'Střední':
                    $priorityClass = 'priority-medium';
                    break;
                case 'Nízká':
                    $priorityClass = 'priority-low';
                    break;
            }
        ?>

            <tr>
                <td><?= $parent['ID'] ?>: <a href="task_view.php?id=<?= $parent['ID'] ?>"><?= htmlspecialchars($parent['Title']) ?></a></td>
                <td class="<?= $priorityClass ?>"><?= htmlspecialchars($parent['Priority']) ?></td>

                <td class="mobil"><?= htmlspecialchars($parent['Status']) ?></td>

<td>
    <?php if (!empty($parent['Assigned'])): ?>
        <a href="https://fdk.cz/div/management/user_tasks.php?div=<?= urlencode($parent['Assigned']) ?>">
            <?= htmlspecialchars($parent['Assigned']) ?>
        </a>
    <?php else: ?>
        Nepřiděleno
    <?php endif; ?>
</td>
            
                <td><?= htmlspecialchars($parent['Category']) ?></td>
                <td class="mobil"><?= $parent['Creator'] ?></td>
            </tr>


            <?php if (array_key_exists($parent['ID'], $children)): ?>
                <?php foreach ($children[$parent['ID']] as $child):
                    $priorityClass = ''; 
                    switch ($child['Priority']) { 
                        case 'Vysoká':
                            $priorityClass = 'priority-high';
                            break;
                        case 'Střední':
                            $priorityClass = 'priority-medium';
                            break;
                        case 'Nízká':
                            $priorityClass = 'priority-low';
                            break;
                    }
                ?>

                    <tr style="background-color: #f8f9fa; padding-left: 20px;font-size:80%">
                        <td style="padding-left: 50px;"><a href="task_view.php?id=<?= $child['ID'] ?>"><?= htmlspecialchars($child['Title']) ?></a></td>
                        <td class="<?= $priorityClass ?>"><?= htmlspecialchars($child['Priority']) ?></td>
                        
                <td class="mobil"><?= htmlspecialchars($child['Status']) ?></td>

<td>
    <?php if (!empty($child['Assigned'])): ?>
        <a href="https://fdk.cz/div/management/user_tasks.php?div=<?= urlencode($child['Assigned']) ?>">
            <?= htmlspecialchars($child['Assigned']) ?>
        </a>
    <?php else: ?>
        Nepřiděleno
    <?php endif; ?>
</td>
                        <td><?= htmlspecialchars($child['Category']) ?></td>
                        <td class="mobil"><?= $child['Creator'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </tbody>
</table>



            </div>
        </div>
    </div>



<!--
<div class="container mt-5">
  <h1 class="mb-4">Poslední komentáře k úkolům: </h1>
  <div class="row">
    <div class="col-md-12">

      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Název úkolu</th>
            <th>Comments</th>
            <th>Přiděleno</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($parents as $parent): ?>
            <tr>
              <td><?= $parent['ID'] ?>: </td>
              <td><a href="https://fdk.cz/div/management/task_view.php?id=<?= $parent['ID'] ?>"><?= htmlspecialchars($parent['Title']) ?></a></td>
              <?php
              // Fetch and display latest comments (within <td>)
              
              $comment_query = "SELECT Comments FROM AATasks WHERE ID = " . $parent['ID'] . " AND NOT ISNULL(Comments) ORDER BY Updated DESC LIMIT 10";

              $comment_result = $conn->query($comment_query);

              if ($comment_result) {
                $comments = "";
                while ($comment_row = $comment_result->fetch_assoc()) {
                  $comments .= substr($comment_row['Comments'], 0, 10) . " ";  // Truncate to 10 words
                }
                // Check if comments exist before displaying
                if (!empty($comments)) {
                  echo "<td>" . trim($comments) . "</td>";
                } else {
                  echo "<td>No comments</td>";
                }
              } else {
                echo "<td>No comments</td>";  // Handle no comments case or query error
              }
              ?>
              <td>
                <?php if (!empty($parent['Assigned'])): ?>
                  <a href="https://fdk.cz/div/management/user_tasks.php?div=<?= urlencode($parent['Assigned']) ?>">
                    <?= htmlspecialchars($parent['Assigned']) ?>
                  </a>
                <?php else: ?>
                  Nepřiděleno
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
-->



    <script>
        document.getElementById('category-filter').addEventListener('change', function() {
            var selectedCategory = this.value.toLowerCase();
            var rows = document.querySelectorAll('#tasks-table tbody tr');
            rows.forEach(function(row) {
                var category = row.cells[4].textContent.toLowerCase();
                if (selectedCategory === "" || category === selectedCategory) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
    
    <footer style="mt-5">
    Div.Task.Management v.1.1
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
