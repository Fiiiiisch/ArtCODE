<?php
require_once('../../auth.php');

$email = $_SESSION['email'];

$db = new PDO('sqlite:../../database.sqlite');

if (isset($_GET['id'])) {
  $post_id = $_GET['id'];

  // Use prepared statements to prevent SQL injection
  $query = "SELECT * FROM novel WHERE id = :id AND email = :email";
  $stmt = $db->prepare($query);
  $stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
  $stmt->bindParam(':email', $email, PDO::PARAM_STR);
  $stmt->execute();

  // Fetch the post
  $post = $stmt->fetch();

  if ($post) {
    // Delete associated comments from comments_novel table
    $deleteCommentsQuery = "DELETE FROM comments_novel WHERE page_id = :page_id";
    $stmt = $db->prepare($deleteCommentsQuery);
    $stmt->bindParam(':page_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();

    // Delete associations from favorites_novel table
    $deleteFavoritesQuery = "DELETE FROM favorites_novel WHERE novel_id = :novel_id";
    $stmt = $db->prepare($deleteFavoritesQuery);
    $stmt->bindParam(':novel_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();

    // Use prepared statement for DELETE query as well
    $deletePostQuery = "DELETE FROM novel WHERE id = :id";
    $stmt = $db->prepare($deletePostQuery);
    $stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
    $stmt->execute();

    // Delete the image from the database
    $stmt = $db->prepare("DELETE FROM novel WHERE filename = :filename");
    $stmt->bindValue(':filename', $post['filename']);
    $stmt->execute();

    // Delete the original image and thumbnail
    unlink('images/' . $post['filename']);
    unlink('thumbnails/' . $post['filename']);

    header("Location: profile.php");
    exit;
  }
}

header('Location: ' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/feeds/notes/');
exit; // exit the script to prevent further output
?>
