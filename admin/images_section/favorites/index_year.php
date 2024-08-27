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

// Get the current date and year range
$startOfYear = date('Y-01-01'); // First day of the current year
$endOfYear = date('Y-12-31');   // Last day of the current year

// Prepare and execute the query to get the favorited images for the current page
$stmt = $db->prepare("
  SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(SUM(daily.views), 0) AS views
  FROM images
  INNER JOIN favorites ON images.id = favorites.image_id
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfYear AND :endOfYear
  WHERE favorites.email = :email
  GROUP BY images.id, users.artist, users.pic, users.id
  ORDER BY views DESC, images.id DESC
  LIMIT :limit OFFSET :offset
");

$stmt->bindValue(':startOfYear', $startOfYear, SQLITE3_TEXT);
$stmt->bindValue(':endOfYear', $endOfYear, SQLITE3_TEXT);
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();

// Get the total count of favorited images for the current user within the current year
$total = $db->querySingle("
  SELECT COUNT(*)
  FROM images
  INNER JOIN favorites ON images.id = favorites.image_id
  LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN '$startOfYear' AND '$endOfYear'
  WHERE favorites.email = '$email'
");
?>

    <?php include('image_card_feeds_fav.php'); ?>