<?php
// Connect to the SQLite database using parameterized query
$db = new SQLite3('../database.sqlite'); 
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT, password TEXT, artist TEXT, pic TEXT, desc TEXT, bgpic TEXT, token TEXT, twitter TEXT, pixiv TEXT, other, region TEXT, joined DATETIME, born DATETIME)");
$stmt->execute();
$stmt = $db->prepare('CREATE TABLE IF NOT EXISTS news (id INTEGER PRIMARY KEY, title TEXT, description TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, ver TEXT, verlink TEXT)');
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS images (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, email TEXT, tags TEXT, title TEXT, imgdesc TEXT, link TEXT, date DATETIME, view_count INT DEFAULT 0, type TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS image_child (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT NOT NULL, image_id INTEGER NOT NULL, email TEXT NOT NULL, FOREIGN KEY (image_id) REFERENCES images (id))");
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <link rel="manifest" href="../manifest.json">
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('landing_page.php');?>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>
