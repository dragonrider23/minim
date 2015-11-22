<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?></title>
  <base href="<?= $basepath ?>">
  <link rel="stylesheet" type="text/css" href="styles/style.css">
</head>

<body>
    <div class="header">
      <div class="logo"><a href="."><?= $sitename ?></a></div>
      <ul class="menu">
        <?= $menu ?>
      </ul>
    </div>
    <div class="main">
        <?php if ($page->getType() === 'article'): ?>
        <div class="article"><h2 class="articletitle"><?=$page->getMetadata('title')?></h2><div class="articleinfo">by <?=$page->getMetadata('author')?>, on <?=$page->getMetadata('date')?></div>
        <?php else: ?>
        <div class="page">
        <?php endif; ?>
            <?= $page->render() ?>
        </div>
    </div>
    <div class="footer">
      <div class="left"><a href="">&copy; <?= date("Y") ?> <?= $sitename ?></a></div>
      <div class="right">Powered by Minim CMS</a>.</div>
    </div>
</body>
</html>
