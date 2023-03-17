<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: session.php");
  exit;
}

// Connect to SQLite database
$db = new PDO('sqlite:database.sqlite');

// Retrieve image details
if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $stmt = $db->prepare('SELECT * FROM images WHERE id = :id');
  $stmt->bindParam(':id', $id);
  $stmt->execute();
  $image = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
  // Redirect to error page if image ID is not specified
  header('Location: edit_image.php?id=' . $id);
  exit();
}

// Update image details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = htmlspecialchars($_POST['title']);
  $imgdesc = htmlspecialchars($_POST['imgdesc']);
  $link = htmlspecialchars($_POST['link']);
  $tags = htmlspecialchars($_POST['tags']);
  
  $stmt = $db->prepare('UPDATE images SET title = :title, imgdesc = :imgdesc, link = :link, tags = :tags WHERE id = :id');
  $stmt->bindParam(':title', $title);
  $stmt->bindParam(':imgdesc', $imgdesc);
  $stmt->bindParam(':link', $link);
  $stmt->bindParam(':tags', $tags);
  $stmt->bindParam(':id', $id);
  $stmt->execute();
  
  // Redirect to image details page after update
  header('Location: edit_image.php?id=' . $id);
  exit();
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Image</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  </head>
  <body>
    <h3 class="text-secondary fw-bold mt-2 ms-2 text-center"><i class="bi bi-image"></i> Edit Image</h3>
    <div class="mt-3">
      <div class="roow">
        <div class="cool-6">
          <div class="caard">
            <img src="thumbnails/<?php echo htmlspecialchars($image['filename']); ?>" alt="<?php echo htmlspecialchars($image['title']); ?>" class="art"> 
          </div>
        </div>
        <div class="cool-6">
          <div class="caard container">
            <form method="POST">
              <div class="mb-2">
                <input type="text" placeholder="image title" name="title" value="<?php echo htmlspecialchars($image['title']); ?>" class="form-control" maxlength="40">
              </div>
              <div class="mb-2">
                <textarea name="imgdesc" class="form-control" placeholder="image description" maxlength="200"><?php echo htmlspecialchars($image['imgdesc']); ?></textarea>
              </div>
              <div class="mb-2">
                <input type="text" placeholder="image link" name="link" value="<?php echo htmlspecialchars($image['link']); ?>" class="form-control" maxlength="120">
              </div>
              <div class="mb-2">
                <input type="text" name="tags" placeholder="image tag" value="<?php echo htmlspecialchars($image['tags']); ?>" class="form-control">
              </div>
              <input type="submit" value="Save" class="form-control bg-primary text-white fw-bold mb-2">
              <a class="btn btn-danger form-control fw-bold text-white" href="profile.php">back</a>
              <div class="mt-5"></div>
            </form> 
          </div> 
        </div>
      </div>
    </div>
    <style>
      .roow {
        display: flex;
        flex-wrap: wrap;
      }

      .cool-6 {
        width: 50%;
        padding: 0 15px;
        box-sizing: border-box;
      }

      .caard {
        background-color: #fff;
        margin-bottom: 15px;
      }

      .art {
        border: 2px solid lightgray;
        border-radius: 10px;
        height: 350px;
        width: 100%;
        object-fit: cover;
      }

      @media (max-width: 768px) {
        .cool-6 {
          width: 100%;
          padding: 0;
        }
  
        .art {
          border-top: 2px solid lightgray;
          border-bottom: 2x solid lightgray;
          border-left: none;
          border-right: none;
          border-radius: 0;
          object-fit: cover;
        }
      } 
    </style>
  </body>
</html>