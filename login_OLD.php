<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1"> 
        <link rel="shortcut icon" href="favicon.ico" >
        <link rel="stylesheet" href="css/style.css?version=1" />

        <script src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
        <script src="http://code.jquery.com/mobile/1.2.1/jquery.mobile-1.2.1.min.js"></script>
        <link rel="stylesheet" href="http://code.jquery.com/mobile/1.2.1/jquery.mobile-1.2.1.min.css" />

        <script type="text/javascript" src="js/sha512.js"></script>
        <script type="text/javascript" src="js/forms.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {

            <?php
            if (isset($_GET['error'])) {
                echo ' $("#errorText").append("<b>Inloggningen misslyckades!</b>");';
            }
            if(isset($_GET['reg'])){
                  echo ' $("#newUser").append("<b>Din användare har skapats, välkommen att logga in!</b>");';
            }
            ?>
            });
        </script>

    </head> 
    <body>
        <div data-role="page" id="login"> 
            <div data-theme="b" data-role="header" data-id="persistent" data-position="fixed"> 
                <h1>BeachTime</h1> 

            </div> 


            <div data-role="content">	
                <div class="logRegDiv">
                    <p id="errorText"></p>
                    <form class="logInner" data-ajax="false" name="loginForm" action="http://www.beachtime.se/testenv/process_login.php" method="post" onsubmit="formhash(document.forms['loginForm'], document.forms['loginForm'].password);" name="login_form">

                        <input placeholder="Email" type="text" name="email" /> 
                        <input placeholder="Lösenord" type="password" name="password" id="password"/><br />

                        <a type="button" value="Registrera" href="#register">Registrera</a>
                        <input data-theme="b" class="logButton"  type="submit" action="submit" value="Logga in" />

                    </form>
                    <p  id="newUser"></p>
                </div>

            </div><!-- /content -->

            <div data-theme="d" data-role="footer"  data-id="persistent" data-position="fixed"> 
                <h4>Inloggning</h4> 
            </div> 
        </div>
        <div data-role="page" id="register"> 
            <div data-theme="b" data-role="header" data-id="persistent" data-position="fixed"> 
                <h1>BeachTime</h1> 

            </div> 


            <div data-role="content">	
                <div class="logRegDiv">
                    <form data-ajax="false"  class="regInner" name="registerForm" action="http://www.beachtime.se/testenv/register.php" method="post" onsubmit="formhash(document.forms['registerForm'], document.forms['registerForm'].password);">
                        <input placeholder="Användarnamn" type="text" name="user" />
                        <input placeholder="Email" type="text" name="email" />
                        <input type="text" name="fakeN" style="display:none">
                        <input type="password" name="fakeP" style="display:none">
                        <input placeholder="Lösenord" type="password" name="password" id="password"/><br />

                        <a type="button" value="Avbryt" href="#login">Avbryt</a>
                        <input data-theme="b" class="logButton"  type="submit" action="submit" value="Skapa användare" />
                    </form>
                </div>
            </div><!-- /content -->

            <div data-theme="d" data-role="footer"  data-id="persistent" data-position="fixed"> 
                <h4>Inloggning</h4> 
            </div> 
        </div>
    </body>
    <html>



