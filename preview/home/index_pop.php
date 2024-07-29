<?php
// Set the limit of images per page
$limit = 12;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Get the total number of images
$total = $db->querySingle("SELECT COUNT(*) FROM images");

// Get the images for the current page using a parameterized query with LIMIT and OFFSET clauses
$stmt = $db->prepare("SELECT images.*, COUNT(favorites.id) AS favorite_count 
                     FROM images 
                     LEFT JOIN favorites ON images.id = favorites.image_id 
                     GROUP BY images.id 
                     ORDER BY favorite_count DESC 
                     LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();
?>

    <?php include('image_card_home.php')?>