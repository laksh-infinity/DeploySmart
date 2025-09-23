<?php
require '../assets/auth.php';
include('../assets/header.php');
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in and is an admin
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['is_admin'] != 1) {
    echo '<div class="content"><h2>Access Denied</h2><p>You do not have permission to view this page.</p></div>';
    exit;
}

// Load existing page content
$pageFile = '../content/page.json';
$blocks = [];

if (file_exists($pageFile)) {
    $json = file_get_contents($pageFile);
    $blocks = json_decode($json, true) ?? [];
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
  <title>Edit Page</title>
  <link rel="stylesheet" href="../assets/style.css">
  <link rel="stylesheet" href="pagebuilder.css">
</head>
<body>
<div class="content">
  <h2>Edit Page Content</h2>

  <div id="page-builder">
    <?php foreach ($blocks as $index => $block): ?>
      <div class="block" data-type="<?= htmlspecialchars($block['type']) ?>">
        <input type="hidden" name="blocks[<?= $index ?>][type]" value="<?= htmlspecialchars($block['type']) ?>">

        <?php if ($block['type'] === 'hero'): ?>
          <div class="hero-block">
            <label>Hero Image URL:</label>
            <input type="text" name="blocks[<?= $index ?>][image]" value="<?= htmlspecialchars($block['image'] ?? '') ?>">

            <label>Custom HTML:</label>
            <textarea name="blocks[<?= $index ?>][html]"><?= htmlspecialchars($block['html'] ?? '') ?></textarea>

            <label>Custom CSS:</label>
            <textarea name="blocks[<?= $index ?>][css]"><?= htmlspecialchars($block['css'] ?? '') ?></textarea>

            <label>Block Class:</label>
            <input type="text" name="blocks[<?= $index ?>][class]" value="<?= htmlspecialchars($block['class'] ?? '') ?>">
          </div>
        <?php elseif ($block['type'] === 'text'): ?>
          <div class="text-block">
            <label>Text Content:</label>
            <textarea name="blocks[<?= $index ?>][content]"><?= htmlspecialchars($block['content'] ?? '') ?></textarea>

            <label>Custom CSS:</label>
            <textarea name="blocks[<?= $index ?>][css]"><?= htmlspecialchars($block['css'] ?? '') ?></textarea>

            <label>Block Class:</label>
            <input type="text" name="blocks[<?= $index ?>][class]" value="<?= htmlspecialchars($block['class'] ?? '') ?>">
          </div>
        <?php elseif ($block['type'] === 'image'): ?>
          <div class="image-block">
            <label>Image URL:</label>
            <input type="text" name="blocks[<?= $index ?>][src]" value="<?= htmlspecialchars($block['src'] ?? '') ?>">

            <label>Caption:</label>
            <input type="text" name="blocks[<?= $index ?>][caption]" value="<?= htmlspecialchars($block['caption'] ?? '') ?>">

            <label>Custom CSS:</label>
            <textarea name="blocks[<?= $index ?>][css]"><?= htmlspecialchars($block['css'] ?? '') ?></textarea>

            <label>Block Class:</label>
            <input type="text" name="blocks[<?= $index ?>][class]" value="<?= htmlspecialchars($block['class'] ?? '') ?>">
          </div>
        <?php elseif ($block['type'] === 'code'): ?>
          <div class="code-block">
            <label>Code Snippet:</label>
            <textarea name="blocks[<?= $index ?>][code]"><?= htmlspecialchars($block['code'] ?? '') ?></textarea>

            <label>Custom CSS:</label>
            <textarea name="blocks[<?= $index ?>][css]"><?= htmlspecialchars($block['css'] ?? '') ?></textarea>

            <label>Block Class:</label>
            <input type="text" name="blocks[<?= $index ?>][class]" value="<?= htmlspecialchars($block['class'] ?? '') ?>">
          </div>
        <?php endif; ?>

        <button class="remove-block">🗑️ Remove Block</button>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="block-controls">
    <button onclick="addBlock('hero')">➕ Add Hero Block</button>
    <button onclick="addBlock('text')">➕ Add Text Block</button>
    <button onclick="addBlock('image')">➕ Add Image Block</button>
    <button onclick="addBlock('code')">➕ Add Code Block</button>
  </div>

  <form method="POST" action="save_page.php" onsubmit="return prepareSave()">
    <input type="hidden" name="page_data" id="page-data">
    <button type="submit">💾 Save Page</button>
  </form>
</div>

<script src="pagebuilder.js"></script>
</body>
<?php require '../assets/footer.php'; ?>
</html>