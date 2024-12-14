<?php
require_once 'auth.php';
?>

<?php
$file = $_GET['file'];

config_file="/etc/pivpn/openvpn/setupVars.conf"
if [ -f "$config_file" ]; then
    $command = "ip=\$(sudo sed -n '2s/#\\([0-9.]*\\)/\\1/p' /etc/openvpn/ccd/".$file.") && sudo sed -i -e \"1s/\\(ifconfig-push \\)[0-9.]*\\( .*\\)/\\1\$ip\\2/\" -e '2d' /etc/openvpn/ccd/".$file;
else
    $command = "ip=\$(sudo sed -n '2s/#\\([0-9.]*\\)/\\1/p' /etc/wireguard/".$file.") && sudo sed -i -e \"1s/\\(ifconfig-push \\)[0-9.]*\\( .*\\)/\\1\$ip\\2/\" -e '2d' /etc/wireguard/".$file;
fi
$output = shell_exec($command);

if ($output == null) {
    ob_start();
    include_once 'index.php';
    $currentPath = rtrim(dirname($_SERVER['PHP_SELF']), '/');
    header("Refresh: 0; URL=$currentPath/../index.php");
} else {
    $logFilePath = '../logs/error.log';
    $errorMessage = 'Error enabling user: Check if the file '.$file.' exists in the directory openvpn: "/etc/openvpn/ccd/" or wireguard: "/home/'.$iuser.'/configs/" or if it has the necessary permissions.';
    file_put_contents($logFilePath, date('Y-m-d H:i:s') . ' - ' . $errorMessage . "\n", FILE_APPEND);

    echo $errorMessage;
}

ob_end_clean();
?>
