<?php
// Retrieve users from the database based on the search query and sorted by follower count
$query = "SELECT users.*, SUBSTR(users.artist, 1, 1) AS first_letter, COUNT(following.follower_email) AS follower_count FROM users LEFT JOIN following ON users.email = following.following_email ";
if (!empty($searchQuery)) {
  $query .= " WHERE artist LIKE '%$searchQuery%'";
}
$query .= "GROUP BY users.email ORDER BY follower_count DESC, first_letter COLLATE NOCASE DESC, users.artist COLLATE NOCASE ASC";

$users = $db->query($query);

// Group users by category
$groupedUsers = [];
while ($user = $users->fetchArray()) {
    $letter = strtoupper($user['first_letter']);
    $groupedUsers[$letter][] = $user;
}
?>

    <div class="container-fluid mt-2">
      <div class="container-fluid">
        <div class="row justify-content-center">
          <?php foreach ($groupedUsers as $group => $users) : ?>
            <div class="col-4 col-md-2 col-sm-5 px-0">
              <a class="btn btn-outline-dark border-0 fw-medium d-flex flex-column align-items-center" href="#category-<?php echo $group; ?>">
                <h6 class="fw-medium">Category</h6>
                <h6 class="fw-bold"><?php echo $group; ?></h6>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php foreach ($groupedUsers as $group => $users) : ?>
        <?php include('user_card.php'); ?>
      <?php endforeach; ?>
    </div>
    <div class="mt-5"></div>