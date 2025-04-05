<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?> - <?= htmlspecialchars($site_title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.4/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-DQvkBjpPgn7RC31MCQoOeC9TI2kdqa4+BSgNMNj8v77fdC77Kj5zpWFTJaaAoMbC" crossorigin="anonymous">
  <link rel="stylesheet" href="assets/style.css">
  
  <!-- Add Font Awesome for sponsor page icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Add Highlight.js CSS (choose a theme, e.g., default) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/default.min.css">
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
    <nav class="bg-light nav-pills">
      <ul class="nav flex-column p-3">
        <?= $menu_html ?>
      </ul>
    </nav>
  </div>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar for large screens with fixed position elements -->
      <nav class="col-lg-3 d-none d-lg-block bg-light nav-pills position-relative min-vh-100">
        <!-- Sidebar header -->
        <div class="sticky-top bg-light">
          <div class="sidebar-brand py-3 border-bottom">
            <a href="index.html" class="text-decoration-none text-dark fw-bold fs-4"><?= htmlspecialchars($site_title) ?></a>
          </div>
          <!-- Menu section - should scroll independently -->
          <div style="max-height: calc(100vh - 150px); overflow-y: auto;">
            <ul class="nav flex-column py-3">
              <?= $menu_html ?>
            </ul>
          </div>
        </div>
        <!-- Footer with fixed position at bottom -->
        <div class="d-flex flex-column bg-dark text-white p-3 d-lg-block position-fixed bottom-0" style="width: calc(25% - 24px); max-width: inherit;">
          <ul class="list-unstyled">
            <li><?= htmlspecialchars($site_title) ?> by <a href="https://speculoos.world" class="text-white" target="_blank">Speculoos World</a></li>
            <li>GitHub: <nobr><a href="https://github.com/<?= htmlspecialchars($github_user) ?>/<?= htmlspecialchars($repo) ?>" target="_blank" class="text-white"><?= htmlspecialchars($github_user) ?>/<?= htmlspecialchars($repo) ?></a></nobr></li>
            <li>License: <a href="https://www.gnu.org/licenses/agpl-3.0.en.html target="_blank" class="text-white text-decoration-underline">AGPLv3</a></li>
          </ul>
        </div>
      </nav>
      <main class="col-lg-9 p-3">
        <?= $content ?>
      </main>
    </div>
  </div>
  <!-- Mobile footer: shown only on mobile below the whole page -->
  <footer class="bg-dark text-white p-3 d-lg-none">
    <ul class="list-unstyled">
      <li><?= htmlspecialchars($site_title) ?> by <a href="https://speculoos.world" class="text-white" target="_blank">Speculoos World</a></li>
      <li>GitHub: <nobr><a href="https://github.com/<?= htmlspecialchars($github_user) ?>/<?= htmlspecialchars($repo) ?>" target="_blank" class="text-white"><?= htmlspecialchars($github_user) ?>/<?= htmlspecialchars($repo) ?></a></nobr></li>
      <li>License: <a href="https://www.gnu.org/licenses/agpl-3.0.en.html target="_blank" class="text-white text-decoration-underline">AGPLv3</a></li>
    </ul>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Add Highlight.js JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
  <script>
    hljs.highlightAll();
  </script>
</body>
</html>
