<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}

$email = $_SESSION['email'];
  
// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Retrieve the tag from the URL parameter and remove any leading/trailing white spaces
$tag = trim(htmlspecialchars($_GET['tag']));

// Retrieve the tag from the URL parameter and remove any leading/trailing white spaces
$tag = trim(htmlspecialchars($_GET['tag']));

// Remove any additional spaces from the tag
$tag = str_replace(' ', '', $tag);

$stmt = $db->prepare("SELECT COUNT(*) FROM images WHERE REPLACE(tags, ' ', '') LIKE :tag OR REPLACE(tags, ' ', '') LIKE :tag_start OR REPLACE(tags, ' ', '') LIKE :tag_end OR tags = :tag_exact");
$stmt->bindValue(':tag', "{$tag},%", SQLITE3_TEXT);
$stmt->bindValue(':tag_start', "%,{$tag}", SQLITE3_TEXT);
$stmt->bindValue(':tag_end', "%,{$tag},%", SQLITE3_TEXT);
$stmt->bindValue(':tag_exact', "{$tag}", SQLITE3_TEXT);
$count = $stmt->execute()->fetchArray()[0];

// Define the limit and offset for the query
$limit = 100; // Number of items per page
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Get the current page number from the URL parameter
$offset = ($page - 1) * $limit; // Calculate the offset based on the page number and limit

// Retrieve the total number of images with the specified tag
$stmt = $db->prepare("SELECT COUNT(*) FROM images WHERE REPLACE(tags, ' ', '') LIKE :tag OR REPLACE(tags, ' ', '') LIKE :tag_start OR REPLACE(tags, ' ', '') LIKE :tag_end OR tags = :tag_exact");
$stmt->bindValue(':tag', "{$tag},%", SQLITE3_TEXT);
$stmt->bindValue(':tag_start', "%,{$tag}", SQLITE3_TEXT);
$stmt->bindValue(':tag_end', "%,{$tag},%", SQLITE3_TEXT);
$stmt->bindValue(':tag_exact', "{$tag}", SQLITE3_TEXT);
$total = $stmt->execute()->fetchArray()[0];

// Retrieve the images for the current page
$stmt = $db->prepare("SELECT * FROM images WHERE REPLACE(tags, ' ', '') LIKE :tag OR REPLACE(tags, ' ', '') LIKE :tag_start OR REPLACE(tags, ' ', '') LIKE :tag_end OR tags = :tag_exact ORDER BY id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':tag', "{$tag},%", SQLITE3_TEXT);
$stmt->bindValue(':tag_start', "%,{$tag}", SQLITE3_TEXT);
$stmt->bindValue(':tag_end', "%,{$tag},%", SQLITE3_TEXT);
$stmt->bindValue(':tag_exact', "{$tag}", SQLITE3_TEXT);
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();
 
// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $existing_fav = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = $image_id");

  if ($existing_fav == 0) {
    $db->exec("INSERT INTO favorites (email, image_id) VALUES ('$email', $image_id)");
  }

  // Redirect to the same page to prevent duplicate form submissions
  $currentUrl = strtok($_SERVER["REQUEST_URI"], '?');
  header("Location: {$currentUrl}?tag={$tag}&page={$page}");
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $db->exec("DELETE FROM favorites WHERE email = '$email' AND image_id = $image_id");

  // Redirect to the same page to prevent duplicate form submissions
  $currentUrl = strtok($_SERVER["REQUEST_URI"], '?');
  header("Location: {$currentUrl}?tag={$tag}&page={$page}");
  exit();
}
?>

<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $tag; ?></title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>  
    <?php include('header.php'); ?>
    <h5 class="text-secondary ms-2 mt-2 fw-bold"><i class="bi bi-tags-fill"></i> <?php echo $tag; ?> (<?php echo $count; ?>)</h5>
    <!-- Display the images -->
    <div class="images">
      <?php while ($image = $result->fetchArray()): ?>
        <div class="image-container">
          <a class="shadow" href="image.php?artworkid=<?php echo $image['id']; ?>">
            <img class="lazy-load" data-src="thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>">
          </a>
          <div class="favorite-btn">
            <?php
              $is_favorited = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = {$image['id']}");
              if ($is_favorited) {
            ?>
              <form method="POST">
                <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                <button type="submit" class="p-b3 btn btn-sm rounded btn-dark opacity-50 fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i></button>
              </form>
            <?php } else { ?>
              <form method="POST">
                <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                <button type="submit" class="p-b3 btn btn-sm rounded btn-dark opacity-50 fw-bold" name="favorite"><i class="bi bi-heart"></i></button>
              </form>
            <?php } ?> 
          </div>
        </div>
      <?php endwhile; ?>
    </div> 
    <div class="mt-3 mb-5 d-flex justify-content-center btn-toolbar container">
      <?php
        $totalPages = ceil($total / $limit);
        $prevPage = $page - 1;
        $nextPage = $page + 1;

        if ($page > 1) {
          echo '<a type="button" class="btn rounded-pill fw-bold btn-secondary opacity-50 me-1" href="?tag=' . $tag . '&page=' . $prevPage . '"><i class="bi bi-arrow-left-circle-fill"></i> prev</a>';
        }
        if ($page < $totalPages) {
          echo '<a type="button" class="btn rounded-pill fw-bold btn-secondary opacity-50 ms-1" href="?tag=' . $tag . '&page=' . $nextPage . '">next <i class="bi bi-arrow-right-circle-fill"></i></a>';
        }
      ?>
    </div>
    <style>
      @media (min-width: 768px) {
        .p-b3 {
          margin-left: 6px;
          border-radius: 4px;
          margin-top: -71px;
        } 
      }
      
      @media (max-width: 767px) {
        .p-b3 {
          margin-left: 5px;
          border-radius: 4px;
          margin-top: -71px;
        }
      } 

      @media (max-width: 450px) {
        .p-b3 {
          margin-left: 6px;
          border-radius: 4px;
          margin-top: -70px;
        } 
      }

      @media (max-width: 415px) {
        .p-b3 {
          margin-left: 6px;
          border-radius: 4px;
          margin-top: -70px;
        } 
      }

      @media (max-width: 380px) {
        .p-b3 {
          margin-left: 6px;
          border-radius: 4px;
          margin-top: -70px;
        } 
      }
    
      .image-container {
        margin-bottom: -24px;  
      }
    
      .images {
        display: grid;
        grid-template-columns: repeat(2, 1fr); /* Two columns in mobile view */
        grid-gap: 3px;
        justify-content: center;
        margin-right: 3px;
        margin-left: 3px;
      }

      @media (min-width: 768px) {
        /* For desktop view, change the grid layout */
        .images {
          grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
      }

      .images a {
        display: block;
        border-radius: 4px;
        overflow: hidden;
      }

      .images img {
        width: 100%;
        height: auto;
        object-fit: cover;
        height: 200px;
        transition: transform 0.5s ease-in-out;
      }
    </style>
    <script>
      document.addEventListener("DOMContentLoaded", function() {
        let lazyloadImages;
        if("IntersectionObserver" in window) {
          lazyloadImages = document.querySelectorAll(".lazy-load");
          let imageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
              if(entry.isIntersecting) {
                let image = entry.target;
                image.src = image.dataset.src;
                image.classList.remove("lazy-load");
                imageObserver.unobserve(image);
              }
            });
          });
          lazyloadImages.forEach(function(image) {
            imageObserver.observe(image);
          });
        } else {
          let lazyloadThrottleTimeout;
          lazyloadImages = document.querySelectorAll(".lazy-load");

          function lazyload() {
            if(lazyloadThrottleTimeout) {
              clearTimeout(lazyloadThrottleTimeout);
            }
            lazyloadThrottleTimeout = setTimeout(function() {
              let scrollTop = window.pageYOffset;
              lazyloadImages.forEach(function(img) {
                if(img.offsetTop < (window.innerHeight + scrollTop)) {
                  img.src = img.dataset.src;
                  img.classList.remove('lazy-load');
                }
              });
              if(lazyloadImages.length == 0) {
                document.removeEventListener("scroll", lazyload);
                window.removeEventListener("resize", lazyload);
                window.removeEventListener("orientationChange", lazyload);
              }
            }, 20);
          }
          document.addEventListener("scroll", lazyload);
          window.addEventListener("resize", lazyload);
          window.addEventListener("orientationChange", lazyload);
        }
      })
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>