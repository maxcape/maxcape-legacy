<?php
    require_once "../../dbfunctions.php";
    $dbf = new dbfunctions;

    $dbf->connectToDatabase($dbf->database) or die("Cannot connect to database");

    $query = mysql_real_escape_string($_POST['query']);

    $results = $dbf->getAllAssocResults("SELECT * FROM logs l JOIN users u ON l.UserID = u.UserID WHERE LogTitle LIKE '%$query%' OR LogDescription LIKE '%$query%' OR Username LIKE '%$query%'");

?>
<table border="1">
    <tr>
        <th>Log Title</th>
        <th>Log Description</th>
        <th>Author</th>
        <th>Log Type</th>
    </tr>

    <?php
        foreach ($results as $log) {
            $logtype = $log['LogType'];
            switch($logtype) {
                case 1:
                    $logtype = "Bank Tab";
                    break;
                case 2:
                    $logtype = "Kill Log";
                    break;
                case 3:
                    $logtype = "Trip Log";
                    break;
                case 4:
                    $logtype = "Cumulative";
                    break;
            }
            ?>
            <tr onclick="redirectToLog(<?php echo $log['LogID']; ?>);">
                <td><?php echo $log['LogTitle']; ?></td>
                <td><?php echo $log['LogDescription'] ?></td>
                <td><?php echo $log['Username'] ?></td>
                <td><?php echo $logtype; ?></td>
            </tr>
        <?php
        }
    ?>
</table>
