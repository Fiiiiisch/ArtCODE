<?php
// Prepare the query to get the user's numpage
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT);
$resultNum = $queryNum->execute();
$user = $resultNum->fetchArray(SQLITE3_ASSOC);

$numpage = $user['numpage'];

// Set the limit of images per page
$limit = empty($numpage) ? 50 : $numpage;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Get the current date in YYYY-MM-DD format
$currentDate = date('Y-m-d');

// Prepare and execute the query to get the images for the current page
$stmt = $db->prepare("
  SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(daily.views, 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id AND daily.date = :currentDate
  ORDER BY views DESC, images.id DESC
  LIMIT :limit OFFSET :offset
");

$stmt->bindValue(':currentDate', $currentDate, SQLITE3_TEXT);
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();

// Get the total count of favorite images for the current user
$total = $db->querySingle("SELECT COUNT(*) FROM images LEFT JOIN daily ON images.id = daily.image_id AND daily.date = '$currentDate'");
?>

    <?php include('image_card_feeds_fav.php'); ?>