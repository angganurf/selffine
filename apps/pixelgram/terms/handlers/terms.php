<?php 
$tpage = (!empty($_GET['page'])) ? $_GET['page'] : 'about-us';
$pname = 'about_us';
if ($tpage == 'terms-of-use') {
	$pname = 'terms_of_use';
}

elseif ($tpage == 'privacy-and-policy') {
	$pname = 'privacy_and_policy';
}

elseif ($tpage == 'contact_us') {
	$pname = 'contact_us';
}

$terms_pages = array('terms_of_use' => 'terms_of_use_page',
                     'about_us' => 'about_page',
                     'privacy_and_policy' => 'privacy_policy_page');
if (in_array($pname, array_keys($terms_pages)) && $context[$terms_pages[$pname]] == 1) {
	$pagecont = htmlspecialchars_decode(lang($terms_pages[$pname]));
}
else{
	if ($pname != 'contact_us') {
		header("Location: $site_url/404");
		exit();
	}
	else{
		$pagecont = $pixelphoto->PX_LoadPage('terms/templates/terms/pages/contact_us');
	}
}

$context['page_link'] = $tpage;
$context['pname'] = $pname;
$context['tpage'] = $tpage;
$context['page_title'] = lang($pname);
$context['pagecont'] = $pagecont;
$context['app_name'] = 'terms';
$context['xhr_url'] = "$site_url/aj/main";
$context['content'] = $pixelphoto->PX_LoadPage('terms/templates/terms/index');
