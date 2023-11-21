<?php
require_once('auth.php');

// Connect to the database
$db = new SQLite3('database.sqlite');
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS forum (id INTEGER PRIMARY KEY, email TEXT, comment TEXT, created_at TEXT)");
$stmt->execute();

// Check if the form was submitted for adding a new comment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
  // Get the comment from the form data
  $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $comment = nl2br($comment);
  $email = $_SESSION['email'];

  // Get the current time
  $now = date('Y-m-d');

  // Insert the comment into the database
  $stmt = $db->prepare("INSERT INTO forum (email, comment, created_at) VALUES (:email, :comment, :created_at)");
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':comment', $comment, SQLITE3_TEXT);
  $stmt->bindValue(':created_at', $now, SQLITE3_TEXT);
  $stmt->execute();

  // Redirect back to the image page
  $currentURL = $_SERVER['REQUEST_URI'];
  $redirectURL = $currentURL;
  header("Location: $redirectURL");
  exit();
}

// Check if the form was submitted for updating or deleting a comment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
  $action = $_POST['action'];
  $comment_id = $_POST['comment_id'];

  // Get the email of the current user
  $email = $_SESSION['email'];

  // Check if the comment belongs to the current user
  $stmt = $db->prepare("SELECT * FROM forum WHERE id=:comment_id AND email=:email");
  $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $comment = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

  if ($comment) {
    if ($action == 'delete') {
      // Delete the comment from the comments table
      $stmt = $db->prepare("DELETE FROM forum WHERE id=:comment_id");
      $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
      $stmt->execute();

      // Delete the corresponding replies from the reply_comments table
      $stmt = $db->prepare("DELETE FROM reply_forum WHERE comment_id=:comment_id");
      $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
      $stmt->execute();
    }
  }

  // Redirect back to the image page
  $currentURL = $_SERVER['REQUEST_URI'];
  $redirectURL = $currentURL;
  header("Location: $redirectURL");
  exit();
}

// Set the number of items to display per page
$items_per_page = 100;

// Get the current page from the URL, or default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the starting offset for the current page
$offset = ($page - 1) * $items_per_page;

// Get the total number of forum items
$total_items_stmt = $db->prepare("SELECT COUNT(*) FROM forum");
$total_items = $total_items_stmt->execute()->fetchArray()[0];

// Calculate the total number of pages
$total_pages = ceil($total_items / $items_per_page);

// Get all forum items for the current page
$stmt = $db->prepare("SELECT forum.*, users.artist, users.pic, users.id as iduser FROM forum JOIN users ON forum.email = users.email ORDER BY forum.id DESC LIMIT :items_per_page OFFSET :offset");
$stmt->bindValue(':items_per_page', $items_per_page, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$forum = $stmt->execute();
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Forum</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <br><br>
    <div class="container-fluid mt-2">
      <?php
        while ($comment = $forum->fetchArray()) :
      ?>
        <div class="card border-0 shadow mb-1 position-relative">
          <div class="d-flex align-items-center mb-2 position-relative">
            <div class="position-absolute top-0 start-0 m-1">
              <img class="rounded-circle" src="<?php echo !empty($comment['pic']) ? $comment['pic'] : "icon/profile.svg"; ?>" alt="Profile Picture" width="32" height="32">
              <a class="text-dark text-decoration-none fw-semibold" href="artist.php?id=<?php echo $comment['iduser'];?>" target="_blank">@<?php echo $comment['artist']; ?></a>
            </div>
            <?php if ($comment['email'] == $_SESSION['email']) : ?>
              <div class="dropdown ms-auto position-relative">
                <button class="btn btn-sm btn-secondary opacity-50 position-absolute top-0 end-0 m-1" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                  <form action="" method="POST">
                    <a href="edit_forum.php?forumid=<?php echo $comment['id']; ?>" class="dropdown-item fw-semibold">
                      <i class="bi bi-pencil-fill me-2"></i>Edit
                    </a>
                    <input type="hidden" name="filename" value="<?php echo $filename; ?>">
                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                    <button type="submit" name="action" onclick="return confirm('Are you sure?')" value="delete" class="dropdown-item fw-semibold">
                      <i class="bi bi-trash-fill me-2"></i>Delete
                    </button>
                  </form>
                </div>
              </div>
            <?php endif; ?>
          </div>
          <div class="mt-5 container-fluid fw-medium">
            <p class="mt-3 small" style="white-space: break-spaces; overflow: hidden;">
              <?php
                if (!function_exists('getYouTubeVideoId')) {
                  function getYouTubeVideoId($urlComment)
                  {
                    $videoId = '';
                    $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
                    if (preg_match($pattern, $urlComment, $matches)) {
                      $videoId = $matches[1];
                    }
                    return $videoId;
                  }
                }

                $commentText = isset($comment['comment']) ? $comment['comment'] : '';

                if (!empty($commentText)) {
                  $paragraphs = explode("\n", $commentText);

                  foreach ($paragraphs as $index => $paragraph) {
                    $messageTextWithoutTags = strip_tags($paragraph);
                    $pattern = '/\bhttps?:\/\/\S+/i';

                    $formattedText = preg_replace_callback($pattern, function ($matches) {
                      $urlComment = htmlspecialchars($matches[0]);

                      if (preg_match('/\.(png|jpg|jpeg|webp)$/i', $urlComment)) {
                        return '<a href="' . $urlComment . '" target="_blank"><img class="w-100 h-100 rounded-4 lazy-load" loading="lazy" data-src="' . $urlComment . '" alt="Image"></a>';
                      } elseif (strpos($urlComment, 'youtube.com') !== false) {
                        $videoId = getYouTubeVideoId($urlComment);
                        if ($videoId) {
                          $thumbnailUrl = 'https://img.youtube.com/vi/' . $videoId . '/default.jpg';
                          return '<div class="w-100 overflow-hidden position-relative ratio ratio-16x9"><iframe loading="lazy" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" class="rounded-4 position-absolute top-0 bottom-0 start-0 end-0 w-100 h-100 border-0 shadow" src="https://www.youtube.com/embed/' . $videoId . '" frameborder="0" allowfullscreen></iframe></div>';
                        } else {
                          return '<a href="' . $urlComment . '">' . $urlComment . '</a>';
                        }
                      } else {
                        return '<a href="' . $urlComment . '">' . $urlComment . '</a>';
                      }
                    }, $messageTextWithoutTags);
                
                    echo "<p class='small' style=\"white-space: break-spaces; overflow: hidden;\">$formattedText</p>";
                  }
                } else {
                  echo "Sorry, no text...";
                }
              ?>
            </p>
          </div>
          <div class="m-2 ms-auto">
            <a class="btn btn-sm fw-semibold" href="reply_forum.php?comment_id=<?php echo $comment['id']; ?>"><i class="bi bi-reply-fill"></i> Reply</a>
          </div>
        </div>
      <?php
        endwhile;
      ?>
      <?php
        $totalPages = ceil($total_items / $items_per_page);
        $prevPage = $page - 1;
        $nextPage = $page + 1;
      ?>
      <div class="pagination d-flex gap-1 justify-content-center mt-3">
        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
        <?php endif; ?>

        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
        <?php endif; ?>

        <?php
          // Calculate the range of page numbers to display
          $startPage = max($page - 2, 1);
          $endPage = min($page + 2, $totalPages);

          // Display page numbers within the range
          for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i === $page) {
              echo '<span class="btn btn-sm btn-primary active fw-bold">' . $i . '</span>';
            } else {
              echo '<a class="btn btn-sm btn-primary fw-bold" href="?page=' . $i . '">' . $i . '</a>';
            }
          }
        ?>

        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
        <?php endif; ?>
      </div>
      <nav class="navbar fixed-bottom navbar-expand justify-content-center">
        <div class="container-fluid">
          <button type="button" class="w-100 btn btn-primary fw-bold rounded-3" data-bs-toggle="modal" data-bs-target="#forum">send your message</button>
        </div>
      </nav>
    </div>
    <div class="modal fade" id="forum" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Type something else...</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div>
            <form class="form-control border-0" action="" method="POST">
              <textarea type="text" class="form-control fw-bold rounded-3 mb-2" style="height: 200px; max-height: 800px;" name="comment" placeholder="Type something..." aria-label="Type a message..." aria-describedby="basic-addon2" 
                onkeydown="if(event.keyCode == 13) { this.style.height = (parseInt(this.style.height) + 10) + 'px'; return true; }"
                onkeyup="this.style.height = '40px'; var newHeight = (this.scrollHeight + 10 * (this.value.split(/\r?\n/).length - 1)) + 'px'; if (parseInt(newHeight) > 800) { this.style.height = '800px'; } else { this.style.height = newHeight; }" required></textarea>
              <button class="w-100 btn btn-primary rounded-3" type="submit"><i class="bi bi-send-fill"></i></button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <?php include('header.php'); ?>
    <style>
      .text-stroke {
        -webkit-text-stroke: 1px;
      }
    </style> 
    <script>
      function goBack() {
        window.location.href = "/";
      }
    </script>
    <script>
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      let imageContainer = document.getElementById("image-container");

      // Set the default placeholder image
      const defaultPlaceholder = "icon/bg.png";

      if ("IntersectionObserver" in window) {
        let imageObserver = new IntersectionObserver(function(entries, observer) {
          entries.forEach(function(entry) {
            if (entry.isIntersecting) {
              let image = entry.target;
              image.src = image.dataset.src;
              imageObserver.unobserve(image);
            }
          });
        });

        lazyloadImages.forEach(function(image) {
          image.src = defaultPlaceholder; // Apply default placeholder
          imageObserver.observe(image);
          image.style.filter = "blur(5px)"; // Apply initial blur to all images

          // Remove blur and apply custom blur to NSFW images after they load
          image.addEventListener("load", function() {
            image.style.filter = ""; // Remove initial blur
            if (image.classList.contains("nsfw")) {
              image.style.filter = "blur(4px)"; // Apply blur to NSFW images
          
              // Add overlay with icon and text
              let overlay = document.createElement("div");
              overlay.classList.add("overlay", "rounded");
              let icon = document.createElement("i");
              icon.classList.add("bi", "bi-eye-slash-fill", "text-white");
              overlay.appendChild(icon);
              let text = document.createElement("span");
              text.textContent = "R-18";
              text.classList.add("shadowed-text", "fw-bold", "text-white");
              overlay.appendChild(text);
              image.parentNode.appendChild(overlay);
            }
          });
        });
      } else {
        let lazyloadThrottleTimeout;

        function lazyload() {
          if (lazyloadThrottleTimeout) {
            clearTimeout(lazyloadThrottleTimeout);
          }
          lazyloadThrottleTimeout = setTimeout(function() {
            let scrollTop = window.pageYOffset;
            lazyloadImages.forEach(function(img) {
              if (img.offsetTop < window.innerHeight + scrollTop) {
                img.src = img.dataset.src;
                img.classList.remove("lazy-load");
              }
            });
            lazyloadImages = Array.from(lazyloadImages).filter(function(image) {
              return image.classList.contains("lazy-load");
            });
            if (lazyloadImages.length === 0) {
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

      // Infinite scrolling
      let loading = false;

      function loadMoreImages() {
        if (loading) return;
        loading = true;

        // Simulate loading delay for demo purposes
        setTimeout(function() {
          for (let i = 0; i < 10; i++) {
            if (lazyloadImages.length === 0) {
              break;
            }
            let image = lazyloadImages[0];
            imageContainer.appendChild(image);
            lazyloadImages = Array.from(lazyloadImages).slice(1);
          }
          loading = false;
        }, 1000);
      }

      window.addEventListener("scroll", function() {
        if (window.innerHeight + window.scrollY >= imageContainer.clientHeight) {
          loadMoreImages();
        }
      });

      // Initial loading
      loadMoreImages();
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>