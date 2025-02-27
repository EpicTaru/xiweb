<?php 
function create_unsold_items_table() 
{
    require 'config/database.conf';
    require 'globals/ahID.php';
    
    $aH = array();
    $name = array();
    $stack = array();
    $listings = array();
    
    try {
        $conn = new PDO("mysql:host=$dbServer;dbname=$dbName", $dbUser, $dbPass);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $queryAH  = $conn->query('
            SELECT
                ib.itemid,
	            ib.aH,
	            REPLACE(ib.name, "_", " ") AS "name",
	            COUNT(*) AS "listings",
	            (CASE WHEN ah.stack=1 THEN "Y" ELSE "N" END) as "stack"
            FROM item_basic ib
            INNER JOIN 
            	auction_house ah ON ib.itemid = ah.itemid
            WHERE ah.buyer_name IS NULL
            GROUP BY ah.itemId, ah.stack
            ORDER BY ib.aH ASC, ib.itemId;');
        $i = 0;
        while ($row = $queryAH->fetch()){
            if ($row["aH"] == 0) {
                error_log("Item {$row["name"]} (itemid {$row["itemid"]}) doesnt have a valid category in globals/ahID.php", 0);
            } else {
                $aH[$i] = $row["aH"];
                $name[$i] = $row["name"];
                $stack[$i] = $row["stack"];
                $listings[$i] = $row["listings"];
                $i = $i + 1;
            }
        }
    }
    catch(PDOException $e){
        echo "Connection failed: " . $e->getMessage();
    }
    
    // Table is being created here
    echo "<table class='plaintable'><tr><th>Category</th><th>Name</th><th># Listings</th><th>Is a stack?</th></tr>";
    for ($x = 0; $x < $i ; $x+=1){
        echo "<tr><td>" . $ahID[$aH[$x]] . "</td><td>$name[$x]</td><td>$listings[$x]</td><td>$stack[$x]</td></tr>";
    }
    echo "</table>";
}
?>
