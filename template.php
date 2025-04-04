<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?> - <?= htmlspecialchars($site_title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <!-- Updated Mobile navbar -->
  <nav class="navbar navbar-dark bg-primary d-lg-none">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.html"><?= htmlspecialchars($site_title) ?></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>
  </nav>
  <!-- Mobile menu collapse -->
  <div class="collapse d-lg-none" id="mobileMenu">
    <nav class="bg-light">
      <ul class="nav flex-column p-3">
        <?= $menu_html ?>
      </ul>
    </nav>
  </div>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar for large screens -->
      <nav class="col-lg-2 d-none d-lg-block bg-light">
        <div class="sidebar-brand p-3 border-bottom">
          <a href="index.html" class="text-decoration-none text-dark fw-bold fs-4"><?= htmlspecialchars($site_title) ?></a>
        </div>
        <ul class="nav flex-column p-3">
          <?= $menu_html ?>
        </ul>
      </nav>
      <main class="col-lg-10 p-3">
        <?= $content ?>
      </main>
    </div>
  </div>
  <footer class="bg-dark text-white p-3 mt-4">
    <div class="container d-flex justify-content-between align-items-center">
      <a href="index.html" class="text-white text-decoration-none"><?= htmlspecialchars($site_title) ?></a>
      <span>License <a href="https://www.gnu.org/licenses/agpl-3.0.en.html" class="text-white text-decoration-underline">AGPLv3</a></span>
      <a href="https://github.com/<?= htmlspecialchars($github_user) ?>/<?= htmlspecialchars($repo) ?>" class="text-white text-decoration-none">GitHub Repository</a>
    </div>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
