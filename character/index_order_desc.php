<?php
$stmt = $db->prepare("SELECT COUNT(*) FROM images WHERE REPLACE(characters, ' ', '') LIKE :characterWithoutSpaces ESCAPE '\\' OR REPLACE(characters, ' ', '') LIKE :character_start ESCAPE '\\' OR REPLACE(characters, ' ', '') LIKE :character_end ESCAPE '\\' OR characters = :character_exact");
$stmt->bindValue(':characterWithoutSpaces', "{$characterWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':character_start', "%,{$characterWithoutSpaces}", SQLITE3_TEXT);
$stmt->bindValue(':character_end', "%,{$characterWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':character_exact', $character, SQLITE3_TEXT);
$count = $stmt->execute()->fetchArray()[0];

// Define the limit and offset for the query
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT); // Assuming $email is the email you want to search for
$resultNum = $queryNum->execute();
$user = $resultNum->fetchArray(SQLITE3_ASSOC);

$numpage = $user['numpage'];

// Set the limit of images per page
$limit = empty($numpage) ? 50 : $numpage;
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Get the current page number from the URL parameter
$offset = ($page - 1) * $limit; // Calculate the offset based on the page number and limit

// Retrieve the total number of images with the specified character
$stmt = $db->prepare("SELECT COUNT(*) FROM images WHERE REPLACE(characters, ' ', '') LIKE :characterWithoutSpaces ESCAPE '\\' OR REPLACE(characters, ' ', '') LIKE :character_start ESCAPE '\\' OR REPLACE(characters, ' ', '') LIKE :character_end ESCAPE '\\' OR characters = :character_exact");
$stmt->bindValue(':characterWithoutSpaces', "{$characterWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':character_start', "%,{$characterWithoutSpaces}", SQLITE3_TEXT);
$stmt->bindValue(':character_end', "%,{$characterWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':character_exact', $character, SQLITE3_TEXT);
$total = $stmt->execute()->fetchArray()[0];

// Retrieve the images for the current page
$stmt = $db->prepare("SELECT * FROM images WHERE REPLACE(characters, ' ', '') LIKE :characterWithoutSpaces ESCAPE '\\' OR REPLACE(characters, ' ', '') LIKE :character_start ESCAPE '\\' OR REPLACE(characters, ' ', '') LIKE :character_end ESCAPE '\\' OR characters = :character_exact ORDER BY title DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':characterWithoutSpaces', "{$characterWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':character_start', "%,{$characterWithoutSpaces}", SQLITE3_TEXT);
$stmt->bindValue(':character_end', "%,{$characterWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':character_exact', $character, SQLITE3_TEXT);
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();
?>

    <?php include('image_card_character.php')?>