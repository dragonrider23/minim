<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?></title>
  <base href="<?= $basepath ?>">
  <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
<div class="header">
  <div class="logo"><a href="."><?= $sitename ?></a></div>
  <ul class="menu">
    <?= renderMenu(); ?>
  </ul>
</div>
<div class="main">

<?php
if ($type === "article") {
  echo '<div class="article"><h2 class="articletitle">'.$pagemeta['title'].'</h2><div class="articleinfo">by '.$pagemeta['author'].', on '.$pagemeta['date'].'</div>';
} else {
  echo '<div class="page">';
}
echo renderContent($pagemeta['type'], $content);
echo '</div>';
?>

</div>
<div class="footer">
  <div class="left"><a href="">&copy; 2015 <?php echo $sitename?></a></div>
  <div class="right">Powered by Minim CMS</a>.</div>
</div>
</body>
</html>
