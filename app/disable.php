<?php
require_once 'auth.php';
?>

<?php
$file = $_GET['file'];

config_file="/etc/pivpn/openvpn/setupVars.conf"
if [ -f "$config_file" ]; then
    $iuser = exec("sudo cat /etc/pivpn/openvpn/setupVars.conf | grep install_user | sed 's/install_user=//'");
    $command = "ip=\$(sudo sed -n 's/ifconfig-push \(.*\) .*/\\1/p' /etc/openvpn/ccd/".$file.") && sudo sed -i \"1s/.*/ifconfig-push 0.0.0.1 255.255.255.0/\" /etc/openvpn/ccd/".$file." && sudo sed -i \"\$ a\\#\$ip\" /etc/openvpn/ccd/".$file;
else
    $iuser = exec("sudo cat /etc/pivpn/wireguard/setupVars.conf | grep install_user | sed 's/install_user=//'");
    $command = "ip=\$(sudo sed -n 's/ifconfig-push \(.*\) .*/\\1/p' /etc/wireguard/".$file.") && sudo sed -i \"1s/.*/ifconfig-push 0.0.0.1 255.255.255.0/\" /etc/wireguard/".$file." && sudo sed -i \"\$ a\\#\$ip\" /etc/wireguard/".$file;
fi

$output = shell_exec($command);

if ($output == null) {
    ob_start();
    include_once 'index.php';
    $currentPath = rtrim(dirname($_SERVER['PHP_SELF']), '/');
    header("Refresh:0, URL=$currentPath/../index.php");
} else {
    $logFilePath = '../logs/error.log';
    $username = exec('whoami');
    $errorMessage = 'Error disabling user: Check if the file '.$file.' exists in the directory openvpn: "/etc/openvpn/ccd/" or wireguard: "/home/'.$iuser.'/configs/" or if it has the necessary permissions.';
    
    file_put_contents($logFilePath, date('Y-m-d H:i:s') . ' - ' . $errorMessage . "\n", FILE_APPEND);
    
    echo $errorMessage;
}

ob_end_clean();
?>
