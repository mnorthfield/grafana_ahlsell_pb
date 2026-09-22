<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="Content-Style-Type" content="text/css">
        <meta http-equiv="Content-Script-Type" content="text/javascript">

        <title>Phasebox portal</title>
        
        <link rel="stylesheet" href="css/ui.tabs.css" type="text/css" media="print, projection, screen"/>
        <link rel="stylesheet" href="css/base.css" type="text/css"/>

        <script src="js/jquery-1.2.6.js" type="text/javascript"></script>
        <script src="js/ui.core.js" type="text/javascript"></script>
        <script src="js/ui.tabs.js" type="text/javascript"></script>
        <script type="text/javascript">
            $(function() {
                $('#container-1 > ul').tabs({ fx: { opacity: 'toggle'} });
            });
        </script>
    </head>

    <body>
        <h1>Phasebox</h1>
        
        <div id="container-1">
            <ul>
                <li><a href="#cp"><span>Control Panel</span></a></li>
            </ul>

            <div id="cp">
                <div class="fragment-content">
                    <div>
                        <a href="https://pb-grafana.dev.wendelit.se/" target="_blank">
                            <img style="width: 70px"
                        src="images/Grafana_logo.svg.png"/>Grafana</a>
                    </div>
                    <div>
                        <a href="https://pb-influx.dev.wendelit.se/" target="_blank"><img
                        src="images/marketplace_icons_13_.webp" style="width: 70px"/>Influx DB</a>
                        </br>
                        User: </br>
                        Password: 
                    </div>
                    <div>
                        <a href="https://pb-code-server.dev.wendelit.se/" target="_blank"><img
                        src="images/Visual_Studio_Code_1.35_icon.svg.png" style="width: 70px"/>code-server</a>
                        </br>
                        Password: 442c4df440792616a87a1636
                    </div>
                    <div></div>
                    <div></div>
                    <h2>Phasebox info</h2>
                    <ul>
                        <li>
                          Skriptet körs 1ggr varannan timme och väntar 10 sekunder mellan varje anrop för att undvika att vi gör ahlsell sura.
                        </li>
                        <li>
                          Lägg till artikel för att tracka: Gå till code server och lägg till artikelnummret under "artNr.yaml".
                        </li>
                        <li>
                          Köra manuellt: gå in i code-server öppna terminalen och skriv "php getInfo.php"
                        </li>
                    </ul>
                    
                    <h2>Resources and references</h2>
                    <ul>

                        <li>
                          <a href="/phpinfo.php">NGINX PHP information</a>
                          (to disable: rm /var/www/phpinfo.php)
                        </li>
                        <li><a href="https://www.turnkeylinux.org/nginx-php-fastcgi">TurnKey appliance release notes</a></li>
                    </ul>
                    
                </div>
            </div>

        </div>
    </body>
</html>
