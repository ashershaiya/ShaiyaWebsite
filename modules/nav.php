<?php
// $active_page should be set before including this file
// Valid values: 'news', 'register', 'download', 'rankings', 'guilds', 'donate', 'rules',
//              'info', 'bosses', 'drops', 'enchant', 'account', 'characters',
//              'change_password', 'admin'
if (!isset($active_page))
    $active_page = '';

// Helper to add 'active' class
function nav_active($page)
{
    global $active_page;
    return ($active_page === $page) ? ' class="active"' : '';
}

// Rankings dropdown pages
$ranking_pages = ['rankings', 'guilds'];
$ranking_active = in_array($active_page, $ranking_pages);
?>
<nav class="main-nav">
    <ul>
        <li><a href="index.php" <?php echo nav_active('news'); ?>>News</a></li>
        <li><a href="info.php" <?php echo nav_active('info'); ?>>Server Info</a></li>
        <li><a href="drops.php" <?php echo nav_active('drops'); ?>>Drop List</a></li>
        <li><a href="rankings.php" <?php echo $ranking_active ? ' class="active"' : ''; ?>>Ranks</a></li>
        <li><a href="bosses.php" <?php echo nav_active('bosses'); ?>>BOSSES</a></li>
        <li><a href="donate.php" <?php echo nav_active('donate'); ?>>Donate</a></li>
        <li><a href="rules.php" <?php echo nav_active('rules'); ?>>Rules</a></li>
        <li><a href="download.php" <?php echo nav_active('download'); ?>>Download</a></li>
    </ul>
</nav>