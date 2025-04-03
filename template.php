<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <nav>
    <ul>
      <?= $menu_html ?>
    </ul>
  </nav>
  <main>
    <?= $content ?>
  </main>
</body>
</html>