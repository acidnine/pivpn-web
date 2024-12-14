<?php
require_once 'app/auth.php';
function convert_seconds($seconds){
    // Create DateTime objects representing the start and end timestamps
    $dt1 = new DateTime("@0");
    $dt2 = new DateTime("@$seconds");
    // Calculate the difference between the two timestamps
    $diff = $dt1->diff($dt2);
    // Format the difference to display days, hours, minutes, and seconds
    if($diff->format('%a') != '0'){
        $ret = $diff->format('%a days, %h hours, %i minutes and %s seconds ago');
    }elseif($diff->format('%h') != '0'){
        $ret = $diff->format('%h hours, %i minutes and %s seconds ago');
    }elseif($diff->format('%i') != '0'){
        $ret = $diff->format('%i minutes and %s seconds ago');
    }else{
        $ret = $diff->format('%s seconds ago');
    }
    return $ret;
}
function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB');/// dividing by 1024 so MiB not MB
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    // Uncomment one of the following alternatives
    $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) .' '. $units[$pow];
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>PiVPN</title>
    <link type="text/css" rel="stylesheet" href="css/index.css">
    <link rel="icon" type="image/png" sizes="64x64" href="img/pivpnlogo64.png">
</head>
<body>
    <p id="result"></p>
    <script src="js/mobile.js"></script>
    <script src="js/checkName.js"></script>
    <nav>
        <a href="index.php"><img alt="PiVPN Logo" src="img/pivpnlogo64.png" width="32"></a>
        <?php
        $install_check = shell_exec("openvpn --version");
        if($install_check){
        ?>
        <h1>PiVPN <span class="version">(OpenVPN v<?=shell_exec("openvpn --version | awk '/OpenVPN/ {print $2\")\"}' | head -n 1")?></span></h1>
        <?php }else{ ?>
        <h1>PiVPN <span class="version">(Wireguard <?=shell_exec("wg --version | awk '/ v/ {print $2\")\"}'")?></span></h1>
        <?php } ?>
        <button class="reload" onClick="window.location.reload();">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path d="M13.5 2c-5.621 0-10.211 4.443-10.475 10h-3.025l5 6.625 5-6.625h-2.975c.257-3.351 3.06-6 6.475-6 3.584 0 6.5 2.916 6.5 6.5s-2.916 6.5-6.5 6.5c-1.863 0-3.542-.793-4.728-2.053l-2.427 3.216c1.877 1.754 4.389 2.837 7.155 2.837 5.79 0 10.5-4.71 10.5-10.5s-4.71-10.5-10.5-10.5z"/>
            </svg>
        </button>
        <form action="index.php" method="POST">
            <button class="logout" type="submit" name="logout">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
            </button>
        </form>
    </nav>
    <div class="container">
        <?php
        $output = shell_exec("/bin/sh clients.sh");
        if($install_check){
            echo $output;
        }else{
            // RECEIVE WIREGUARD JSON FROM clients.sh
            $wg_json = json_decode( $output, TRUE );
            // EXAMPLE JSON: Object(stdClass)#5 (1) { ["wg0"]=> object(stdClass)#1 (4) { ["privateKey"]=> string(44) "RANDOM=" ["publicKey"]=> string(44) "RANDOM=" ["listenPort"]=> int(51820) ["peers"]=> object(stdClass)#3 (2) { ["RANDOM1="]=> object(stdClass)#2 (2) { ["presharedKey"]=> string(44) "RANDOM=" ["allowedIps"]=> array(1) { [0]=> string(15) "10.241.255.2/32" } } ["RANDOM2="]=> object(stdClass)#4 (6) { ["presharedKey"]=> string(44) "RANDOM=" ["endpoint"]=> string(17) "192.168.0.1:41689" ["latestHandshake"]=> int(1734154901) ["transferRx"]=> int(1314012) ["transferTx"]=> int(12197316) ["allowedIps"]=> array(1) { [0]=> string(15) "10.241.255.3/32" } } } } }
            echo "<table><tr><th class='border-bottom' colspan='7'>Clients</th><th class='border-bottom'><button id='show-form' style='float:right' class='custom-btn-new btn'> + New </button></th></tr>";
            echo "<tr><td>";
            foreach($wg_json as $wg_id => $wg_connector){
                //var_dump($wg_connector);
                echo '<br><b>'.$wg_id.'</b><br>';
                foreach($wg_connector['peers'] as $wg_peer_id => $wg_peer){
                    echo $wg_peer_id.'<br>';
                    //var_dump($wg_peer);
                    if(array_key_exists('endpoint',$wg_peer)){
                        echo 'Endpoint: '.$wg_peer['endpoint'].'<br>';
                        echo 'Last Seen: '.date("Y-m-d H:i:s", $wg_peer['latestHandshake']).' - '.convert_seconds(time()-$wg_peer['latestHandshake']).'<br>';
                        echo 'Received: '.formatBytes($wg_peer['transferRx']).'<br>';
                        echo 'Sent: '.formatBytes($wg_peer['transferTx']).'<br>';
                        echo 'Allowed IPs: '.implode(', ',$wg_peer['allowedIps']).'<br>';
                    }
                    echo '<br><br>';
                }
            }
            echo "</td></tr></table>";
        }
        ?>
        <p>
            <a target="_blank" href="https://github.com/g8998/pivpn-web">GITHUB</a>
            <a> · </a>
            <a target="_blank" href="https://pivpn.io">PiVPN Project</a>
            <a> · </a>
            <a target="_blank" href="https://www.buymeacoffee.com/g8998">Donate</a>
        </p>
    </div>
    <div id="popup-overlay" class="hidden"></div>
    <div id="popup" class="hidden">
        <h3>New Client</h3>
        <form onsubmit="return checkName()" action="app/new.php" method="POST" id="form">
            <input type="text" id="name" name="name" placeholder="Name" required><br>
            <input type="number" id="days" name="days" min="1" max="3650" placeholder="Days" required><br>
            <input type="password" id="password" name="password" placeholder="Password (Optional)"><br>
            <button class="close" id="close-form">Close</button>
            <input class="submit" type="submit" value="Submit">
        </form>
    </div>
    <script src="js/show-form.js"></script>
</body>
</html>
