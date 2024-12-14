<?php
require_once 'auth.php';
?>

<?php
$file = $_GET['file'];
config_file="/etc/pivpn/openvpn/setupVars.conf"
if [ -f "$config_file" ]; then
    $iuser = exec("sudo cat /etc/pivpn/openvpn/setupVars.conf | grep install_user | sed 's/install_user=//'");
    $filePath = '/home/'.$iuser.'/ovpns/' . $file;
else
    $iuser = exec("sudo cat /etc/pivpn/wireguard/setupVars.conf | grep install_user | sed 's/install_user=//'");
    $filePath = '/home/'.$iuser.'/configs/' . $file;
fi

if (file_exists($filePath) && is_readable($filePath)) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file . '"');

    readfile($filePath);
} else {
    $logFilePath = '../logs/error.log';
    $errorMessage = 'Error downloading config file: The file '.$file.' does not exist or cannot be read.';

    file_put_contents($logFilePath, date('Y-m-d H:i:s') . ' - ' . $errorMessage . "\n", FILE_APPEND);

    echo $errorMessage;
}
?>
