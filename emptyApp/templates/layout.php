<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>Blablabla</title>
        <?php require 'headerHtml.php' ?>
        <link rel="stylesheet" type="text/css" media="screen" href="<?php renderAsset('css/menu.css', true) ?>" />
        
        <style>
.wrapper-menu {
    /* Size & position */
    position: relative;
    width: 200px;
    margin: 0 0;
    padding: 12px 15px;

    /* Styles */
    background: #fff;
    border-radius: 5px;
    box-shadow: 0 1px 0 rgba(0,0,0,0.2);
    cursor: pointer;
    outline: none;
    -webkit-transition: all 0.3s ease-out;
    -moz-transition: all 0.3s ease-out;
    -ms-transition: all 0.3s ease-out;
    -o-transition: all 0.3s ease-out;
    transition: all 0.3s ease-out;
}

.wrapper-menu:after { /* Little arrow */
    content: "";
    width: 0;
    height: 0;
    position: absolute;
    top: 50%;
    right: 15px;
    margin-top: -3px;
    border-width: 6px 6px 0 6px;
    border-style: solid;
    border-color: #4cbeff transparent;
}

.wrapper-menu .dropdown {
    /* Size & position */
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;

    /* Styles */
    background: #fff;
    border-radius: 0 0 5px 5px;
    border: 1px solid rgba(0,0,0,0.2);
    border-top: none;
    border-bottom: none;
    list-style: none;
    -webkit-transition: all 0.3s ease-out;
    -moz-transition: all 0.3s ease-out;
    -ms-transition: all 0.3s ease-out;
    -o-transition: all 0.3s ease-out;
    transition: all 0.3s ease-out;

    /* Hiding */
    max-height: 0;
    overflow: hidden;
}

.wrapper-menu .dropdown li {
    padding: 0 10px ;
}

.wrapper-menu .dropdown li a {
    display: block;
    text-decoration: none;
    color: #333;
    padding: 10px 0;
    transition: all 0.3s ease-out;
    border-bottom: 1px solid #e6e8ea;
}

.wrapper-menu .dropdown li:last-of-type a {
    border: none;
}

.wrapper-menu .dropdown li i {
    margin-right: 5px;
    color: inherit;
    vertical-align: middle;
}

/* Hover state */

.wrapper-menu .dropdown li:hover a {
    color: #57a9d9;
}

/* Active state */

.wrapper-menu.active {
    border-radius: 5px 5px 0 0;
    background: #4cbeff;
    box-shadow: none;
    border-bottom: none;
    color: white;
}

.wrapper-menu.active:after {
    border-color: #82d1ff transparent;
}

.wrapper-menu.active .dropdown {
    border-bottom: 1px solid rgba(0,0,0,0.2);
    max-height: 400px;
}
        </style>        
    </head>
    <body>
        <script type="text/javascript">

        function DropDown(el) {
                this.dd = el;
                this.initEvents();
        }
        DropDown.prototype = {
                initEvents : function() {
                        var obj = this;

                        obj.dd.on('click', function(event){
                                $(this).toggleClass('active');
                                event.stopPropagation();
                        });	
                }
        }

        $(function() {

                var dd = new DropDown( $('#dd') );

                $(document).click(function() {
                        // all dropdowns
                        $('.wrapper-menu').removeClass('active');
                });

        });

        </script>

        <div class="grid-container grid">
            <header class="grid-100 mobile-grid-100">
                <div class="grid-50 hide-on-mobile">
                    <div id="logo">Gridlink</div>
                    <div id="searchUser">
                        <?php //formulaire de recherche des utilisateurs
                        $formRecherche = new mmForm();
                        $formRecherche->setAction(url('friends/search'));
                        $formRecherche->setNameFormat('searchFriends[%s]');
                        $formRecherche->addWidget(new mmWidgetText('userName'));
                        $formRecherche->addWidget(new mmWidgetButtonSubmit());
                        echo $formRecherche->start();
                        echo 'Chercher des amis'.$formRecherche['userName'];
                        echo $formRecherche['ok'];
                        echo $formRecherche->stop();
                        echo $formRecherche->renderJavascript();
                        ?>
                        
                    </div>
                </div>
                <div class="grid-50 mobile-grid-100" style="border: 1px solid black;">
                    <div class="grid-100 mobil-gris-100"><h3><?php echo mmUser::get('username', 'ERREUR USERNAME') ?></h3><a href="<?php echo url('message') ?>">message : <span id="pm_counter"></span></a></div>
                </div>
                <div class="grid-100 mobile-grid-100">
                    <nav>
                        <?php renderPartial('menu') ?>
                    </nav>
                </div>
            </header>
            <section class="grid-100 mobile-grid-100">
                <?php
                $flashes = mmUser::renderFlashs();
                if ($flashes != ''):
                    ?>
                    <div id="mainFlash" onclick="$('#mainFlash').hide()">
                        <div class="adminBox"></div>
                    <?php echo $flashes; ?>
                    </div>
                <?php endif; ?>
                <?php echo $sortieProgramme ?>
            </section>
            <footer class="grid-100 mobile-grid-100">
                <p>GridLink est un produis de DIMLIS...</p>
                <p>&copy; Dimlis Habitat 2014</p>                
            </footer>
        </div>
        <script>
            function checkPm() {
                $.getJSON('<?php echo url('wsj/pmn') ?>',function(data){
                    if (data.success) {
                        $('#pm_counter').html(data['nb']);
                    }
                });
                setTimeout(checkPm, 60000);
            }
            $(document).ready(function(){
                checkPm();
                $(window).focus(function(){
                    checkPm();
                });
            });
        </script>
    </body>
</html>
