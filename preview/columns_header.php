    <div class="btn-group w-100 gap-2 my-3 container-fluid overflow-auto pt-3 pb-4">
      <a class="btn bg-body-tertiary p-4 fw-bold w-50 rounded-4 shadow <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'tags/') !== false) echo 'opacity-75 shadow'; ?>" href="/preview/tags/"><i class="bi bi-tags-fill"></i> Tags</a>
      <a class="btn bg-body-tertiary p-4 fw-bold w-50 rounded-4 shadow <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'characters/') !== false) echo 'opacity-75 shadow'; ?>" href="/preview/characters/"><i class="bi bi-people-fill"></i> Characters</a>
      <a class="btn bg-body-tertiary p-4 fw-bold w-50 rounded-4 shadow <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'parodies/') !== false) echo 'opacity-75 shadow'; ?>" href="/preview/parodies/"><i class="bi bi-journals"></i> Parodies</a>
      <a class="btn bg-body-tertiary p-4 fw-bold w-50 rounded-4 shadow <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'groups/') !== false) echo 'opacity-75 shadow'; ?>" href="/preview/groups/"><i class="bi bi-person-fill"></i> Groups</a>
      <a class="btn bg-body-tertiary p-4 fw-bold w-50 rounded-4 shadow <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'users/') !== false) echo 'opacity-75 shadow'; ?>" href="/preview/users/"><i class="bi bi-person-circle"></i> Users</a>
    </div> 
