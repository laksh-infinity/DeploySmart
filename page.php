<?php
require 'assets/header.php';

$pageFile = 'content/page.json';
$blocks = [];

if (file_exists($pageFile)) {
    $json = file_get_contents($pageFile);
    $blocks = json_decode($json, true) ?? [];
}
?>

<div class="content">
  <?php foreach ($blocks as $block): ?>
    <?php if ($block['type'] === 'hero'): ?>
	  <div class="hero-full <?= htmlspecialchars($block['class'] ?? '') ?>" style="background: url('<?= htmlspecialchars($block['image'] ?? '') ?>') center/cover no-repeat;">
	    <div class="hero-inner">
	      <?= $block['html'] ?? '' ?>
	      <?php if (!empty($block['css'])): ?>
	        <style><?= $block['css'] ?></style>
	      <?php endif; ?>
	    </div>
	  </div>
    <?php elseif ($block['type'] === 'text'): ?>
      <div class="text-block">
        <p><?= nl2br(htmlspecialchars($block['content'])) ?></p>
      </div>
    <?php elseif ($block['type'] === 'image'): ?>
      <div class="image-block">
        <img src="<?= htmlspecialchars($block['src']) ?>" alt="<?= htmlspecialchars($block['caption']) ?>" style="max-width:100%;">
        <p class="caption"><?= htmlspecialchars($block['caption']) ?></p>
      </div>
    <?php elseif ($block['type'] === 'code'): ?>
      <pre><code><?= htmlspecialchars($block['code']) ?></code></pre>
	<?php elseif ($block['type'] === 'info'): ?>
	  <div class="info-block <?= htmlspecialchars($block['class'] ?? '') ?>">
	    <i class="icon <?= htmlspecialchars($block['icon'] ?? '') ?>"></i>
	    <h3><?= htmlspecialchars($block['title'] ?? '') ?></h3>
	    <p><?= htmlspecialchars($block['description'] ?? '') ?></p>
	  </div>
    <?php endif; ?>
  <?php endforeach; ?>
</div>

<?php require 'assets/footer.php'; ?>