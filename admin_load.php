<?php
require_once('./sys/init.php');

if (IS_LOGGED == false || $user->isAdmin() == false) {
    header("Location: $site_url");
    exit();
}


if (!empty($_GET)) {
    foreach ($_GET as $key => $value) {
        if (!is_array($value)) {
            $value = preg_replace('/on[^<>=]+=[^<>]*/m', '', $value);
            $value = preg_replace('/\((.*?)\)/m', '', $value);
            $_GET[$key] = strip_tags($value);
        }
    }
}
if (!empty($_REQUEST)) {
    foreach ($_REQUEST as $key => $value) {
        if (!is_array($value)) {
            $value = preg_replace('/on[^<>=]+=[^<>]*/m', '', $value);
            $_REQUEST[$key] = strip_tags($value);
        }
    }
}
if (!empty($_POST)) {
    foreach ($_POST as $key => $value) {
        if (!is_array($value)) {
            $value = preg_replace('/on[^<>=]+=[^<>]*/m', '', $value);
            $_POST[$key] = strip_tags($value);
        }
    }
}
$path = (!empty($_GET['path'])) ? getPageFromPath($_GET['path']) : null;
// print_r($_GET['path']);
// print_r($path);
// exit();
$files = scandir('admin-panel/pages');
unset($files[0]);
unset($files[1]);
$page = 'dashboard';
if (!empty($path['page']) && in_array($path['page'], $files) && file_exists('admin-panel/pages/'.$path['page'].'/content.phtml')) {
    $page = $path['page'];
}
$data = array();
$admin = new Admin();
$text = $admin->loadPage($page.'/content');
?>
<input type="hidden" id="json-data" value='<?php echo htmlspecialchars(json_encode($data));?>'>
<?php
echo $text;
?>