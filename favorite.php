<?php
  session_start();
  if (!isset($_SESSION['email'])) {
    header("Location: session.php");
    exit;
  }

  // Connect to the SQLite database
  $db = new SQLite3('database.sqlite');

  // Get all of the favorite images for the current user
  $email = $_SESSION['email'];
  $result = $db->query("SELECT images.* FROM images INNER JOIN favorites ON images.id = favorites.image_id WHERE favorites.email = '$email' ORDER BY favorites.id DESC");

  // Process any favorite/unfavorite requests
  if (isset($_POST['favorite'])) {
    $image_id = $_POST['image_id'];
    
    // Check if the image has already been favorited by the current user
    $existing_fav = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = $image_id");
    
    if ($existing_fav == 0) {
      $db->exec("INSERT INTO favorites (email, image_id) VALUES ('$email', $image_id)");
    }
    
    // Redirect to the same page to prevent duplicate form submissions
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
    
  } elseif (isset($_POST['unfavorite'])) {
    $image_id = $_POST['image_id'];
    $db->exec("DELETE FROM favorites WHERE email = '$email' AND image_id = $image_id");
    
    // Redirect to the same page to prevent duplicate form submissions
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <style>
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
    <div class="mt-2">
      <h5 class="text-center text-secondary fw-bold">MY FAVORITES</h5>
      <div class="images">
        <?php while ($image = $result->fetchArray()): ?>
          <div class="image-container">
            <div class="position-relative">
              <a class="shadow" href="image.php?artworkid=<?php echo $image['id']; ?>">
                <img class="lazy-load" data-src="thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>">
              </a> 
              <div class="position-absolute top-0 start-0">
                <div class="dropdown">
                  <button class="btn btn-sm btn-secondary ms-1 mt-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                  </button>
                  <ul class="dropdown-menu">
                    <form method="POST">
                      <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                      <li><button type="submit" class="dropdown-item fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i> <small>unfavorite</small></button></li>
                    </form>
                  <li><button class="dropdown-item fw-bold" onclick="shareImage(<?php echo $image['id']; ?>)"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
                  <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $image['id']; ?>"><i class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
                </ul>
                <!-- Modal -->
                <div class="modal fade" id="infoImage_<?php echo $image['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content rounded-3 shadow">
                      <div class="modal-body p-4 text-center">
                        <h5 class="mb-4 fw-bold"><?php echo $image['title']?></h5>
                        <img class="rounded object-fit-cover mb-3 shadow" src="thumbnails/<?php echo $image['filename']; ?>" style="width: 100%; height: 300px;">
                        <p class="text-start fw-semibold"><?php echo $image['imgdesc']?></p>
                        <div class="card container">
                          <p class="text-center fw-semibold mt-2">Image Information</p>
                          <p class="text-start fw-semibold">Image ID: "<?php echo $image['id']?>"</p>
                          <?php
                            // Get image size in megabytes
                            $image_size = round(filesize('images/' . $image['filename']) / (1024 * 1024), 2);

                            // Get image dimensions
                            list($width, $height) = getimagesize('images/' . $image['filename']);

                            // Display image information
                            echo "<p class='text-start fw-semibold'>Image data size: " . $image_size . " MB</p>";
                            echo "<p class='text-start fw-semibold'>Image dimensions: " . $width . "x" . $height . "</p>";
                            echo "<p class='text-start fw-semibold'><a class='text-decoration-none' href='images/" . $image['filename'] . "'>View original image</a></p>";
                          ?>
                        </div>
                        <div class="btn-group w-100 mt-2">
                          <a class="btn btn-primary fw-bold rounded-start-5" href="image.php?artworkid=<?php echo $image['id']; ?>"><i class="bi bi-eye-fill"></i> view</a>
                          <a class="btn btn-primary fw-bold" href="images/<?php echo $image['filename']; ?>" download><i class="bi bi-download"></i> download</a>
                          <button class="btn btn-primary fw-bold rounded-end-5" onclick="shareImage(<?php echo $image['id']; ?>)"><i class="bi bi-share-fill"></i> share</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?> 
      </div>
    </div>
    <script>
      function shareImage(userId) {
        // Compose the share URL
        var shareUrl = 'image.php?artworkid=' + userId;

        // Check if the Share API is supported by the browser
        if (navigator.share) {
          navigator.share({
          url: shareUrl
        })
          .then(() => console.log('Shared successfully.'))
          .catch((error) => console.error('Error sharing:', error));
        } else {
          console.log('Share API is not supported in this browser.');
          // Provide an alternative action for browsers that do not support the Share API
          // For example, you can open a new window with the share URL
          window.open(shareUrl, '_blank');
        }
      }
    </script>
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