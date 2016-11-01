<? php
    header('Access-Control-Allow-Origin: *');

	$msg = $_SERVER['REQUEST_METHOD'];
	if (!isset($_POST['data']) {
		$msg += "-POST IS NOT SET";
	} elseif (empty($_POST['data'])) {
        $msg += "-POST IS EMPTY";
    } else {
        echo $msg json_encode($_POST);
    }
    echo $msg;
?>
